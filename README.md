# Currency Converter Application

A Laravel-based currency converter application with real-time exchange rates, featuring automatic daily synchronization and an admin panel for rate management.

## Features

- 💱 **Real-time Currency Conversion** - Convert between 30+ major currencies
- 🔄 **Automatic Rate Synchronization** - Daily exchange rate updates via queue jobs
- 📊 **Admin Panel** - View and manage exchange rates with authentication
- 🏗️ **Module-based Architecture** - Self-contained, maintainable code structure
- ⚡ **Queue-based Processing** - Asynchronous rate synchronization
- 🎨 **Modern Frontend** - React with Inertia.js and Tailwind CSS
- 🔒 **Secure** - Input validation, authentication, and error handling

## Requirements

- **PHP** 8.4 or higher
- **Composer** 2.0+
- **Node.js** 18+ and npm
- **MySQL** 8.0+ (or compatible database)
- **Redis** (optional, recommended for production queues)

## Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd converter/src
```

### 2. Install Dependencies

```bash
cd src
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file with your configuration (see [Configuration Guide](docs/CONFIGURATION.md))

### 4. Database Setup

```bash
php artisan migrate
```

### 5. Build Frontend Assets

```bash
# Production build
npm run build

# Development (with hot reload)
npm run dev
```

### 6. Start Queue Worker

```bash
php artisan queue:work
```

**Important:** The queue worker must be running for exchange rate synchronization to work.

### 7. Setup Scheduler (Cron)

Add to your crontab:

```bash
* * * * * cd /path-to-project/src && php artisan schedule:run >> /dev/null 2>&1
```

This runs the scheduler every minute, which will execute scheduled tasks (like daily rate sync).

### 8. Run Initial Rate Sync

```bash
php artisan app:currency-rates-sync
```

This will dispatch jobs to fetch exchange rates for all supported currencies.

### 9. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` to see the application.

## Documentation

- **[Setup Guide](docs/SETUP.md)** - Detailed installation and configuration instructions
- **[Configuration](docs/CONFIGURATION.md)** - Environment variables and configuration options
- **[Architecture](docs/ARCHITECTURE.md)** - System design, patterns, and structure
- **[Troubleshooting](docs/TROUBLESHOOTING.md)** - Common issues and solutions

## Project Structure

```
currency-converter-test-task/
├── src/                          # Laravel application
│   ├── app/
│   │   └── Modules/
│   │       └── Currency/         # Currency module
│   ├── database/
│   │   └── migrations/           # Database migrations
│   ├── resources/
│   │   └── js/                   # React frontend
│   └── routes/                   # Route definitions
├── docs/                         # Documentation
└── README.md                     # This file
```

## Key Commands

```bash
# Run queue worker
php artisan queue:work

# Sync exchange rates manually
php artisan app:currency-rates-sync

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear

# Development mode (server + queue + logs + vite)
composer run dev
```

## Application Routes

### Public Routes
- `GET /converter` - Currency converter page
- `POST /converter` - Convert currency

### Admin Routes (Authentication Required)
- `GET /admin/exchange-rates` - View exchange rates (defaults to USD)
- `GET /admin/exchange-rates/{currency}` - View rates for specific currency
- `POST /api/exchange-rates/sync-daily` - Manually trigger rate sync

## Supported Currencies

The application supports 30+ major currencies including:
- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)
- JPY (Japanese Yen)
- AUD (Australian Dollar)
- CAD (Canadian Dollar)
- CHF (Swiss Franc)
- CNY (Chinese Yuan)
- And more...

See `src/app/Modules/Currency/Configuration/currencies.php` for the complete list.

## Technology Stack

### Backend
- **Laravel 12** - PHP framework
- **Guzzle HTTP** - API client
- **MySQL** - Database
- **Laravel Queue** - Job processing

### Frontend
- **React 18** - UI library
- **Inertia.js** - Server-driven SPA framework
- **Tailwind CSS** - Styling
- **Vite** - Build tool

## Development

### Running in Development Mode

The project includes a convenient development script:

```bash
composer run dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server (hot reload)

### Code Quality

- **PHP Code Style**: Laravel Pint
- **Testing**: PHPUnit
- **Architecture**: Module-based with clear separation of concerns

## Queue & Scheduler

### Queue Worker

The application uses Laravel's queue system for asynchronous processing:

```bash
php artisan queue:work
```

**Production:** Use a process manager like Supervisor to keep the queue worker running.

### Scheduler

The scheduler automatically syncs exchange rates daily at 00:00 UTC:

```php
Schedule::command('app:currency-rates-sync')
    ->daily()
    ->at('00:00')
    ->timezone('UTC');
```

Ensure cron is configured to run `php artisan schedule:run` every minute.

## Configuration

### Required Environment Variables

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=currency_converter
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Currency API (get key from https://freecurrencyapi.com/)
CURRENCY_API_KEY=your_api_key_here

# Queue
QUEUE_CONNECTION=database
```

See [Configuration Guide](docs/CONFIGURATION.md) for all options.

## Troubleshooting

Common issues and solutions:

- **Queue jobs not processing**: Ensure `php artisan queue:work` is running
- **Rates not updating**: Check API key and queue worker status
- **Scheduler not running**: Verify cron is configured correctly

See [Troubleshooting Guide](docs/TROUBLESHOOTING.md) for detailed solutions.

## License

MIT

## Support

For issues, questions, or contributions, please refer to the documentation in the `docs/` folder.

