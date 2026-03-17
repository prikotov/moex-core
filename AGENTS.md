# Инструкции для AI-агента

## Описание проекта

MOEX Skill - консольное приложение на PHP для работы с MOEX ISS API.

## Технологии

- PHP 8.4+
- Symfony Console (CLI)
- Symfony DependencyInjection (DI)
- Guzzle HTTP Client
- Monolog (логирование)
- PHPUnit (тестирование)

## Структура проекта

```
├── bin/moex                     # Точка входа CLI
├── config/
│   ├── container.php            # Инициализация DI-контейнера
│   └── services.yaml            # Определение сервисов
├── src/
│   ├── Command/                 # Консольные команды
│   ├── Service/                 # Слой бизнес-логики
│   │   └── Security/            # Группа сервисов для работы с бумагами
│   │       ├── Dto/             # View-DTO для команд
│   │       ├── SecurityServiceInterface.php
│   │       ├── SecurityService.php
│   │       └── *Result.php      # Результаты вызовов
│   └── Component/Moex/          # API-компоненты
│       ├── MoexIssComponentInterface.php
│       └── MoexIssComponent.php
├── tests/                       # Тесты PHPUnit
├── .env                         # Конфигурация (в репозитории)
└── .env.local                   # Локальные переопределения (игнорируется git)
```

## Команды

### Установка зависимостей
```bash
composer install
```

### Тесты
```bash
composer test
```

### Код-стайл
```bash
composer cs-check    # Проверка PSR-12
composer cs-fix      # Исправление нарушений PSR-12
```

### Статический анализ
```bash
composer stan        # PHPStan
composer psalm       # Psalm
```

### Запуск приложения
```bash
./bin/moex
./bin/moex security:specification SBER
./bin/moex security:trade-data SBER
./bin/moex security:aggregates SBER
./bin/moex security:indices SBER
./bin/moex --help
```

## Архитектура

### Трёхуровневая архитектура

1. **Command** - консольные команды, только форматирование вывода
2. **Service** - бизнес-логика, подготовка данных для команд
3. **Component** - вызовы API

### Слой сервисов
```
src/Service/{Group}/
├── Dto/                          # View-DTO для команд
├── {Group}ServiceInterface.php   # Интерфейс
├── {Group}Service.php            # Реализация
└── *Result.php                   # Результаты вызовов сервисов
```

### API-компоненты
- `MoexIssComponentInterface.php` - Интерфейс
- `MoexIssComponent.php` - Реализация с Guzzle HTTP Client

### Dependency Injection
- Все сервисы регистрируются в `config/services.yaml`
- Autowiring включён
- Параметры привязываются через `$baseUrl`
- DTO и Result-классы исключены из autowiring

## При внесении изменений

### Добавление новой консольной команды
1. Создать сервис в `src/Service/{Group}/`
2. Создать команду в `src/Command/`
3. Команда автоматически зарегистрируется через тег `console.command`

### Перед коммитом
1. `composer cs-check` - исправить нарушения
2. `composer stan` - исправить ошибки PHPStan
3. `composer psalm` - исправить ошибки Psalm
4. `composer test` - убедиться что тесты проходят

### Стиль кода
- Без комментариев в коде, если явно не запрошено
- Использовать `declare(strict_types=1);`
- Использовать readonly-свойства в DTO
- Использовать возможности PHP 8.4+ (enums, readonly, property hooks и т.д.)

## Справочник API

MOEX ISS API: https://iss.moex.com/iss/reference/
