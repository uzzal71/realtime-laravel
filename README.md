# realtime-laravel
Laravel &amp; Realtime: Build Several Realtime Apps with Laravel

# Step: 6. Obtaining and Preparing the Laravel Structure Using Composer
```
composer create-project laravel/laravel RealtimeLaravel
```

Create database like "realtime_laravel"

Database connection changed .env file
```
DB_DATABASE=realtime_laravel
```
Database migration
```
php artisan migrate
```

# Step: 8. Adding Laravel UI and Generating Some Useful Components

```
composer require laravel/ui
php artisan ui bootstrap --auth
```