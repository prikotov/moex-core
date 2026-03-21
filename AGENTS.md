# Инструкции для AI-агента

## Описание

CLI утилита для работы с MOEX ISS API (Московская Биржа).

## Технологии

- PHP 8.4+
- Symfony Console, DependencyInjection
- Guzzle HTTP Client
- Monolog

## Структура

```
├── bin/moex                     # CLI entry point
├── config/
│   ├── container.php
│   └── services.yaml
├── src/
│   ├── Command/
│   ├── Service/Security/
│   │   ├── Dto/
│   │   ├── SecurityServiceInterface.php
│   │   └── SecurityService.php
│   └── Component/Moex/
└── tests/
```

## Команды

```bash
./bin/moex security:specification SBER
./bin/moex security:trade-data SBER
./bin/moex security:aggregates SBER
./bin/moex security:search SBER
./bin/moex security:indices SBER
./bin/moex security:candles SBER
```

## Форматы вывода

Все команды поддерживают `--format`:
- `md` (по умолчанию) - Markdown таблица
- `json` - JSON массив объектов
- `csv` - CSV с заголовком
- `text` - ASCII-таблица

```bash
./bin/moex security:search SBER              # md
./bin/moex security:search SBER --format csv
```

## Архитектура

1. **Command** - форматирование вывода
2. **Service** - бизнес-логика
3. **Component** - API вызовы

## Разработка

```bash
composer cs-check && composer stan && composer psalm && composer test
```

## Стиль кода

- `declare(strict_types=1);`
- readonly-свойства в DTO
- Без комментариев

## API Reference

https://iss.moex.com/iss/reference/
