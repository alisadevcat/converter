# Configuration Guide

Local development configuration guide for the Currency Converter application.

## Environment Variables

All configuration is done via environment variables. The application uses two environment files:

1. **`src/.env`** - Laravel application configuration
2. **`env/mysql.env`** - MySQL Docker container configuration

### Required Variables

#### Application Configuration (`src/.env`)

```env
APP_NAME="Currency Converter"
APP_ENV=local
APP_KEY=  # Generated with: docker compose run --rm artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
```

#### Database Configuration (`src/.env`)

**Important:** Use `mysql` as the host to connect to the Docker MySQL container.

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=currency_converter
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### MySQL Docker Configuration (`env/mysql.env`)

```env
MYSQL_DATABASE=currency_converter
MYSQL_USER=your_username
MYSQL_PASSWORD=your_password
MYSQL_ROOT_PASSWORD=your_root_password
```

**Note:** Database credentials must match between `src/.env` and `env/mysql.env`.

#### Currency API Configuration (`src/.env`)

```env
# Required: Get your API key from https://freecurrencyapi.com/
CURRENCY_API_KEY=your_api_key_here
```

**Getting an API Key:**
1. Visit https://freecurrencyapi.com/
2. Sign up for a free account
3. Get your API key from the dashboard
4. Add it to your `src/.env` file

#### Queue Configuration (`src/.env`)

```env
QUEUE_CONNECTION=database
```

### Optional Variables

#### Currency API Advanced Settings (`src/.env`)

These have sensible defaults and usually don't need to be changed:

```env
# Override default API base URL (default: https://api.freecurrencyapi.com)
# CURRENCY_API_BASE_URL=https://api.freecurrencyapi.com

# Override API endpoint (default: v1/latest)
# CURRENCY_API_ENDPOINT=v1/latest

# Request timeout in seconds (default: 30)
# CURRENCY_API_TIMEOUT=30

# Default base currency when none specified (default: USD)
# CURRENCY_DEFAULT_BASE=USD

# Delay between API requests in seconds (default: 3)
# Increase if you encounter rate limit errors
# CURRENCY_REQUEST_DELAY=3

# Delay between queue job dispatches in seconds (default: 5)
# Increase if you encounter rate limit errors
# CURRENCY_JOB_DELAY=5
```

#### Session Configuration (`src/.env`)

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

#### Cache Configuration (`src/.env`)

```env
CACHE_STORE=database
```

#### Logging Configuration (`src/.env`)

```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

## Configuration Files

### Currency Module Configuration

Located at: `src/app/Modules/Currency/Configuration/api.php`

This file contains default values and can be overridden via environment variables. You typically don't need to modify this file directly.

**Default Values:**
- Base URL: `https://api.freecurrencyapi.com`
- Endpoint: `v1/latest`
- Timeout: `30` seconds
- Default Base Currency: `USD`
- Request Delay: `3` seconds
- Job Delay: `5` seconds

### Currency List

Located at: `src/app/Modules/Currency/Configuration/currencies.php`

This file contains the hardcoded list of supported currencies. To add or remove currencies:

1. Edit `src/app/Modules/Currency/Configuration/currencies.php`
2. Add/remove entries in the format:
   ```php
   'CURRENCY_CODE' => [
       'name' => 'Currency Name',
       'symbol' => 'Symbol',
   ],
   ```
3. Clear config cache: `php artisan config:clear`

## Rate Limiting Configuration

The application includes built-in delays to prevent API rate limiting:

### Request Delays

- **Request Delay** (`CURRENCY_REQUEST_DELAY`): Delay between individual API requests
- **Job Delay** (`CURRENCY_JOB_DELAY`): Delay between queue job dispatches
- **Rate Limit Retry Delay**: 5 minutes (300 seconds) - hardcoded in job

### Adjusting Delays

If you encounter rate limit errors (HTTP 429):

1. Increase `CURRENCY_REQUEST_DELAY` to 5-10 seconds
2. Increase `CURRENCY_JOB_DELAY` to 10-15 seconds
3. Check your API plan limits at https://freecurrencyapi.com/

### Free API Plan Limits

The free plan typically allows:
- Limited requests per day
- Rate limits per minute

Adjust delays accordingly based on your plan.

## Local Development Setup

### Default Configuration

The default configuration is optimized for local development with Docker:

- **Queue:** Database queue (no additional setup required)
- **Cache:** Database cache (persists across container restarts)
- **Session:** Database sessions
- **Logging:** File-based logging (view with `docker compose logs -f`)

## Verifying Configuration

### Check Configuration Values

```bash
docker compose run --rm artisan tinker
```

```php
// Check API configuration
config('currency.api.api_key')
config('currency.api.base_url')
config('currency.api.timeout')

// Check queue connection
config('queue.default')

// Check database connection
DB::connection()->getPdo();
```

### Test API Connection

```bash
docker compose run --rm artisan tinker
```

```php
$api = app(\App\Modules\Currency\Contracts\Api\CurrencyApiInterface::class);
$result = $api->fetchLatestRates('USD');
// Should return array with 'data' key containing rates
```

### Test Database Connection

```bash
# Access MySQL directly
docker compose exec mysql mysql -u root -p

# Or test via Laravel
docker compose run --rm artisan tinker
>>> DB::connection()->getPdo();
```

## Configuration Best Practices

1. **Never commit `.env` files** - Both `src/.env` and `env/mysql.env` are ignored by git
2. **Use `.env.example` as template** - Copy and customize for your setup
3. **Match database credentials** - Ensure `src/.env` and `env/mysql.env` have matching values
4. **Use Docker service names** - Use `DB_HOST=mysql` (not `127.0.0.1`) to connect to Docker MySQL
5. **Get API key early** - Required for exchange rate synchronization
6. **Monitor rate limits** - Adjust `CURRENCY_REQUEST_DELAY` and `CURRENCY_JOB_DELAY` if needed
7. **Keep debug enabled** - `APP_DEBUG=true` is fine for local development

## Troubleshooting Configuration

### Configuration Not Updating

```bash
# Clear config cache
docker compose run --rm artisan config:clear

# Clear all caches
docker compose run --rm artisan optimize:clear
```

### Environment Variables Not Loading

1. Ensure `src/.env` file exists
2. Ensure `env/mysql.env` file exists
3. Verify variable names match exactly (case-sensitive)
4. Restart Docker containers: `docker compose restart`
5. Restart queue worker if running

### Database Connection Issues

1. **Check MySQL container is running:**
   ```bash
   docker compose ps
   ```

2. **Verify DB_HOST in `src/.env`:**
   - Should be `DB_HOST=mysql` (not `127.0.0.1`)
   - This uses Docker's internal network

3. **Check credentials match:**
   - `src/.env` DB_USERNAME/DB_PASSWORD must match `env/mysql.env` MYSQL_USER/MYSQL_PASSWORD

4. **Test connection:**
   ```bash
   docker compose exec mysql mysql -u root -p
   ```

### API Key Not Working

1. Verify key is correct (no extra spaces or quotes)
2. Check API key is active at https://freecurrencyapi.com/
3. Verify API key has sufficient quota
4. Check logs for errors: `docker compose logs -f php`
5. Test API connection manually (see Verifying Configuration section)

### Rate Limit Errors

If you encounter HTTP 429 (rate limit) errors:

1. Increase delays in `src/.env`:
   ```env
   CURRENCY_REQUEST_DELAY=5
   CURRENCY_JOB_DELAY=10
   ```

2. Check your API plan limits at https://freecurrencyapi.com/
3. Monitor job logs: `docker compose logs -f`

## Docker-Specific Notes

### Container Networking

- Use service names (e.g., `mysql`, `php`) as hostnames when connecting between containers
- `DB_HOST=mysql` connects to the MySQL container via Docker's internal network
- External access: MySQL is exposed on port `3316` (not default 3306)

### Persistent Data

- Database data is stored in Docker volume `db_data`
- Application code is mounted from `./src` directory (live editing supported)
- Logs are in `src/storage/logs` directory

### Running Commands

All Laravel commands should be run via Docker:

```bash
# Instead of: php artisan migrate
docker compose run --rm artisan migrate

# Instead of: composer install
docker compose run --rm composer install
```

## Additional Resources

- [Laravel Configuration Documentation](https://laravel.com/docs/configuration)
- [FreeCurrencyAPI Documentation](https://freecurrencyapi.com/docs)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

