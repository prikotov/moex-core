# Задачи MOEX Core

## P0 - Критично для ребалансировки

### 1. Исторические свечи
**Оценка:** 3h

**Endpoint:**
```
GET /engines/stock/markets/shares/securities/{SECID}/candles.json
  ?from=YYYY-MM-DD&till=YYYY-MM-DD&interval=24
```

**Задачи:**
- [ ] `MoexIssComponentInterface::getCandles(ticker, from, to, interval)`
- [ ] `MoexIssComponent::getCandles()`
- [ ] `Service/Security/Dto/CandleDto.php`
- [ ] `Service/Security/Dto/GetCandlesResult.php`
- [ ] `Service/Security/SecurityService::getCandles()`
- [ ] Команда `candles <ticker> --from=... --to=...`

**Пример:**
```bash
./bin/moex candles SBER --from=2024-01-01 --to=2024-12-31
```

---

### 2. Сектор инструмента
**Оценка:** 2h

**Задачи:**
- [ ] Добавить поле `sector` в `SecuritySpecificationDto`
- [ ] Парсить `SECTNAME` из API

---

### 3. Вес бумаги в индексе
**Оценка:** 2h

**Endpoint:**
```
GET /statistics/engines/stock/markets/index/analytics/{INDEXID}.json?secid={SECID}
```

**Задачи:**
- [ ] `MoexIssComponent::getIndexWeight(secid, indexid)`
- [ ] `Service/Index/IndexServiceInterface.php`
- [ ] `Service/Index/IndexService.php`
- [ ] `Service/Index/Dto/IndexWeightDto.php`
- [ ] Команда `index:weight <ticker> <index>`

**Пример:**
```bash
./bin/moex index:weight SBER IMOEX
# SBER в IMOEX: 10.52%
```

---

## P1 - Важно

### 4. Торговый календарь ✅
**Оценка:** 2h

**Endpoint:**
```
GET /engines/stock/trading.json
```

**Возвращает:**
- Расписание торгов по рынкам
- Аукционы открытия/закрытия
- Вечерняя сессия
- Клиринг

**Задачи:**
- [x] `Service/Schedule/ScheduleServiceInterface.php`
- [x] `Service/Schedule/ScheduleService.php`
- [x] `Service/Schedule/Dto/TradingSessionDto.php`
- [x] `Service/Schedule/Dto/ScheduleResult.php`
- [x] Команда `schedule [--engine=stock] [--market=shares]`
- [ ] Тесты

**Пример:**
```bash
./bin/moex schedule --engine=stock --market=shares
# STOCK / Акции
#   Main session: 10:00 - 18:40
#   Evening session: 19:00 - 23:50
```

---

### 5. История индекса (свечи)
**Оценка:** 3h

**Endpoint:**
```
GET /engines/stock/markets/index/securities/{INDEXID}/candles.json
```

**Задачи:**
- [ ] `MoexIssComponent::getIndexCandles(indexid, from, to)`
- [ ] Команда `index:history <index> --from=... --to=...`

**Пример:**
```bash
./bin/moex index:history IMOEX --from=2024-01-01 --to=2024-12-31
```

---

### 5. Состав индекса
**Оценка:** 2h

**Endpoint:**
```
GET /statistics/engines/stock/markets/index/analytics/{INDEXID}.json
```

**Задачи:**
- [ ] `MoexIssComponent::getIndexComposition(indexid)`
- [ ] `Service/Index/Dto/IndexCompositionDto.php`
- [ ] Команда `index:composition <index>`

**Пример:**
```bash
./bin/moex index:composition IMOEX
# 1. LKOH  14.46%
# 2. SBER  10.52%
# 3. GAZP   9.56%
```

---

## Структура сервисов

```
src/Service/
├── Security/           # Существует
│   ├── SecurityServiceInterface.php
│   ├── SecurityService.php
│   └── Dto/
│       ├── CandleDto.php           # NEW
│       └── GetCandlesResult.php    # NEW
│
└── Index/              # NEW
    ├── IndexServiceInterface.php
    ├── IndexService.php
    └── Dto/
        ├── IndexWeightDto.php
        ├── IndexCompositionDto.php
        └── IndexCandlesResult.php
```

---

## Команды

| Команда | Описание | Приоритет | Статус |
|---------|----------|-----------|--------|
| `candles <ticker>` | Исторические свечи | P0 | |
| `index:weight <ticker> <index>` | Вес в индексе | P0 | |
| `schedule` | Торговый календарь | P1 | ✅ |
| `index:history <index>` | История индекса | P1 | |
| `index:composition <index>` | Состав индекса | P1 | |

---

## Итого

| Приоритет | Задачи | Время |
|-----------|--------|-------|
| P0 | 3 задачи | 7h |
| P1 | 2 задачи | 5h |
| **Всего** | **5 задач** | **12h** |
