<?php

use App\Enums\User\SocialProvider;
use App\Http\Controllers\Web\LoginSocialController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    \abort(403);
});

Route::prefix('/auth')
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->group(static function (Router $router) {
        $providerVariants = \sprintf('(%s)', \implode('|', SocialProvider::values()));
        $router
            ->get('/login/{provider}', [LoginSocialController::class, 'login'])
            ->where('provider', $providerVariants)
            ->name('social-login-login')
        ;
        $router
            ->get('/callback/{provider}', [LoginSocialController::class, 'callback'])
            ->where('provider', $providerVariants)
            ->name('social-login-callback')
        ;
        $router
            ->get('/social/{provider}', [LoginSocialController::class, 'callback'])
            ->where('provider', $providerVariants)
            ->name('social-login-callback-old')
        ;
    })
;
