# MOEX Skill

Консольное приложение на PHP для работы с MOEX ISS API.

## Установка

```bash
composer install
```

## Использование

```bash
./bin/moex security:specification SBER
./bin/moex security:trade-data SBER
./bin/moex security:aggregates SBER
./bin/moex security:indices SBER
```

## Команды

| Команда | Описание |
|---------|----------|
| `security:specification <тикер>` | Получить спецификацию инструмента |
| `security:trade-data <тикер>` | Получить текущие рыночные данные |
| `security:aggregates <тикер> [--date=YYYY-MM-DD]` | Получить агрегированные итоги торгов |
| `security:indices <тикер>` | Получить список индексов МосБиржи, в которые входит бумага |

## Разработка

```bash
composer test       # Запуск тестов
composer cs-check   # Проверка PSR-12
composer cs-fix     # Исправление PSR-12
composer stan       # PHPStan
composer psalm      # Psalm
```

## Лицензия

MIT
