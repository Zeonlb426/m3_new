<?php

declare(strict_types=1);

namespace App\Providers;

use App\Components\MediaLibrary\UniqidFileNamer;
use App\Enums\App\EnvEnum;
use App\Enums\MorphMapperTarget;
use App\Hashing\Yii2Hashes;
use App\Utils\DebugUtils;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use L5Swagger\L5SwaggerServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        #debug mode may be enabled only for development mode and specific IP's
        if ($this->app->environment('development')) {
            if ($this->app->runningInConsole() || DebugUtils::isDebugIp(\request())) {
                \config(['app.debug' => true]);
            }
        }

        #telescope may be enabled only for local and development mode with specific IP's
        if ($this->app->environment('local')
            || DebugUtils::telescopeEnabledByAdditionalEnvironments(\request())
        ) {
            $this->app->register(TelescopeServiceProvider::class);
            $this->app->register(TelescopeApplicationServiceProvider::class);
        }

        if ($this->app->environment(['development', 'local'])) {
            $this->app->register(L5SwaggerServiceProvider::class);
        }

        if ($this->app->environment(['local'])) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        $this->app->resolving(FileAdder::class, static function (FileAdder $fileAdder, Container $container) {
            $uniqIdNamer = $container->get(UniqidFileNamer::class);
            $sanitizer = (function (string $originalFileName) use ($uniqIdNamer): string {
                /** @var \Spatie\MediaLibrary\MediaCollections\FileAdder $this */
                return $uniqIdNamer->name($originalFileName);
            })(...);

            $fileAdder->sanitizingFileName($sanitizer->bindTo($fileAdder));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (false === \Illuminate\Support\Str::hasMacro('finishWith')) {
            \Illuminate\Support\Str::macro('finishWith', static function (string $str, string $with) {
                return \Illuminate\Support\Str::endsWith($str, $with) ? $str : ($str . $with);
            });
        }

        if (false === \Illuminate\Support\Str::hasMacro('isEmpty')) {
            \Illuminate\Support\Str::macro('isEmpty', static function (?string $str) {
                return null === $str || '' === $str;
            });
        }

        if (false === \Illuminate\Support\Str::hasMacro('isNotEmpty')) {
            \Illuminate\Support\Str::macro('isNotEmpty', static function (?string $str) {
                return null !== $str && '' !== $str;
            });
        }

        \Hash::extend('yii2', static function () {
            return new Yii2Hashes();
        });

        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            return \sprintf('%s?email=%s&token=%s', \url('/'), $notifiable->email, $token);
        });

        Relation::enforceMorphMap([
            MorphMapperTarget::USER->value => \App\Models\User::class,
            MorphMapperTarget::USER_ACTIVITY->value => \App\Models\User\UserActivity::class,
            MorphMapperTarget::USER_SOCIAL->value => \App\Models\User\UserSocial::class,
            MorphMapperTarget::USER_CREDIT->value => \App\Models\User\UserTotalCredit::class,
            MorphMapperTarget::AGE_GROUP->value => \App\Models\AgeGroup::class,
            MorphMapperTarget::LEAD->value => \App\Models\Lead::class,
            MorphMapperTarget::PARTNER->value => \App\Models\Competition\Partner::class,
            MorphMapperTarget::CITY->value => \App\Models\Location\City::class,
            MorphMapperTarget::REGION->value => \App\Models\Location\Region::class,
            MorphMapperTarget::COURSE->value => \App\Models\MasterClass\Course::class,
            MorphMapperTarget::MASTER_CLASS->value => \App\Models\MasterClass\MasterClass::class,
            MorphMapperTarget::NEWS->value => \App\Models\News\News::class,
            MorphMapperTarget::SUCCESS_HISTORY->value => \App\Models\Promo\SuccessHistory::class,
            MorphMapperTarget::SLIDER->value => \App\Models\Slider::class,
            MorphMapperTarget::SHARING->value => \App\Models\Sharing::class,
            MorphMapperTarget::DOCUMENT->value => \App\Models\Misc\Document::class,
            MorphMapperTarget::COMPETITION->value => \App\Models\Competition\Competition::class,
            MorphMapperTarget::PRIZE->value => \App\Models\Competition\Prize::class,
            MorphMapperTarget::THEME->value => \App\Models\Competition\Theme::class,
            MorphMapperTarget::WORK->value => \App\Models\CompetitionWork\Work::class,
            MorphMapperTarget::WORK_AUTHOR->value => \App\Models\CompetitionWork\WorkAuthor::class,
        ]);
    }
}
