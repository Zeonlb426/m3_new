<?php

use App\Admin\Controllers\AgeGroupController;
use App\Admin\Controllers\Competition\CompetitionController;
use App\Admin\Controllers\Competition\PartnerController;
use App\Admin\Controllers\Competition\ThemeController;
use App\Admin\Controllers\Competition\WorkTypeController;
use App\Admin\Controllers\CompetitionWork\WorkController;
use App\Admin\Controllers\Misc\DocumentController;
use App\Admin\Controllers\Misc\FeedbackController;
use App\Admin\Controllers\LeadController;
use App\Admin\Controllers\Location\CityController;
use App\Admin\Controllers\Location\RegionController;
use App\Admin\Controllers\MasterClass\CourseController;
use App\Admin\Controllers\MasterClass\MasterClassController;
use App\Admin\Controllers\Misc\LikeController;
use App\Admin\Controllers\Misc\CreditController;
use App\Admin\Controllers\News\NewsController;
use App\Admin\Controllers\Promo\SuccessHistoryController;
use App\Admin\Controllers\SettingsController;
use App\Admin\Controllers\SliderController;
use App\Admin\Controllers\User\UserController;
use Illuminate\Routing\Router;

Admin::routes();

$defaultGroupAttributes = [
    'prefix' => \config('admin.route.prefix'),
    'middleware' => \config('admin.route.middleware'),
    'as' => \config('admin.route.prefix') . '.',
];

Route::group(\array_merge($defaultGroupAttributes, [
    'namespace' => \config('admin.route.namespace'),
]), function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->group(
        [
            'prefix' => 'api',
        ],
        static function(Router $router) {
            $router
                ->get('loader/{type}', \App\Admin\Controllers\Api\DropDownListLoaderController::class)
                ->name(\App\Admin\Controllers\Api\DropDownListLoaderController::ROUTE_NAME)
            ;
        }
    );

    $router->resource('regions', RegionController::class)->only(['index', 'edit', 'update']);
    $router->resource('cities', CityController::class)->only(['index', 'edit', 'update']);
    $router->resource('users', UserController::class)->except(['create']);
    $router->resource('news', NewsController::class);
    $router->resource('success-histories', SuccessHistoryController::class);
    $router->resource('leads', LeadController::class);
    $router->resource('courses', CourseController::class);
    $router->resource('age-groups', AgeGroupController::class)->except(['view']);
    $router->resource('master-classes', MasterClassController::class);
    $router->resource('partners', PartnerController::class);
    $router->resource('competitions', CompetitionController::class);
    $router->resource('themes', ThemeController::class);
    $router->resource('sliders', SliderController::class);
    $router->resource('feedbacks', FeedbackController::class)->except(['create']);
    $router->resource('documents', DocumentController::class);
    $router->resource('likes', LikeController::class)->only(['index']);
    $router->resource('credits', CreditController::class)->only(['index']);
    $router->post('settings/{setting}', [SettingsController::class, 'update'])->name('settings.update');
    $router->get('settings', [SettingsController::class, 'index'])->name('settings.index');
    $router->resource('works', WorkController::class)->only(['index', 'update', 'delete']);
    $router->resource('work-types', WorkTypeController::class)->only(['index', 'update']);
});
