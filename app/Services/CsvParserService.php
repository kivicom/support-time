<?php

namespace App\Services;

class CsvParserService
{
    private const DELIMITER = ';';
    private const REQUIRED_FIELDS = 3;
    private const HEADER_OFFSET = 1;

    public function parse(string $text): array
    {
        $text = $this->ensureUtf8($text);
        $lines = explode("\n", trim($text));
        $data = [];

        // Пропускаем заголовок
        $lines = array_slice($lines, self::HEADER_OFFSET);

        foreach ($lines as $line) {
            $fields = str_getcsv($line, self::DELIMITER);

            if (count($fields) < self::REQUIRED_FIELDS) {
                continue; // недостаточно полей: datetime, user, event
            }

            $datetime = trim($fields[0]);
            $user = trim($fields[1]);
            $event = trim($fields[2]);

            // базовая валидация даты
            if (!strtotime($datetime)) {
                continue;
            }

            $data[] = [$datetime, $user, $event];
        }

        return $data;
    }

    private function ensureUtf8(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            return mb_convert_encoding($text, 'UTF-8', 'CP1251');
        }
        return $text;
    }
}
