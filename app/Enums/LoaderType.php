<?php

declare(strict_types=1);

namespace App\Enums;

enum LoaderType: string
{
    case REGION = 'region';
    case CITY = 'city';
    case LEADS = 'leads';
    case AGE_GROUPS = 'age_groups';
    case COURSES = 'courses';
    case USERS = 'users';
    case THEMES = 'themes';
    case MASTER_CLASSES = 'master_classes';
    case PARTNERS = 'partner';
    case COMPETITIONS = 'competitions';
}
