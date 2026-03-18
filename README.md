# MOEX CLI

CLI утилита для работы с MOEX ISS API (Московская Биржа).

## Установка

```bash
composer require prikotov/moex-core:@dev
```

## Использование

```bash
./vendor/bin/moex security:specification SBER
./vendor/bin/moex security:trade-data SBER
./vendor/bin/moex security:aggregates SBER
./vendor/bin/moex security:indices SBER
```

## Команды

| Команда | Описание |
|---------|----------|
| `security:specification <тикер>` | Спецификация инструмента (ISIN, List Level, Type) |
| `security:trade-data <тикер>` | Текущие рыночные данные (Last, Open/High/Low, Volume) |
| `security:aggregates <тикер> [--date=YYYY-MM-DD]` | Агрегированные итоги торгов |
| `security:indices <тикер>` | Индексы МосБиржи, в которые входит бумага |

## Разработка

```bash
composer install
composer test
composer cs-check
composer stan
composer psalm
```

## API Reference

MOEX ISS API: https://iss.moex.com/iss/reference/

## Лицензия

MIT
