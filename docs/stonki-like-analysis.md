# Stonki AI для российского рынка - анализ MOEX API

## Функции Stonki AI

| Функция | Описание | US Market | MOEX API |
|---------|----------|-----------|----------|
| **Recipes** | Торговые идеи с отслеживанием | ✅ | ⚠️ Нужно строить |
| **Monitoring** | Алерты на уровни, проверки | ✅ | ⚠️ Нужно строить |
| **Рыночные данные** | Цены, OHLCV, объёмы | ✅ | ✅ Есть |
| **Опционы** | Цепочки, греки, IV | ✅ | ❌ Сложно |
| **Теханализ** | Индикаторы, паттерны | ✅ | ⚠️ На своих данных |
| **Новости** | Анализ влияния | ✅ | ❌ Нет в API |
| **Скринер** | Поиск по критериям | ✅ | ⚠️ Частично |
| **Индексы** | Состав, веса | ✅ | ✅ Есть |
| **Фундаменталы** | P/E, P/B, выручка | ✅ | ⚠️ Через T-Invest |

---

## Что можно реализовать на MOEX API

### 1. ✅ Рыночные данные (P0)

**Уже есть:**
```bash
# Свечи
GET /engines/stock/markets/shares/securities/{SECID}/candles.json

# Текущие цены
GET /engines/stock/markets/shares/boards/TQBR/securities/{SECID}.json

# Итоги торгов
GET /securities/{SECID}/aggregates.json
```

**Задачи:**
- [x] `security:trade-data` - текущие цены
- [x] `security:aggregates` - объёмы торгов
- [ ] `candles` - исторические свечи (нужна реализация)

---

### 2. ✅ Индексы и веса (P0)

**Endpoint'ы:**
```bash
# В какие индексы входит бумага
GET /securities/{SECID}/indices.json

# Вес бумаги в индексе
GET /statistics/engines/stock/markets/index/analytics/{INDEXID}.json?secid={SECID}

# Состав индекса
GET /statistics/engines/stock/markets/index/analytics/{INDEXID}.json

# История индекса
GET /engines/stock/markets/index/securities/{INDEXID}/candles.json
```

**Задачи:**
- [x] `security:indices` - список индексов
- [ ] `index:weight` - вес в индексе
- [ ] `index:composition` - состав индекса
- [ ] `index:history` - история индекса

---

### 3. ⚠️ Recipes / Торговые идеи (P1)

**Что нужно:**
- Хранилище рецептов (JSON/DB)
- Формат рецепта
- Команды CRUD
- Автообновление по мониторингу

**Формат рецепта:**
```json
{
  "id": "long-sber-dividends-2025",
  "title": "Long SBER - дивиденды 2025",
  "ticker": "SBER",
  "thesis": "Сбербанк - высокая % маржа при ставке ЦБ 19%",
  "created_at": "2025-03-17",
  "status": "active",
  "entry": {
    "target_price": 300,
    "current_price": 318,
    "action": "wait_pullback"
  },
  "exit": {
    "target_price": 350,
    "stop_loss": 280,
    "timeframe": "6 months"
  },
  "risk": {
    "position_size_pct": 10,
    "risk_reward": 1.5
  },
  "updates": [
    {
      "date": "2025-03-20",
      "note": "Цена выросла до 320, ждём отката"
    }
  ],
  "monitoring": {
    "alerts": [
      {"type": "price_cross", "level": 350, "action": "notify"},
      {"type": "price_cross", "level": 280, "action": "notify"}
    ],
    "scheduled_checks": "weekly"
  }
}
```

**Задачи:**
- [ ] Создать структуру данных рецепта
- [ ] `recipe:create` - создать рецепт
- [ ] `recipe:list` - список рецептов
- [ ] `recipe:show <id>` - детали рецепта
- [ ] `recipe:update <id>` - обновить рецепт
- [ ] `recipe:close <id>` - закрыть рецепт

---

### 4. ⚠️ Monitoring / Алерты (P1)

**Типы мониторинга:**

| Тип | Описание | Реализация |
|-----|----------|------------|
| `price_cross` | Пересечение уровня | cron + MOEX API |
| `volume_spike` | Аномальный объём | cron + aggregates |
| `index_change` | Изменение веса в индексе | cron + analytics |
| `scheduled` | Периодическая проверка | cron + анализ |

**Архитектура:**
```
bin/moex monitor:start          # Запуск демона
bin/moex monitor:create         # Создать алерт
bin/moex monitor:list           # Список алертов
bin/moex monitor:check          # Ручная проверка
```

**Задачи:**
- [ ] Создать структуру данных мониторинга
- [ ] `monitor:create --ticker=SBER --type=price_cross --level=350`
- [ ] `monitor:list`
- [ ] `monitor:check` - проверить все алерты
- [ ] Интеграция с уведомлениями (email, telegram)

---

### 5. ⚠️ Скринер (P2)

**Критерии скрининга:**
- По сектору
- По весу в индексе
- По объёму торгов
- По дивидендной доходности (нужны данные)
- По P/E, P/B (нужны данные из T-Invest)

**Команда:**
```bash
./bin/moex screen --sector=oil_gas --min-volume=1B --min-dividend=5
```

**Задачи:**
- [ ] `screen:sector <sector>` - скрин по сектору
- [ ] `screen:volume --min=X` - по объёму
- [ ] `screen:index <index>` - бумаги из индекса

---

### 6. ❌ Чего НЕТ в MOEX API

| Функция | Stonki | Альтернатива |
|---------|--------|--------------|
| Опционы | ✅ Греки, IV, цепочки | MOEX имеет опционы, но API сложный |
| Новости | ✅ Анализ влияния | Парсинг RBC, Интерфакс |
| Соцсети | ✅ Twitter, Reddit | Парсинг Telegram, Смартлаб |
| Отчётности | ✅ Календарь | Парсинг disclosures |
| Фундаменталы | ✅ P/E, P/B, выручка | T-Invest API |

---

## Архитектура Stonki-подобного инструмента

```
moex-core/
├── src/
│   ├── Component/Moex/
│   │   ├── MoexIssComponent.php           # API клиент
│   │   └── Dto/
│   ├── Service/
│   │   ├── Security/                       # ✅ Есть
│   │   ├── Index/                          # ⚠️ Нужно
│   │   │   ├── IndexService.php
│   │   │   └── Dto/
│   │   ├── Recipe/                         # ❌ Нужно
│   │   │   ├── RecipeService.php
│   │   │   └── Dto/
│   │   ├── Monitoring/                     # ❌ Нужно
│   │   │   ├── MonitoringService.php
│   │   │   └── Dto/
│   │   └── Screen/                         # ❌ Нужно
│   ├── Command/
│   │   ├── candles.php                     # ⚠️ Нужно
│   │   ├── index:*.php                     # ⚠️ Нужно
│   │   ├── recipe:*.php                    # ❌ Нужно
│   │   └── monitor:*.php                   # ❌ Нужно
│   └── Repository/                         # ❌ Нужно для рецептов
│       └── RecipeRepository.php
├── data/                                   # ❌ Нужно
│   └── recipes/                            # JSON файлы рецептов
└── config/
    └── monitoring.yaml                     # ❌ Нужно
```

---

## Приоритеты реализации

### P0 - MVP (8h)
| # | Задача | Оценка |
|---|--------|--------|
| 1 | `candles` - исторические свечи | 3h |
| 2 | `index:weight` - вес в индексе | 2h |
| 3 | `index:composition` - состав индекса | 2h |
| 4 | `index:history` - история индекса | 1h |

### P1 - Recipes (10h)
| # | Задача | Оценка |
|---|--------|--------|
| 5 | Структура данных рецепта | 1h |
| 6 | `recipe:create/list/show/update/close` | 4h |
| 7 | Структура данных мониторинга | 1h |
| 8 | `monitor:create/list/check` | 4h |

### P2 - Advanced (8h)
| # | Задача | Оценка |
|---|--------|--------|
| 9 | `screen:*` - скринеры | 4h |
| 10 | Интеграция с T-Invest (фундаменталы) | 2h |
| 11 | Уведомления (telegram) | 2h |

---

## Пример использования

### Создание торговой идеи
```bash
./bin/moex recipe:create \
  --ticker=SBER \
  --title="Long SBER дивиденды" \
  --entry=300 \
  --target=350 \
  --stop=280 \
  --thesis="Высокая % маржа при ставке 19%"
```

### Настройка мониторинга
```bash
# Алерт на уровень
./bin/moex monitor:create \
  --ticker=SBER \
  --type=price_cross \
  --level=350

# Еженедельная проверка
./bin/moex monitor:create \
  --ticker=SBER \
  --type=scheduled \
  --schedule="weekly" \
  --prompt="Проверь техсостояние SBER и обнови рецепт"
```

### Скрининг
```bash
# Топ по объёму в нефтегазе
./bin/moex screen --sector=oil_gas --min-volume=1B --limit=10

# Все бумаги из IMOEX с весом > 5%
./bin/moex screen --index=IMOEX --min-weight=5
```

---

## Интеграция с AI

Stonki использует Claude как оркестратора. Для нашего инструмента:

1. **CLI команды** → данные в JSON
2. **AI агент** → вызывает команды, анализирует данные
3. **Результат** → рекомендации пользователю

**Пример промпта для AI:**
```
Ты - финансовый аналитик для российского рынка.
Используй команды moex-core для анализа:

Доступные команды:
- moex security:specification <ticker>
- moex security:trade-data <ticker>
- moex security:indices <ticker>
- moex index:weight <ticker> <index>
- moex candles <ticker> --from=X --to=Y

Задача: проанализируй SBER и дай рекомендацию.
```
