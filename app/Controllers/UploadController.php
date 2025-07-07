<?php

namespace App\Controllers;

use App\Services\AppLogger;
use App\Services\CsvParserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class UploadController
{
    public function __construct() {}

    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $csv = $this->extractCsvText($request);

        if (!$csv) {
            AppLogger::get()->warning('Пустой CSV при загрузке');
            return $this->json($response, ['error' => 'Нет данных CSV'], 400);
        }

        $parser = new CsvParserService();
        $rows = $parser->parse($csv);
        AppLogger::get()->info('CSV загружен', ['строк' => count($rows)]);

        [$schedule, $users] = $this->buildSchedule($rows);

        return $this->json($response, [
            'raw' => $rows,
            'schedule' => $schedule,
            'users' => $users
        ]);
    }

    private function extractCsvText(ServerRequestInterface $request): string
    {
        $parsed = $request->getParsedBody();
        $csv = trim($parsed['csvtext'] ?? '');

        $uploaded = $request->getUploadedFiles();
        if (empty($csv) && isset($uploaded['csvfile']) && $uploaded['csvfile']->getError() === UPLOAD_ERR_OK) {
            $csv = $uploaded['csvfile']->getStream()->getContents();
        }

        return $csv;
    }

    private function buildSchedule(array $rows): array
    {
        $days = [];
        $userList = [];

        foreach ($rows as [$datetime, $user, $_]) {
            $day = date('d.m.Y', strtotime($datetime));
            $user = trim($user);
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

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
