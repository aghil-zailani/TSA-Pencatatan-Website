<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login; // Import event Login
use App\Listeners\LogSuccessfulLogin; // Import listener kita
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
// ...

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ... event lain mungkin sudah ada di sini
        
        Login::class => [
            LogSuccessfulLogin::class,
        ],
    ];

    // ...
}