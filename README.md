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

- **Docker** and **Docker Compose**
- **Node.js** 18+ and npm (for frontend build/dev server, can also run in Docker if needed)

## Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd converter
```

### 2. Setup Environment Files

```bash
# Copy MySQL environment file
cp env/mysql.env.example env/mysql.env

# Copy Laravel environment file
cp src/.env.example src/.env
```

Edit `env/mysql.env` and `src/.env` with your configuration (see [Configuration Guide](docs/CONFIGURATION.md))

### 3. Build Docker Images

```bash
docker compose build
```

This will build the custom PHP and Composer images defined in the `dockerfiles/` directory.

### 4. Start Docker Containers

```bash
docker compose up -d
```

This will start:
- **Nginx** (web server on port 8000)
- **PHP-FPM** (Laravel application)
- **MySQL 8.0** (database on port 3316)

The **composer** and **artisan** services are available as run commands (not persistent containers).

### 5. Install PHP Dependencies

```bash
docker compose run --rm composer install
```

### 6. Install Frontend Dependencies

```bash
cd src
npm install
```

### 7. Generate Application Key

```bash
docker compose run --rm artisan key:generate
```

### 8. Run Database Migrations

```bash
docker compose run --rm artisan migrate
```

### 9. Build Frontend Assets

```bash
# Production build
cd src && npm run build

# Development (with hot reload, in separate terminal)
cd src && npm run dev
```

### 10. Start Queue Worker

```bash
docker compose run --rm artisan queue:work
```

**Important:** The queue worker must be running for exchange rate synchronization to work. In production, consider running this as a background service or add it as a service in docker-compose.yml.

### 11. Setup Scheduler (Cron)

The scheduler can run inside the Docker container. Add to a cron job on your host:

```bash
* * * * * cd /path-to-project && docker compose run --rm artisan schedule:run >> /dev/null 2>&1
```

Or run the scheduler inside a container with a cron service.

### 12. Run Initial Rate Sync

```bash
docker compose run --rm artisan app:currency-rates-sync
```

This will dispatch jobs to fetch exchange rates for all supported currencies.

### 13. Access the Application

Visit `http://localhost:8000` to see the application.

**Note:** MySQL is accessible on port `3316` (not the default 3306) to avoid conflicts.

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

### Docker Commands

```bash
# Build Docker images
docker compose build

# Start containers
docker compose up -d

# Stop containers
docker compose down

# View logs
docker compose logs -f

# Execute Laravel commands
docker compose run --rm artisan <command>
docker compose run --rm composer <command>

# Access PHP container shell
docker compose exec php sh

# Access MySQL
docker compose exec mysql mysql -u root -p
```

### Application Commands

```bash
# Run queue worker
docker compose run --rm artisan queue:work

# Sync exchange rates manually
docker compose run --rm artisan app:currency-rates-sync

# Run tests
docker compose run --rm artisan test

# Clear cache
docker compose run --rm artisan cache:clear
docker compose run --rm artisan config:clear

# Run migrations
docker compose run --rm artisan migrate

# Development mode (if running locally)
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

**`src/.env` file:**

```env
# Application
APP_NAME="Currency Converter"
APP_ENV=local
APP_KEY=  # Generated with: docker compose run --rm artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (Docker MySQL container - use service name 'mysql' as host)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=currency_converter
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Currency API (Required - get key from https://freecurrencyapi.com/)
CURRENCY_API_KEY=your_api_key_here

# Queue
QUEUE_CONNECTION=database
```

**`env/mysql.env` file:**

```env
MYSQL_DATABASE=currency_converter
MYSQL_USER=your_username
MYSQL_PASSWORD=your_password
MYSQL_ROOT_PASSWORD=your_root_password
```

**Important:**
- Make sure the database credentials match between `src/.env` and `env/mysql.env`
- Get your API key from https://freecurrencyapi.com/
- Use `DB_HOST=mysql` (not `127.0.0.1`) to connect to the Docker MySQL container

### Optional Environment Variables

```env
# Currency API Advanced Settings
CURRENCY_API_BASE_URL=https://api.freecurrencyapi.com
CURRENCY_API_ENDPOINT=v1/latest
CURRENCY_API_TIMEOUT=30
CURRENCY_DEFAULT_BASE=USD
CURRENCY_REQUEST_DELAY=3
CURRENCY_JOB_DELAY=5

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=file

# Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### Getting a Currency API Key

1. Visit https://freecurrencyapi.com/
2. Sign up for a free account
3. Get your API key from the dashboard
4. Add it to your `src/.env` file as `CURRENCY_API_KEY=your_key_here`

See [Configuration Guide](docs/CONFIGURATION.md) for detailed options and production settings.

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

