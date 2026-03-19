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
./vendor/bin/moex security:candles SBER
```

## Команды

| Команда | Описание |
|---------|----------|
| `security:specification <тикер>` | Спецификация инструмента (ISIN, List Level, Type) |
| `security:trade-data <тикер>` | Текущие рыночные данные (Last, Open/High/Low, Volume) |
| `security:aggregates <тикер> [--date=YYYY-MM-DD]` | Агрегированные итоги торгов |
| `security:indices <тикер>` | Индексы МосБиржи, в которые входит бумага |
| `security:candles <тикер> [options]` | Исторические свечи (OHLCV) |

### Свечи

```bash
moex security:candles SBER --from=2024-01-01 --to=2024-01-31 --interval=60 --limit=100
```

| Опция | Описание | По умолчанию |
|-------|----------|--------------|
| --from, -f | Начало периода | нет |
| --to, -t | Конец периода | нет |
| --interval, -i | Интервал (1, 10, 60, 24, 7, 31 мин) | 60 |
| --limit, -l | Макс. свечей | 100 |

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
