# Setup Guide

Complete step-by-step guide for setting up the Currency Converter application.

## Prerequisites

Before starting, ensure you have the following installed:

- **PHP** 8.4 or higher
- **Composer** 2.0+
- **Node.js** 18+ and npm
- **MySQL** 8.0+ (or compatible database)
- **Git**

### Verify Prerequisites

```bash
# Check PHP version
php -v  # Should be 8.4+

# Check Composer
composer --version  # Should be 2.0+

# Check Node.js
node -v  # Should be 18+

# Check npm
npm -v

# Check MySQL
mysql --version  # Should be 8.0+
```

## Installation Steps

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd currency-converter-test-task
```

### Step 2: Install PHP Dependencies

```bash
cd src
composer install
```

This will install all Laravel and PHP dependencies.

### Step 3: Install Frontend Dependencies

```bash
npm install
```

This will install React, Inertia.js, Tailwind CSS, and other frontend dependencies.

### Step 4: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Environment Variables

Edit the `.env` file with your settings:

```env
# Application
APP_NAME="Currency Converter"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=currency_converter
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Currency API (Required - get from https://freecurrencyapi.com/)
CURRENCY_API_KEY=your_api_key_here

# Queue
QUEUE_CONNECTION=database
```

**Important:** Get your API key from https://freecurrencyapi.com/

See [Configuration Guide](CONFIGURATION.md) for all available options.

### Step 6: Create Database

Create a MySQL database:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE currency_converter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Or use a database management tool like phpMyAdmin or MySQL Workbench.

### Step 7: Run Database Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- `users` - User authentication
- `cache` - Cache storage
- `jobs` - Queue jobs
- `failed_jobs` - Failed queue jobs
- `sessions` - Session storage (if using database sessions)
- `exchange_rates` - Exchange rate data

### Step 8: Build Frontend Assets

**For Production:**
```bash
npm run build
```

**For Development (with hot reload):**
```bash
npm run dev
```

Keep this running in a separate terminal for development.

### Step 9: Start Queue Worker

The queue worker is essential for exchange rate synchronization:

```bash
php artisan queue:work
```

**Important:** Keep this running in a separate terminal. The queue worker processes jobs asynchronously.

**For Production:** Use a process manager like Supervisor to keep the queue worker running automatically.

### Step 10: Setup Scheduler (Cron)

The scheduler runs daily exchange rate synchronization. Add to your crontab:

```bash
crontab -e
```

Add this line (replace `/path-to-project/src` with your actual path):

```bash
* * * * * cd /path-to-project/src && php artisan schedule:run >> /dev/null 2>&1
```

This runs the scheduler every minute. Laravel will determine which scheduled tasks need to run.

**Verify cron is working:**
```bash
# Check if cron is running
ps aux | grep cron

# Test scheduler manually
php artisan schedule:run
```

### Step 11: Run Initial Rate Sync

Fetch exchange rates for all supported currencies:

```bash
php artisan app:currency-rates-sync
```

This dispatches queue jobs to fetch rates. Check the queue worker terminal to see jobs processing.

**Note:** This may take several minutes depending on the number of currencies and delays configured.

### Step 12: Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` to see the application.

## Verification

### 1. Test Currency Converter

1. Visit `http://localhost:8000/converter`
2. Enter an amount (e.g., 100)
3. Select currencies (e.g., USD to EUR)
4. Click "Convert"
5. Verify conversion result appears

### 2. Test Admin Panel

1. Register a new account or login
2. Visit `http://localhost:8000/admin/exchange-rates`
3. Verify exchange rates are displayed
4. Try selecting different base currencies

### 3. Check Queue Jobs

1. Check queue worker terminal for job processing
2. Verify jobs complete successfully
3. Check for any error messages

### 4. Verify Database

```bash
php artisan tinker
```

```php
// Check exchange rates
\App\Modules\Currency\Models\ExchangeRate::count();

// Check latest rates
\App\Modules\Currency\Models\ExchangeRate::latest('date')->first();
```

## Database Setup Details

### Migration Files

The application includes these migrations:

1. **Laravel Core Migrations:**
   - `0001_01_01_000000_create_users_table.php` - User authentication
   - `0001_01_01_000001_create_cache_table.php` - Cache storage
   - `0001_01_01_000002_create_jobs_table.php` - Queue jobs

2. **Currency Module Migration:**
   - `2025_12_23_175310_create_exchange_rates_table.php` - Exchange rates

### Exchange Rates Table Structure

```sql
CREATE TABLE exchange_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    base_code VARCHAR(3) NOT NULL,
    target_code VARCHAR(3) NOT NULL,
    rate DECIMAL(15,8) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY er_base_target_date_unique (base_code, target_code, date),
    INDEX (base_code),
    INDEX (target_code)
);
```

**Key Points:**
- Uses currency codes directly (no foreign keys)
- Unique constraint prevents duplicate rates
- Indexes for performance
- Stores historical rates by date

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Fresh migration (drop all tables and re-run)
php artisan migrate:fresh

# See migration status
php artisan migrate:status
```

## Queue Worker Setup

### Development

Run queue worker manually:

```bash
php artisan queue:work
```

**Options:**
```bash
# Process specific queue
php artisan queue:work --queue=default

# Set timeout
php artisan queue:work --timeout=60

# Process single job (for testing)
php artisan queue:work --once

# Verbose output
php artisan queue:work --verbose
```

### Production with Supervisor

Create supervisor config file: `/etc/supervisor/conf.d/currency-converter-worker.conf`

```ini
[program:currency-converter-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/src/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-project/src/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start currency-converter-worker:*
```

### Queue Connection Options

**Database Queue (Default):**
```env
QUEUE_CONNECTION=database
```

**Redis Queue (Recommended for Production):**
```env
QUEUE_CONNECTION=redis
```

See [Configuration Guide](CONFIGURATION.md) for Redis setup.

### Monitoring Queue

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry {job-id}

# Clear failed jobs
php artisan queue:flush
```

## Scheduler Configuration

### Cron Setup

The Laravel scheduler requires a single cron entry:

```bash
* * * * * cd /path-to-project/src && php artisan schedule:run >> /dev/null 2>&1
```

This runs every minute. Laravel checks which scheduled tasks need to run.

### Scheduled Tasks

The application schedules:

```php
// Daily at 00:00 UTC
Schedule::command('app:currency-rates-sync')
    ->daily()
    ->at('00:00')
    ->timezone('UTC');
```

### Testing Scheduler

```bash
# List scheduled tasks
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run

# Test specific command
php artisan app:currency-rates-sync
```

### Scheduler Logging

Check scheduler execution in logs:

```bash
tail -f storage/logs/laravel.log | grep schedule
```

## Development Workflow

### Using Development Script

The project includes a convenient development script:

```bash
composer run dev
```

This starts:
- Laravel development server (port 8000)
- Queue worker
- Log viewer (Pail)
- Vite dev server (hot reload)

All in one command with color-coded output.

### Manual Development Setup

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work
```

**Terminal 3 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 4 - Logs (Optional):**
```bash
php artisan pail
```

## Troubleshooting Setup

### Database Connection Issues

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check database exists
mysql -u username -p -e "SHOW DATABASES;"
```

### Queue Not Processing

```bash
# Check queue worker is running
ps aux | grep "queue:work"

# Check failed jobs
php artisan queue:failed

# Restart queue worker
# Stop current worker (Ctrl+C) and restart
php artisan queue:work
```

### Scheduler Not Running

```bash
# Verify cron entry
crontab -l

# Test scheduler manually
php artisan schedule:run

# Check cron is running
ps aux | grep cron
```

### Frontend Not Loading

```bash
# Rebuild assets
npm run build

# Clear cache
php artisan cache:clear
php artisan view:clear

# Check for errors
npm run dev
# Check browser console
```

## Next Steps

After setup is complete:

1. Review [Configuration Guide](CONFIGURATION.md) for advanced settings
2. Read [Architecture Documentation](ARCHITECTURE.md) to understand the system
3. Check [Troubleshooting Guide](TROUBLESHOOTING.md) if you encounter issues
4. Test the application thoroughly
5. Configure for production (see Configuration Guide)

## Additional Resources

- [Laravel Installation](https://laravel.com/docs/installation)
- [Laravel Queues](https://laravel.com/docs/queues)
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)

