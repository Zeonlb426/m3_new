<?php
// !!! File was generated automatically !!!
// - use `artisan admin:export-menu-config` to refresh it from the database.
// - use `artisan admin:import-menu-config` to load it into database.
return [
    [
        'order' => 10,
        'title' => 'Dashboard',
        'icon' => 'fa-bar-chart',
        'uri' => '/',
    ],
    [
        'order' => 20,
        'title' => 'Города и регионы',
        'icon' => 'fa-building',
        'children' => [
            [
                'order' => 3,
                'title' => 'Регионы',
                'uri' => 'regions',
            ],
            [
                'order' => 4,
                'title' => 'Города',
                'uri' => 'cities',
            ],
        ],
    ],
    [
        'order' => 30,
        'title' => 'Пользователи',
        'icon' => 'fa-users',
        'uri' => 'users',
    ],
    [
        'order' => 40,
        'title' => 'Новости',
        'icon' => 'fa-newspaper-o',
        'uri' => 'news',
    ],
    [
        'order' => 50,
        'title' => 'Истории успеха',
        'icon' => 'fa-hand-peace-o ',
        'uri' => 'success-histories',
    ],
    [
        'order' => 60,
        'title' => 'Группы и ведущие',
        'icon' => 'fa-child',
        'children' => [
            [
                'order' => 1,
                'title' => 'Возрастные группы',
                'uri' => 'age-groups',
            ],
            [
                'order' => 2,
                'title' => 'Ведущие',
                'uri' => 'leads',
            ],
        ],
    ],
    [
        'order' => 70,
        'title' => 'Мастер-классы',
        'icon' => 'fa-gift',
        'children' => [
            [
                'order' => 1,
                'title' => 'Курсы',
                'uri' => 'courses',
            ],
            [
                'order' => 2,
                'title' => 'Мастер-классы',
                'uri' => 'master-classes',
            ],
        ],
    ],
    [
        'order' => 80,
        'title' => 'Конкурсы',
        'icon' => 'fa-bolt',
        'children' => [
            [
                'order' => 0,
                'title' => 'Типы работ',
                'uri' => 'work-types',
            ],
            [
                'order' => 1,
                'title' => 'Партнёры',
                'uri' => 'partners',
            ],
            [
                'order' => 2,
                'title' => 'Темы',
                'uri' => 'themes',
            ],
            [
                'order' => 3,
                'title' => 'Конкурсы',
                'uri' => 'competitions',
            ],
            [
                'order' => 4,
                'title' => 'Работы пользователей',
                'uri' => 'works',
            ],
        ],
    ],
    [
        'order' => 85,
        'title' => 'Служебные',
        'icon' => 'fa-gear',
        'children' => [
            [
                'order' => 1,
                'title' => 'Лайки',
                'uri' => 'likes',
            ],
            [
                'order' => 5,
                'title' => 'Начисления баллов',
                'uri' => 'credits',
            ],
            [
                'order' => 10,
                'title' => 'Заявки обратной связи',
                'uri' => 'feedbacks',
            ],
            [
                'order' => 20,
                'title' => 'Документы',
                'uri' => 'documents',
            ],
        ],
    ],
    [
        'order' => 90,
        'title' => 'Настройки',
        'icon' => 'fa-gears',
        'children' => [
            [
                'order' => 1,
                'title' => 'Слайды',
                'uri' => 'sliders',
            ],
            [
                'order' => 5,
                'title' => 'Настройки',
                'uri' => 'settings',
            ],
        ],
    ],
    [
        'order' => 99999,
        'title' => 'Admin',
        'icon' => 'fa-tasks',
        'roles' => [
            0 => 'administrator',
        ],
        'children' => [
            [
                'order' => 3,
                'title' => 'Users',
                'icon' => 'fa-users',
                'uri' => 'auth/users',
            ],
            [
                'order' => 4,
                'title' => 'Roles',
                'icon' => 'fa-user',
                'uri' => 'auth/roles',
            ],
            [
                'order' => 5,
                'title' => 'Permission',
                'icon' => 'fa-ban',
                'uri' => 'auth/permissions',
            ],
            [
                'order' => 6,
                'title' => 'Menu',
                'icon' => 'fa-bars',
                'uri' => 'auth/menu',
            ],
            [
                'order' => 7,
                'title' => 'Operation log',
                'icon' => 'fa-history',
                'uri' => 'auth/logs',
            ],
            [
                'title' => 'Schedule Monitor',
                'icon' => 'fa-tasks',
                'uri' => 'schedule-monitor',
                'permission' => '*',
                'roles' => [
                    0 => 'administrator',
                ],
            ],
        ],
    ],
];
