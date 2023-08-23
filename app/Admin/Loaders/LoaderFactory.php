<?php

declare(strict_types=1);

namespace App\Admin\Loaders;

use App\Admin\Loaders\Pages\AgeGroupLoader;
use App\Admin\Loaders\Pages\CityLoader;
use App\Admin\Loaders\Pages\CompetitionLoader;
use App\Admin\Loaders\Pages\CourseLoader;
use App\Admin\Loaders\Pages\LeadLoader;
use App\Admin\Loaders\Pages\MasterClassLoader;
use App\Admin\Loaders\Pages\PartnerLoader;
use App\Admin\Loaders\Pages\RegionLoader;
use App\Admin\Loaders\Pages\ThemeLoader;
use App\Admin\Loaders\Pages\UserLoader;
use App\Enums\LoaderType;

/**
 * Class LoaderFactory
 * @package App\Admin\Loaders
 */
final class LoaderFactory
{
    /**
     * @param string $loader
     *
     * @return \App\Admin\Loaders\LoaderInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function create(string $loader): LoaderInterface
    {
        $obj = match($loader) {
            LoaderType::REGION->value => RegionLoader::class,
            LoaderType::CITY->value => CityLoader::class,
            LoaderType::LEADS->value => LeadLoader::class,
            LoaderType::AGE_GROUPS->value => AgeGroupLoader::class,
            LoaderType::COURSES->value => CourseLoader::class,
            LoaderType::USERS->value => UserLoader::class,
            LoaderType::THEMES->value => ThemeLoader::class,
            LoaderType::MASTER_CLASSES->value => MasterClassLoader::class,
            LoaderType::PARTNERS->value => PartnerLoader::class,
            LoaderType::COMPETITIONS->value => CompetitionLoader::class,
        };

        return \app()->make($obj);
    }
}
