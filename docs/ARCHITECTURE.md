# Architecture Documentation

Comprehensive overview of the Currency Converter application architecture, design patterns, and system structure.

## Overview

The Currency Converter application follows a modular architecture with clear separation of concerns, making it maintainable, testable, and scalable.

## Module Structure

The application uses a self-contained module structure located at `app/Modules/Currency/`:

```
app/Modules/Currency/
├── Configuration/          # Configuration files
│   ├── api.php            # API settings (URL, key, timeouts, delays)
│   └── currencies.php     # Hardcoded currency list (30+ currencies)
├── Console/               # Artisan commands
│   └── CurrencyRatesSync.php  # Command: app:currency-rates-sync
├── Contracts/             # Interfaces (contracts)
│   ├── Api/
│   │   └── CurrencyApiInterface.php
│   └── ExchangeRateRepositoryInterface.php
├── Controllers/           # HTTP controllers
│   ├── Admin/
│   │   └── ExchangeRateController.php  # Admin routes
│   └── CurrencyConverterController.php  # Public routes
├── Database/
│   └── Migrations/
│       └── create_exchange_rates_table.php
├── DTOs/                  # Data Transfer Objects
│   └── CurrencyConversionResult.php
├── Exceptions/            # Custom exceptions
│   └── CurrencyApiException.php
├── Jobs/                  # Queue jobs
│   └── SyncCurrencyExchangeRates.php
├── Models/                # Eloquent models
│   └── ExchangeRate.php
├── Providers/             # Service providers
│   └── CurrencyServiceProvider.php
├── Repositories/          # Data access layer
│   ├── Api/
│   │   └── CurrencyApiRepository.php  # External API integration
│   └── ExchangeRateRepository.php     # Database operations
├── Requests/              # Form requests (validation)
│   └── ConvertCurrencyRequest.php
├── Resources/             # API resources
│   └── ExchangeRateResource.php
├── routes/                # Route definitions
│   ├── web.php           # Public routes (/converter)
│   ├── api.php           # API routes (/api/exchange-rates/sync-daily)
│   └── admin.php         # Admin routes (/admin/exchange-rates)
└── Services/              # Business logic
    ├── Api/
    │   └── ExchangeRateSyncService.php  # Coordinates rate sync
    ├── ConverterService.php             # Currency conversion logic
    └── CurrencyConfigService.php        # Currency configuration access
```

## Design Patterns

### 1. Repository Pattern

**Purpose:** Abstract data access layer from business logic.

**Implementation:**
- `ExchangeRateRepositoryInterface` defines the contract
- `ExchangeRateRepository` implements database operations
- Controllers and services depend on interfaces, not implementations

**Benefits:**
- Easy to swap implementations (e.g., for testing)
- Centralized data access logic
- Clear separation of concerns

**Example:**
```php
// Interface
interface ExchangeRateRepositoryInterface {
    public function findRate(string $baseCode, string $targetCode, string $date): ?ExchangeRate;
    public function upsertDailyRates(string $baseCode, array $rates, string $date): void;
}

// Implementation
class ExchangeRateRepository implements ExchangeRateRepositoryInterface {
    // Database operations using Eloquent
}
```

### 2. Service Layer Pattern

**Purpose:** Encapsulate business logic separate from controllers and repositories.

**Services:**
- **ConverterService**: Handles currency conversion logic
  - Validates currencies
  - Finds exchange rates
  - Handles fallback strategies (USD conversion)
  - Returns formatted results

- **ExchangeRateSyncService**: Coordinates rate synchronization
  - Dispatches queue jobs
  - Manages delays between jobs
  - Handles job coordination

- **CurrencyConfigService**: Manages currency configuration
  - Provides access to hardcoded currencies
  - Validates currency codes
  - Lazy-loads configuration

**Benefits:**
- Reusable business logic
- Testable units
- Single Responsibility Principle

### 3. Dependency Injection

**Purpose:** Loose coupling and testability.

**Implementation:**
- All dependencies injected via constructor
- Interfaces bound in `CurrencyServiceProvider`
- Laravel's service container resolves dependencies

**Example:**
```php
class ConverterService {
    public function __construct(
        private ExchangeRateRepositoryInterface $exchangeRateRepository
    ) {}
}
```

**Benefits:**
- Easy to mock dependencies for testing
- Flexible implementation swapping
- Clear dependency declarations

### 4. DTO (Data Transfer Object) Pattern

**Purpose:** Type-safe, structured data transfer.

**Implementation:**
- `CurrencyConversionResult` DTO
- Immutable, readonly properties
- Handles formatting (decimals, rates)
- Converts to array for API responses

**Example:**
```php
class CurrencyConversionResult {
    public function __construct(
        public readonly string $amount,
        public readonly string $fromCurrency,
        public readonly string $toCurrency,
        public readonly string $convertedAmount,
        public readonly string $rate,
    ) {}
}
```

**Benefits:**
- Type safety
- Consistent data structure
- Centralized formatting logic

## Data Flow

### Currency Conversion Flow

```
1. User Request
   ↓
2. CurrencyConverterController::convert()
   ↓
3. ConvertCurrencyRequest (Validation)
   - Validates amount, from_currency, to_currency
   - Checks currency codes against CurrencyConfigService
   ↓
4. ConverterService::convert()
   - Validates currencies (defense in depth)
   - Handles same currency (1:1 conversion)
   - Finds exchange rate:
     a. Try direct rate for today
     b. Try latest available rate
     c. Fallback: Convert via USD
   ↓
5. ExchangeRateRepository
   - Queries database for rates
   - Returns ExchangeRate model or null
   ↓
6. CurrencyConversionResult (DTO)
   - Formats amounts (2 decimals)
   - Formats rates (8 decimals)
   - Creates immutable result object
   ↓
7. Response (Inertia)
   - Returns formatted result or error
   - Frontend displays conversion
```

### Rate Synchronization Flow

```
1. Scheduler (Daily at 00:00 UTC)
   ↓
2. CurrencyRatesSync Command
   ↓
3. ExchangeRateSyncService::syncDailyRates()
   - Gets all supported currencies
   - Checks which currencies already have today's rates
   - Dispatches jobs with incremental delays
   ↓
4. SyncCurrencyExchangeRates Job (per currency)
   - Checks if rates already exist for today
   - Fetches rates from API (CurrencyApiRepository)
   - Filters rates (supported currencies only)
   - Upserts rates to database (ExchangeRateRepository)
   ↓
5. Database
   - Stores rates with unique constraint
   - Updates existing or creates new records
```

## Database Schema

### exchange_rates Table

```sql
CREATE TABLE exchange_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    base_code VARCHAR(3) NOT NULL,           -- Base currency code (e.g., 'USD')
    target_code VARCHAR(3) NOT NULL,         -- Target currency code (e.g., 'EUR')
    rate DECIMAL(15,8) NOT NULL,            -- Exchange rate (high precision)
    date DATE NOT NULL,                     -- Rate date (allows historical rates)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    -- Unique constraint prevents duplicate rates
    UNIQUE KEY er_base_target_date_unique (base_code, target_code, date),

    -- Indexes for performance
    INDEX (base_code),
    INDEX (target_code)
);
```

**Design Decisions:**
- **Currency Codes Directly**: No foreign keys to currencies table (currencies are hardcoded)
- **High Precision Rates**: `DECIMAL(15,8)` for accurate conversions
- **Date Field**: Allows storing historical rates
- **Unique Constraint**: Prevents duplicate rates for same pair/date
- **Indexes**: Optimize queries by base_code and target_code

### Other Tables

- **users**: Laravel authentication
- **jobs**: Queue job storage
- **failed_jobs**: Failed queue jobs
- **cache**: Cache storage
- **sessions**: Session storage (if using database sessions)

## Currency Configuration

### Hardcoded Currencies

Currencies are stored in `Configuration/currencies.php` as a PHP array:

```php
return [
    'EUR' => ['name' => 'Euro', 'symbol' => '€'],
    'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
    // ... 30+ currencies
];
```

**Why Hardcoded?**
- No database queries needed
- Fast access
- Easy to version control
- Simple to add/remove currencies

**Access Pattern:**
- Accessed via `CurrencyConfigService`
- Lazy-loaded on first access
- Cached in static property

## Queue System

### Job Structure

- **One Job Per Currency**: Each currency gets its own queue job
- **Incremental Delays**: Jobs dispatched with increasing delays (0s, 5s, 10s, ...)
- **Automatic Retry**: Failed jobs retry up to 3 times
- **Rate Limit Handling**: Special handling for 429 errors (5-minute delay)

### Job Lifecycle

```
1. Job Dispatched
   ↓
2. Job Queued (with delay)
   ↓
3. Queue Worker Picks Up Job
   ↓
4. Job Executes
   - Check if rates exist for today
   - Fetch from API
   - Filter and validate rates
   - Upsert to database
   ↓
5. Success or Failure
   - Success: Job completes
   - Failure: Retry or mark as failed
```

### Queue Configuration

- **Default**: Database queue (`QUEUE_CONNECTION=database`)
- **Production**: Redis queue (better performance)
- **Worker**: Must run continuously (`php artisan queue:work`)

## Scheduling

### Scheduled Tasks

```php
Schedule::command('app:currency-rates-sync')
    ->daily()
    ->at('00:00')
    ->timezone('UTC');
```

**Execution:**
- Runs daily at midnight UTC
- Dispatches jobs for all currencies
- Jobs process asynchronously via queue

**Cron Setup:**
```bash
* * * * * cd /path-to-project/src && php artisan schedule:run >> /dev/null 2>&1
```

## Frontend Architecture

### Technology Stack

- **React 18**: UI library
- **Inertia.js**: Server-driven SPA framework
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast build tool

### Component Structure

```
resources/js/
├── Pages/
│   ├── Converter/
│   │   ├── Index.jsx              # Converter page
│   │   └── Partials/
│   │       └── CurrencyConverter.jsx  # Converter form component
│   └── Admin/
│       └── ExchangeRates/
│           ├── Index.jsx          # Exchange rates page
│           └── Partials/
│               └── ChooseCurrency.jsx  # Currency selector
└── Layouts/
    └── SimpleLayout.jsx           # Shared layout
```

### State Management

- **Inertia.js `useForm()`**: Form state and validation
- **Server-side Validation**: Errors automatically shared via Inertia
- **No Redux/Context**: Inertia handles state management

### Data Flow (Frontend)

```
1. User Interaction
   ↓
2. Inertia Form Submit
   ↓
3. POST Request to Laravel
   ↓
4. Laravel Processes Request
   - Validates input
   - Performs conversion
   - Returns result or errors
   ↓
5. Inertia Updates Page
   - Shares data/errors
   - React re-renders
   ↓
6. User Sees Result/Error
```

## Security Considerations

### 1. Authentication
- Admin routes require authentication
- Laravel Breeze handles authentication
- Session-based authentication

### 2. Input Validation
- All inputs validated via FormRequest
- Currency codes validated against whitelist
- Amount validation (numeric, min, max)

### 3. Rate Limiting
- Built-in delays prevent API rate limits
- Job retry logic handles rate limit errors
- Configurable delays per environment

### 4. Error Handling
- Sensitive errors logged, not exposed
- User-friendly error messages
- Proper exception handling

### 5. SQL Injection Protection
- Eloquent ORM provides protection
- Parameterized queries
- No raw SQL with user input

### 6. XSS Protection
- Inertia.js escapes data
- React handles rendering safely
- No direct HTML injection

## Performance Optimizations

### 1. Lazy Loading
- Currency config loaded on first access
- Static caching prevents repeated file reads

### 2. Database Indexes
- Indexes on `base_code` and `target_code`
- Unique constraint prevents duplicate queries

### 3. Queue Jobs
- Asynchronous processing
- Prevents blocking main application
- Parallel processing possible

### 4. Efficient Queries
- Uses Eloquent efficiently
- Minimal database queries
- Upsert operations for batch updates

### 5. Caching Opportunities
- Currency list (already cached in memory)
- Exchange rates (can be cached)
- Configuration values

## Testing Strategy

### Unit Tests
- Service layer logic
- Repository methods
- DTO formatting
- Currency validation

### Feature Tests
- API endpoints
- Conversion functionality
- Admin routes
- Authentication

### Integration Tests
- Queue job processing
- API integration (with mocks)
- Database operations
- Scheduler execution

### Test Structure
```
tests/
├── Unit/
│   └── Currency/
│       ├── ConverterServiceTest.php
│       └── CurrencyConfigServiceTest.php
└── Feature/
    └── Currency/
        ├── ConversionTest.php
        └── ExchangeRatesTest.php
```

## Module Registration

### Service Provider

`CurrencyServiceProvider` registers the module:

```php
class CurrencyServiceProvider extends ServiceProvider {
    public function register(): void {
        // Bind interfaces to implementations
        $this->app->bind(CurrencyApiInterface::class, CurrencyApiRepository::class);
        $this->app->bind(ExchangeRateRepositoryInterface::class, ExchangeRateRepository::class);
    }

    public function boot(): void {
        // Load configuration
        $this->mergeConfigFrom(__DIR__ . '/../Configuration/api.php', 'currency.api');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([CurrencyRatesSync::class]);
        }
    }
}
```

**Registered in:** `config/app.php` or `bootstrap/providers.php`

## Error Handling

### Exception Hierarchy

```
Exception
├── CurrencyApiException (Custom)
│   └── HTTP errors, API failures
├── InvalidArgumentException
│   └── Invalid currency codes
└── RuntimeException
    └── Missing exchange rates
```

### Error Flow

```
1. Exception Thrown
   ↓
2. Caught in Controller/Job
   ↓
3. Logged (if needed)
   ↓
4. User-Friendly Message
   ↓
5. Returned to User
```

## Scalability Considerations

### Current Architecture Supports

- **Horizontal Scaling**: Stateless application
- **Queue Workers**: Multiple workers can process jobs
- **Database**: Can be scaled with read replicas
- **Caching**: Can add Redis for better performance

### Future Enhancements

- **Rate Caching**: Cache frequently accessed rates
- **API Rate Limiting**: Implement rate limiting for API endpoints
- **Monitoring**: Add application monitoring
- **Logging**: Enhanced logging and analytics
- **API Versioning**: If API endpoints are exposed

## Summary

The Currency Converter application uses:

- **Modular Architecture**: Self-contained, maintainable module
- **Repository Pattern**: Clean data access abstraction
- **Service Layer**: Reusable business logic
- **Dependency Injection**: Loose coupling, testability
- **DTO Pattern**: Type-safe data transfer
- **Queue System**: Asynchronous processing
- **Modern Frontend**: React with Inertia.js

This architecture provides:
- ✅ Maintainability
- ✅ Testability
- ✅ Scalability
- ✅ Security
- ✅ Performance

