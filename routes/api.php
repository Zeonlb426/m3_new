<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\V1\AgeGroupController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegistrationController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Competition\CompetitionController;
use App\Http\Controllers\Api\V1\Competition\CompetitionThemeController;
use App\Http\Controllers\Api\V1\Competition\PartnerController;
use App\Http\Controllers\Api\V1\CompetitionWork\WorkController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\Location\CityController;
use App\Http\Controllers\Api\V1\Location\RegionController;
use App\Http\Controllers\Api\V1\MasterClass\CourseController;
use App\Http\Controllers\Api\V1\MasterClass\MasterClassController;
use App\Http\Controllers\Api\V1\Misc\CountersController;
use App\Http\Controllers\Api\V1\Misc\DocumentController;
use App\Http\Controllers\Api\V1\Misc\FeedbackController;
use App\Http\Controllers\Api\V1\Misc\TextsController;
use App\Http\Controllers\Api\V1\News\NewsController;
use App\Http\Controllers\Api\V1\Promo\SuccessHistoryController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SliderController;
use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Routing\Router;

Route::group(
    [
        'prefix' => 'v1',
        'as' => 'v1.'
    ],
    static function (Router $router) {
        $router->group(
            [
                'prefix' => 'auth',
                'as' => 'auth.'
            ],
            static function (Router $router) {
                $router->post('registration', RegistrationController::class)->name('registration');
                $router->post('login', [LoginController::class, 'login'])->name('login');
                $router->post('forgot-password', [ResetPasswordController::class, 'forgotPassword'])->name('forgot-password');
                $router->post('reset-password', [ResetPasswordController::class, 'resetPassword'])->name('reset-password');
                $router
                    ->middleware('auth:sanctum')
                    ->delete('logout', [LoginController::class, 'logout'])
                    ->name('logout')
                ;
            }
        );
        $router->get('/sliders', SliderController::class)->name('sliders');
        $router
            ->get('/search', SearchController::class)
            ->middleware('verify.auth')
            ->name('search')
        ;
        $router->post('/feedbacks', FeedbackController::class)->name('feedbacks');
        $router->get('/documents/{slug}', DocumentController::class)->name('document');
        $router->get('/file/{slug}', [DocumentController::class, 'getFile'])->name('file-document');
        $router->get('/counters', CountersController::class)->name('counters');
        $router->get('/texts', TextsController::class)->name('texts');
        $router->group(
            [
                'prefix' => 'location',
                'as' => 'location.'
            ],
            static function (Router $router) {
                $router->get('regions', RegionController::class)->name('regions');
                $router
                    ->get('cities/{regionId?}', CityController::class)
                    ->whereNumber('regionId')
                    ->name('cities')
                ;
            }
        );
        $router->group(
            [
                'prefix' => 'news',
                'as' => 'news.',
                'middleware' => 'verify.auth',
            ],
            static function (Router $router) {
                $router->get('/', [NewsController::class, 'index'])->name('index');
                $router->get('/{slug}', [NewsController::class, 'view'])->name('view');
            }
        );
        $router->group(
            [
                'prefix' => 'success-histories',
                'as' => 'success-histories.',
                'middleware' => 'verify.auth',
            ],
            static function (Router $router) {
                $router->get('/', [SuccessHistoryController::class, 'index'])->name('index');
                $router
                    ->get('/{id}', [SuccessHistoryController::class, 'view'])
                    ->whereNumber('id')
                    ->name('view')
                ;
            }
        );
        $router->group(
            [
                'prefix' => 'leads',
                'as' => 'leads.'
            ],
            static function (Router $router) {
                $router->get('/', [LeadController::class, 'index'])->name('index');
                $router->get('/{id}', [LeadController::class, 'view'])->name('view');
            }
        );
        $router->group(
            [
                'prefix' => 'age-groups',
                'as' => 'age-groups.'
            ],
            static function (Router $router) {
                $router->get('/', [AgeGroupController::class, 'index'])->name('index');
                $router
                    ->get('/{id}', [AgeGroupController::class, 'view'])
                    ->whereNumber('id')
                    ->name('view')
                ;
            }
        );
        $router->group(
            [
                'prefix' => 'master-classes',
                'as' => 'master-classes.',
                'middleware' => 'verify.auth',
            ],
            static function (Router $router) {
                $router->group(
                    [
                        'prefix' => 'courses',
                        'as' => 'courses.'
                    ],
                    static function (Router $router) {
                        $router->get('/', [CourseController::class, 'index'])->name('index');
                        $router->get('/{id}', [CourseController::class, 'view'])->name('view');
                    }
                );
                $router->group(
                    ['prefix' => '{id}'],
                    static function (Router $router) {
                        $router->get('/courses', [CourseController::class, 'index'])->name('courses');
                        $router
                            ->get('/', [MasterClassController::class, 'view'])
                            ->whereNumber('id')
                            ->name('view')
                        ;
                    }
                );

                $router->get('/', [MasterClassController::class, 'index'])->name('index');
            }
        );
        $router->group(
            [
                'prefix' => 'competitions',
                'as' => 'competitions.',
                'middleware' => 'verify.auth',
            ],
            static function (Router $router) {
                $router->get('/partners', PartnerController::class)->name('partners.index');
                $router
                    ->get('/{slug}/themes/{theme}', [CompetitionThemeController::class, 'index'])
                    ->middleware('verify.auth')
                    ->whereNumber('theme')
                    ->name('themes.index')
                ;
                $router->get('/{slug}/works', [WorkController::class, 'showByCompetition'])->name('works.index');
                $router->get('/{slug}', [CompetitionController::class, 'view'])->name('view');
                $router->get('/', [CompetitionController::class, 'index'])->name('index');
            }
        );

        $router->group(
            [
                'middleware' => ['auth:sanctum']
            ],
            static function (Router $router) {
                $router->group([
                    'prefix' => 'users',
                    'as' => 'users.',
                ], static function (Router $router) {
                    $router->group([
                        'prefix' => 'self',
                        'as' => 'self.',
                    ], static function (Router $router) {
                        $router->post('/avatar', [UserController::class, 'updateAvatar'])->name('update-avatar');
                        $router->get('/competitions', [UserController::class, 'competitions'])->name('competitions');
                        $router->get('/works', [UserController::class, 'works'])->name('works');
                        $router->get('/credits', [UserController::class, 'credits'])->name('credits');
                        $router->get('/', [UserController::class, 'view'])->name('view');
                        $router->post('/', [UserController::class, 'update'])->name('update');
                    });
                });
                $router->post('/news/{slug}/like', [NewsController::class, 'like'])->name('news.like');
                $router->post('/success-histories/{id}/like', [SuccessHistoryController::class, 'like'])->name('success-histories.like');
                $router
                    ->post('/master-classes/{id}/like', [MasterClassController::class, 'like'])
                    ->whereNumber('id')
                    ->name('master-classes.like')
                ;
                $router->post('/competitions/works/{id}/like', [WorkController::class, 'like'])->name('competitions.works.like');
                $router->post('/competitions/{slug}/works', [WorkController::class, 'create'])->name('competitions.works.create');
            }
        );
    }
);
