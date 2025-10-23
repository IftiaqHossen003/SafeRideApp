# 🚗 SafeRide Application

**Real-time GPS Tracking & Safety Platform with Traccar Integration**

<p align="center">
<a href="https://github.com/IftiaqHossen003/SafeRideApp/actions"><img src="https://github.com/IftiaqHossen003/SafeRideApp/workflows/CI%2FCD%20Pipeline/badge.svg" alt="Build Status"></a>
<a href="https://github.com/IftiaqHossen003/SafeRideApp"><img src="https://img.shields.io/github/license/IftiaqHossen003/SafeRideApp" alt="License"></a>
<a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version"></a>
<a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.2-blue.svg" alt="PHP Version"></a>
</p>

## 📖 About SafeRide

SafeRide is a comprehensive ride-sharing safety application built with Laravel 12 that integrates with **Traccar GPS** server for real-time location tracking. The application provides essential safety features including SOS alerts, live map view, trip tracking, volunteer responder system, and trusted contact notifications.

### ✨ Key Features

- **🗺️ Live Map View**: Real-time GPS tracking with Mapbox integration
- **📡 Traccar GPS Integration**: Device mapping, position syncing, and webhook support
- **🚨 SOS Alert System**: Emergency alerts to trusted contacts with volunteer responders
- **📍 Real-time Broadcasting**: WebSocket updates via Laravel Echo + Pusher
- **👥 Trusted Contacts**: Manage and notify emergency contacts
- **🔐 Device Management**: One-click GPS device linking per user
- **📊 Trip History**: Complete route visualization with GPS metrics
- **⚡ Route Anomaly Detection**: Deviation alerts and unsafe area warnings
- **🎯 Role-based Access**: Admin, Volunteers, and Users with proper permissions

## Environment Variables

The following environment variables are required for the SafeRide application:

### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saferide
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

### Pusher Configuration (Real-time features)
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### Twilio Configuration (SMS notifications)
```env
TWILIO_SID=your_twilio_account_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_PHONE_NUMBER=+1234567890
```

### Email Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Application Configuration
```env
APP_NAME=SafeRide
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Local Development Setup

Follow these step-by-step instructions to set up SafeRide on your local machine:

### Prerequisites

- **PHP 8.2 or 8.3** with required extensions
- **Composer** (latest version)
- **Node.js 18+** and npm
- **MySQL 8.0+** or **SQLite** (for development)
- **Git**

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/IftiaqHossen003/SafeRideApp.git
   cd SafeRideApp
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   # Copy environment file
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

5. **Configure your .env file**
   - Update database credentials
   - Add Pusher credentials (optional for real-time features)
   - Add Twilio credentials (optional for SMS notifications)
   - Configure email settings

6. **Database setup**
   ```bash
   # Create SQLite database (for development)
   touch database/database.sqlite
   
   # OR create MySQL database
   mysql -u root -p -e "CREATE DATABASE saferide;"
   
   # Run migrations
   php artisan migrate
   
   # Seed with test data
   php artisan db:seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   # Option 1: Laravel development server
   php artisan serve
   
   # Option 2: Full development environment with watch mode
   composer run dev
   ```

### Quick Setup (One Command)

For rapid setup, use the automated setup script:

```bash
composer run setup
```

This command will:
- Install all dependencies
- Copy environment file
- Generate application key
- Run migrations
- Build frontend assets

### Development Tools

**Run tests:**
```bash
# All tests
php artisan test

# Specific test suite
php artisan test --testsuite=Feature
```

**Code formatting:**
```bash
./vendor/bin/pint
```

**Queue worker (for background jobs):**
```bash
php artisan queue:work
```

**Asset development with hot reload:**
```bash
npm run dev
```

### Test Accounts

After seeding, you can use these test accounts:

- **Admin**: admin@saferide.com / password
- **Volunteer**: sarah@volunteer.com / password  
- **User**: john@example.com / password

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
