<?php
namespace App\Http;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        // ...
        'has.wallet' => \App\Http\Middleware\EnsureHasWallet::class,
    ];
}