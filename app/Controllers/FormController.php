<?php

namespace App\Controllers;

use App\Services\AppLogger;
use App\Services\CsvParserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class FormController
{
    public function __construct(private Twig $view) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // 1. Получение CSV-данных
        $csv = $this->getCsvText($request);

        $parsed = $request->getParsedBody();
        $raw = json_decode($parsed['raw'] ?? '[]', true);
        $schedule = json_decode($parsed['schedule'] ?? '{}', true);
        $users = json_decode($parsed['users'] ?? '[]', true);

        // 2. Парсинг и построение расписания
        if (!empty($csv)) {
            $parser = new CsvParserService();
            $raw = $parser->parse($csv);

            [$schedule, $users] = $this->buildSchedule($raw);

            AppLogger::get()->info('Форма: построено расписание', ['строк' => count($raw)]);
        }

        // 3. Рендеринг шаблона
        return $this->view->render($response, 'form.twig', [
            'schedule' => $schedule,
            'users' => $users,
            'raw' => $raw
        ]);
    }

    private function getCsvText(ServerRequestInterface $request): string
    {
        $parsed = $request->getParsedBody();
        $csv = '';

        if (isset($parsed['csvtext']) && trim($parsed['csvtext']) !== '') {
            $csv = $parsed['csvtext'];
        }

        $uploaded = $request->getUploadedFiles();
        if (!$csv && isset($uploaded['csvfile']) && $uploaded['csvfile']->getError() === UPLOAD_ERR_OK) {
            $csv = $uploaded['csvfile']->getStream()->getContents();
        }

        return $csv;
    }

    private function buildSchedule(array $raw): array
    {
        $days = [];
        $userList = [];

        foreach ($raw as [$datetime, $user, $_]) {
            $user = trim($user);
            $day = date('d.m.Y', strtotime($datetime));
            $days[$day] = true;
            $userList[$user] = true;
        }

        ksort($days);
        ksort($userList);

        $schedule = [];
        foreach (array_keys($days) as $day) {
            foreach (array_keys($userList) as $user) {
                $schedule[$day][$user] = [
                    'hash' => md5($day . $user)
                ];
            }
        }

        return [$schedule, array_keys($userList)];
    }
}
