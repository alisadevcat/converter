# Configuration Guide

Complete guide to configuring the Currency Converter application.

## Environment Variables

All configuration is done via environment variables in the `.env` file.

### Required Variables

#### Application Configuration

```env
APP_NAME="Currency Converter"
APP_ENV=local
APP_KEY=base64:...  # Generated with: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
```

#### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=currency_converter
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Currency API Configuration

```env
# Required: Get your API key from https://freecurrencyapi.com/
CURRENCY_API_KEY=your_api_key_here
```

**Getting an API Key:**
1. Visit https://freecurrencyapi.com/
2. Sign up for a free account
3. Get your API key from the dashboard
4. Add it to your `.env` file

#### Queue Configuration

```env
# Use 'database' for development, 'redis' for production
QUEUE_CONNECTION=database
```

### Optional Variables

#### Currency API Advanced Settings

```env
# Override default API base URL (default: https://api.freecurrencyapi.com)
CURRENCY_API_BASE_URL=https://api.freecurrencyapi.com

# Override API endpoint (default: v1/latest)
CURRENCY_API_ENDPOINT=v1/latest

# Request timeout in seconds (default: 30)
CURRENCY_API_TIMEOUT=30

# Default base currency when none specified (default: USD)
CURRENCY_DEFAULT_BASE=USD

# Delay between API requests in seconds (default: 3)
# Increase if you encounter rate limit errors
CURRENCY_REQUEST_DELAY=3

# Delay between queue job dispatches in seconds (default: 5)
# Increase if you encounter rate limit errors
CURRENCY_JOB_DELAY=5
```

#### Session Configuration

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

#### Cache Configuration

```env
CACHE_DRIVER=file
# For production, use Redis:
# CACHE_DRIVER=redis
```

#### Logging Configuration

```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

#### Telescope (Optional)

```env
# Disable if Telescope is not installed
TELESCOPE_ENABLED=false
```

### Production Configuration

For production environments, use these settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use Redis for better performance
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Increase delays to prevent rate limiting
CURRENCY_REQUEST_DELAY=5
CURRENCY_JOB_DELAY=10

# Use secure session settings
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
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

## Queue Configuration

### Database Queue (Default)

Uses Laravel's database queue driver:

```env
QUEUE_CONNECTION=database
```

**Pros:**
- No additional setup required
- Works out of the box
- Good for development

**Cons:**
- Slower than Redis
- Not ideal for high-volume production

### Redis Queue (Recommended for Production)

For better performance in production:

```env
QUEUE_CONNECTION=redis
```

**Setup:**
1. Install Redis server
2. Install PHP Redis extension: `pecl install redis`
3. Update `.env` with Redis connection:
   ```env
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

## Cache Configuration

### File Cache (Default)

```env
CACHE_DRIVER=file
```

Good for development and small deployments.

### Redis Cache (Production)

```env
CACHE_DRIVER=redis
```

Better performance for production environments.

## Session Configuration

### Database Sessions (Recommended)

```env
SESSION_DRIVER=database
```

**Setup:**
1. Create sessions table: `php artisan session:table`
2. Run migration: `php artisan migrate`

### File Sessions

```env
SESSION_DRIVER=file
```

Simpler but less scalable.

## Environment-Specific Configuration

### Development

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
QUEUE_CONNECTION=database
CACHE_DRIVER=file
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=false
LOG_LEVEL=info
QUEUE_CONNECTION=database
CACHE_DRIVER=redis
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Verifying Configuration

### Check Configuration Values

```bash
php artisan tinker
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

```php
php artisan tinker
```

```php
$api = app(\App\Modules\Currency\Contracts\Api\CurrencyApiInterface::class);
$result = $api->fetchLatestRates('USD');
// Should return array with 'data' key containing rates
```

## Configuration Best Practices

1. **Never commit `.env` file** - Use `.env.example` as template
2. **Use different keys per environment** - Dev, staging, production
3. **Rotate API keys regularly** - Especially if exposed
4. **Use strong database passwords** - Especially in production
5. **Enable HTTPS in production** - Set `APP_URL` to `https://`
6. **Use Redis in production** - Better performance for queues and cache
7. **Monitor rate limits** - Adjust delays based on API plan
8. **Set appropriate timeouts** - Balance between reliability and speed

## Troubleshooting Configuration

### Configuration Not Updating

```bash
# Clear config cache
php artisan config:clear

# Clear all caches
php artisan optimize:clear
```

### Environment Variables Not Loading

1. Ensure `.env` file exists in `src/` directory
2. Check file permissions
3. Verify variable names match exactly (case-sensitive)
4. Restart queue worker if running

### API Key Not Working

1. Verify key is correct (no extra spaces)
2. Check API key is active at https://freecurrencyapi.com/
3. Verify API key has sufficient quota
4. Check API base URL is correct

## Additional Resources

- [Laravel Configuration Documentation](https://laravel.com/docs/configuration)
- [FreeCurrencyAPI Documentation](https://freecurrencyapi.com/docs)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)

