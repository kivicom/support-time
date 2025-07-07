<?php

namespace App\Controllers;

use App\Services\AppLogger;
use App\Services\WorkTimeCalculatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Slim\Views\Twig;

class ReportController
{
    public function __construct(private Twig $view) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $post = $request->getParsedBody();

        $dayTypes = $post['daytype'] ?? [];
        $bonus = $post['bonus'] ?? [];
        $raw = json_decode($post['raw'] ?? '[]', true);
        $schedule = json_decode($post['schedule'] ?? '{}', true);
        $users = json_decode($post['users'] ?? '[]', true);

        $eventsByUserAndDay = $this->groupEvents($raw);
        [$report, $totals] = $this->buildReport($schedule, $dayTypes, $bonus, $eventsByUserAndDay);

        AppLogger::get()->info('Расчёт отчёта завершён', ['пользователей' => count($users), 'дней' => count($schedule)]);

        return $this->view->render($response, 'report.twig', [
            'report' => $report,
            'totals' => $totals,
            'users' => $users,
        ]);
    }

    private function groupEvents(array $raw): array
    {
        $grouped = [];
        foreach ($raw as [$datetime, $user, $action]) {
            $day = date('d.m.Y', strtotime($datetime));
            $grouped[$user][$day][] = [$datetime, $user, $action];
        }

        return $grouped;
    }

    private function buildReport(array $schedule, array $dayTypes, array $bonus, array $eventsByUserAndDay): array
    {
        $calc = new WorkTimeCalculatorService();
        $report = [];
        $totals = [];

        foreach ($schedule as $day => $userList) {
            foreach ($userList as $user => $meta) {
                $hash = $meta['hash'];
                $type = $dayTypes[$hash] ?? 'weekend';
                $hasBonus = isset($bonus[$hash]);
                $events = $eventsByUserAndDay[$user][$day] ?? [];

                $result = $calc->calculate($events, $type, $hasBonus);
                $report[$day][$user] = $result;

                foreach ($result as $cat => $val) {
                    $totals[$user][$cat] = ($totals[$user][$cat] ?? 0) + $val;
                }

                //AppLogger::get()->debug("Отчёт для $user на $day", $result);
            }
        }

        return [$report, $totals];
    }
}
