# support-time

Приложение для расчёта рабочего времени на основе выгрузки из Omnidesk (CSV).

## Возможности

- Загрузка CSV через textarea или файл
- Отображение сотрудников и дней с выбором типа дня
- Учёт бонусов за ночное время и выходные
- Поддержка типов дня: рабочий, только заметки, нерабочий, выходной
- Чистый PHP (Slim 4) + JS без фреймворков

## Запуск

```bash
php -S localhost:8000 -t public
```

## Использование

1. Вставьте или загрузите CSV из Omnidesk
2. Выберите тип дня и бонусы для каждого сотрудника
3. Нажмите «Рассчитать»
4. Получите расчёт времени в часах

## Структура проекта

- `public/` — точка входа, `index.php`, ассеты (CSS, JS)
- `app/Controllers` — Slim-контроллеры: `FormController`, `ReportController`, `UploadController`
- `app/Services` — бизнес-логика: `CsvParserService`, `WorkTimeCalculatorService`, `LoggerFactory`, `AppLogger`
- `app/Views`
  - `layout.twig` — базовый шаблон с HTML-структурой и подключением ресурсов
  - `form.twig` — форма загрузки CSV и выбора типа дня
  - `report.twig` — отображение результатов расчёта
- `js/` — клиентская логика (`report.js`)
- `css/` — стили интерфейса
- `logs/` — лог-файлы

## Пример CSV (из Omnidesk)

```
Дата и время;Изменения выполнены;Действие;Изменения;Обращение
01.06.2025 00:07:24;Sergey;отправлен ответ в чате;;...
01.06.2025 00:15:42;Kirill;добавлена заметка в чате;;...
...
```

## Переменные окружения

```env
ENABLE_LOGS=true
LOGDIR=logs
LOGLEVEL=100  # DEBUG=100, INFO=200, WARNING=300
BASE_PATH=/support-time
```

## Логирование

Лог-файл создаётся в `logs/app.log`, если `ENABLE_LOGS=true`. Уровень логов регулируется `LOGLEVEL`.

## Стек

- PHP 8.2+ (Slim Framework 4)
- Twig для шаблонов
- Чистый JavaScript
- Docker-ready для Elest.io и локального запуска

## Лицензия

MIT
