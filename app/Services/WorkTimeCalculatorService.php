<?php

namespace App\Services;

class WorkTimeCalculatorService
{
    private const WORK_START = 8;
    private const WORK_END = 20;
    private const BLOCK_MINUTES = 10;
    private const BLOCK_INTERVAL_SECONDS = 600;
    private const MINUTES_IN_HOUR = 60;
    private const BONUS_MULTIPLIER = 1.2;
    private const NOTES_MINUTES_LIMIT = 360;
    private const OFFWORK_NOTES_MINUTES_LIMIT = 120;

    private const TYPE_WORK = 'work';
    private const TYPE_NOTES = 'notes';
    private const TYPE_BONUS = 'bonus';

    private array $trackedEvents = [
        'добавлена заметка в чате',
        'добавлена заметка',
        'отправлен ответ в чате',
        'отправлен ответ'
    ];

    public function calculate(array $events, string $dayType, bool $bonus): array
    {
        $timeBlocks = $this->collectTimeBlocks($events);
        $minutes = [
            self::TYPE_WORK => 0,
            self::TYPE_NOTES => 0,
            self::TYPE_BONUS => 0
        ];

        foreach ($timeBlocks as $block) {
            $ts = $block['ts'];
            $event = $block['event'];

            $hour = (int)date('G', $ts);
            $weekday = (int)date('w', $ts);
            $isWorkTime = $hour >= self::WORK_START && $hour < self::WORK_END && $weekday >= 1 && $weekday <= 5;
            $isNote = stripos($event, 'заметка') !== false;

            switch ($dayType) {
                case self::TYPE_WORK:
                    if ($isWorkTime) {
                        $minutes[self::TYPE_WORK] += self::BLOCK_MINUTES;
                    } elseif ($bonus) {
                        $minutes[self::TYPE_BONUS] += self::BLOCK_MINUTES;
                    }
                    break;

                case self::TYPE_NOTES:
                case 'offwork':
                    if ($isNote) {
                        if ($isWorkTime) {
                            $minutes[self::TYPE_NOTES] += self::BLOCK_MINUTES;
                        } elseif ($bonus) {
                            $minutes[self::TYPE_BONUS] += self::BLOCK_MINUTES;
                        }
                    }
                    break;

                case 'weekend':
                    if ($bonus) {
                        $minutes[self::TYPE_BONUS] += self::BLOCK_MINUTES;
                    }
                    break;
            }
        }

        // Применение лимитов
        if ($dayType === self::TYPE_NOTES) {
            $minutes[self::TYPE_NOTES] = min($minutes[self::TYPE_NOTES], self::NOTES_MINUTES_LIMIT);
        }
        if ($dayType === 'offwork') {
            $minutes[self::TYPE_NOTES] = min($minutes[self::TYPE_NOTES], self::OFFWORK_NOTES_MINUTES_LIMIT);
        }

        return $this->convertMinutesToHours($minutes, $bonus);
    }

    private function collectTimeBlocks(array $events): array
    {
        $timeBlocks = [];

        foreach ($events as $event) {
            [$datetime, $_user, $action] = $event;
            if (!$this->isTracked($action)) continue;

            $ts = strtotime($datetime);
            $block = floor($ts / self::BLOCK_INTERVAL_SECONDS);

            if (!isset($timeBlocks[$block])) {
                $timeBlocks[$block] = ['ts' => $ts, 'event' => $action];
            }
        }

        return $timeBlocks;
    }

    private function convertMinutesToHours(array $minutes, bool $bonus): array
    {
        return [
            self::TYPE_WORK => round($minutes[self::TYPE_WORK] / self::MINUTES_IN_HOUR, 1),
            self::TYPE_NOTES => round($minutes[self::TYPE_NOTES] / self::MINUTES_IN_HOUR, 1),
            self::TYPE_BONUS => round($minutes[self::TYPE_BONUS] / self::MINUTES_IN_HOUR * ($bonus ? self::BONUS_MULTIPLIER : 0), 1),
        ];
    }

    private function isTracked(string $event): bool
    {
        foreach ($this->trackedEvents as $pattern) {
            if (stripos($event, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
}
