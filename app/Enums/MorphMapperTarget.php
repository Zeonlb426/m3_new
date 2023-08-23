<?php

declare(strict_types = 1);

namespace App\Enums;

use App\Enums\Traits\EnumToArray;

enum MorphMapperTarget: string
{
    use EnumToArray;

    case USER = 'user';
    case USER_ACTIVITY = 'user-activity';
    case USER_SOCIAL = 'user-social';
    case USER_CREDIT = 'user-credit';
    case AGE_GROUP = 'age-group';
    case LEAD = 'lead';
    case PARTNER = 'partner';
    case CITY = 'city';
    case REGION = 'region';
    case COURSE = 'course';
    case MASTER_CLASS = 'master-class';
    case NEWS = 'news';
    case SUCCESS_HISTORY = 'success-history';
    case SLIDER = 'slider';
    case SHARING = 'sharing';
    case DOCUMENT = 'document';
    case COMPETITION = 'competition';
    case PRIZE = 'prize';
    case THEME = 'theme';
    case WORK = 'work';
    case WORK_AUTHOR = 'work-author';

    public function label(): string
    {
        return match ($this) {
            self::USER => \__('enum.models.morph_mapper.user'),
            self::USER_ACTIVITY => \__('enum.models.morph_mapper.user_activity'),
            self::USER_SOCIAL => \__('enum.models.morph_mapper.user_social'),
            self::USER_CREDIT => \__('enum.models.morph_mapper.user_credit'),
            self::AGE_GROUP => \__('enum.models.morph_mapper.age_group'),
            self::LEAD => \__('enum.models.morph_mapper.lead'),
            self::PARTNER => \__('enum.models.morph_mapper.partner'),
            self::CITY => \__('enum.models.morph_mapper.city'),
            self::REGION => \__('enum.models.morph_mapper.region'),
            self::COURSE => \__('enum.models.morph_mapper.course'),
            self::MASTER_CLASS => \__('enum.models.morph_mapper.master_class'),
            self::NEWS => \__('enum.models.morph_mapper.news'),
            self::SUCCESS_HISTORY => \__('enum.models.morph_mapper.success_history'),
            self::SLIDER => \__('enum.models.morph_mapper.slider'),
            self::SHARING => \__('enum.models.morph_mapper.sharing'),
            self::DOCUMENT => \__('enum.models.morph_mapper.document'),
            self::COMPETITION => \__('enum.models.morph_mapper.competition'),
            self::PRIZE => \__('enum.models.morph_mapper.prize'),
            self::THEME => \__('enum.models.morph_mapper.theme'),
            self::WORK => \__('enum.models.morph_mapper.work'),
            self::WORK_AUTHOR => \__('enum.models.morph_mapper.work_author'),
        };
    }

    public static function likesAvailablePairs(): array
    {
        return [
            self::NEWS->value => self::NEWS->label(),
            self::MASTER_CLASS->value => self::MASTER_CLASS->label(),
            self::SUCCESS_HISTORY->value => self::SUCCESS_HISTORY->label(),
            self::WORK->value => self::WORK->label(),
        ];
    }
}
