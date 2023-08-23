<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumToArray;
use OpenApi\Attributes as OA;

/**
 * Class SearchCategory
 * @package App\Enums
 */
#[OA\Schema(
    schema: 'SearchCategoryField',
    description: <<<STR
Поиск :
<li>news - По новостям</li>
<li>works - По работам пользователей</li>
<li>competitions - По конкурсам</li>
<li>master_classes - По мастер-классам</li>
STR,
    type: 'string',
    enum: [
        self::NEWS,
        self::WORKS,
        self::COMPETITIONS,
        self::MASTER_CLASSES,
    ]
)]
enum SearchCategory: string
{
    use EnumToArray;

    case NEWS = 'news';
    case WORKS = 'works';
    case COMPETITIONS = 'competitions';
    case MASTER_CLASSES = 'master-classes';

    public function label(): string
    {
        return match ($this) {
            self::NEWS => \__('enum.search_category.news'),
            self::WORKS => \__('enum.search_category.works'),
            self::COMPETITIONS => \__('enum.search_category.competitions'),
            self::MASTER_CLASSES => \__('enum.search_category.master_classes'),
        };
    }
}
