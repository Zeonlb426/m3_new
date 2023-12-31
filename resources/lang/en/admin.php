<?php

use App\Settings\CountersSettings;
use App\Settings\MainTextsSettings;
use App\Settings\PointsExchangeSettings;

return [
    'online'                => 'Online',
    'login'                 => 'Login',
    'logout'                => 'Logout',
    'setting'               => 'Setting',
    'name'                  => 'Name',
    'username'              => 'Username',
    'password'              => 'Password',
    'password_confirmation' => 'Password confirmation',
    'remember_me'           => 'Remember me',
    'user_setting'          => 'User setting',
    'avatar'                => 'Avatar',
    'list'                  => 'List',
    'new'                   => 'New',
    'create'                => 'Create',
    'delete'                => 'Delete',
    'remove'                => 'Remove',
    'edit'                  => 'Edit',
    'view'                  => 'View',
    'continue_editing'      => 'Continue editing',
    'continue_creating'     => 'Continue creating',
    'detail'                => 'Detail',
    'browse'                => 'Browse',
    'reset'                 => 'Reset',
    'export'                => 'Export',
    'batch_delete'          => 'Batch delete',
    'save'                  => 'Save',
    'refresh'               => 'Refresh',
    'order'                 => 'Order',
    'expand'                => 'Expand',
    'collapse'              => 'Collapse',
    'filter'                => 'Filter',
    'search'                => 'Search',
    'close'                 => 'Close',
    'show'                  => 'Show',
    'entries'               => 'entries',
    'captcha'               => 'Captcha',
    'action'                => 'Action',
    'title'                 => 'Title',
    'description'           => 'Description',
    'back'                  => 'Back',
    'back_to_list'          => 'Back to List',
    'submit'                => 'Submit',
    'menu'                  => 'Menu',
    'input'                 => 'Input',
    'succeeded'             => 'Succeeded',
    'failed'                => 'Failed',
    'delete_confirm'        => 'Are you sure to delete this item ?',
    'delete_succeeded'      => 'Delete succeeded !',
    'delete_failed'         => 'Delete failed !',
    'update_succeeded'      => 'Update succeeded !',
    'save_succeeded'        => 'Save succeeded !',
    'refresh_succeeded'     => 'Refresh succeeded !',
    'login_successful'      => 'Login successful',
    'choose'                => 'Choose',
    'choose_file'           => 'Select file',
    'choose_image'          => 'Select image',
    'more'                  => 'More',
    'deny'                  => 'Permission denied',
    'administrator'         => 'Administrator',
    'roles'                 => 'Roles',
    'permissions'           => 'Permissions',
    'slug'                  => 'Slug',
    'created_at'            => 'Created At',
    'updated_at'            => 'Updated At',
    'alert'                 => 'Alert',
    'parent_id'             => 'Parent',
    'icon'                  => 'Icon',
    'uri'                   => 'URI',
    'operation_log'         => 'Operation log',
    'parent_select_error'   => 'Parent select error',
    'pagination'            => [
        'range' => 'Showing :first to :last of :total entries',
    ],
    'role'                  => 'Role',
    'permission'            => 'Permission',
    'route'                 => 'Route',
    'confirm'               => 'Confirm',
    'cancel'                => 'Cancel',
    'http'                  => [
        'method' => 'HTTP method',
        'path'   => 'HTTP path',
    ],
    'all_methods_if_empty'  => 'All methods if empty',
    'all'                   => 'All',
    'current_page'          => 'Current page',
    'selected_rows'         => 'Selected rows',
    'upload'                => 'Upload',
    'new_folder'            => 'New folder',
    'time'                  => 'Time',
    'size'                  => 'Size',
    'listbox'               => [
        'text_total'         => 'Showing all {0}',
        'text_empty'         => 'Empty list',
        'filtered'           => '{0} / {1}',
        'filter_clear'       => 'Show all',
        'filter_placeholder' => 'Filter',
    ],
    'grid_items_selected'    => '{n} items selected',

    'menu_titles'            => [],
    'prev'                   => 'Prev',
    'next'                   => 'Next',
    'quick_create'           => 'Quick create',

    'switch_visible_statuses' => [
        'on' => ['value' => true, 'text' => 'On', 'color' => 'success'],
        'off' => ['value' => false, 'text' => 'Off', 'color' => 'default'],
    ],
    'switch_grid_visible_statuses' => [
        'on' => ['value' => 1, 'text' => '✓', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => '✖', 'color' => 'danger'],
    ],
    'switch_block_visible_statuses' => [
        'on' => ['value' => 1, 'text' => 'Block visible', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => 'Block hidden', 'color' => 'default'],
    ],

    'messages' => [
        'empty_value' => 'Empty',
        'tile_images' => 'Images for "Tiles" list',
        'show_images' => 'Header image for single view',
    ],
    'titles' => [
        'cities' => 'Cities',
        'regions' => 'Reions',
        'news' => 'News',
        'users' => 'Users',
        'success-histories' => 'Histories of success',
        'leads' => 'Leads',
        'courses' => 'Courses',
        'age-groups' => 'Age groups',
        'master-classes' => 'Master classes',
        'partners' => 'Partners',
        'sliders' => 'Slides',
        'feedbacks' => 'Feedbacks',
        'credits' => 'Credits',
        'settings' => [
            CountersSettings::group() => 'Add to counters',
            MainTextsSettings::group() => 'General texts',
            PointsExchangeSettings::group() => 'Points exchange',
        ],
        'competitions' => 'Competitions',
        'themes' => 'Themes (categories)',
        'works' => 'Competition users` works',
        'work-types' => 'Work content types',
    ],
    'tabs' => [
        'main-info' => 'General',
        'sharing-info' => 'Sharing',
        'display-settings-info' => 'Display settings',
        'blocks-info' => 'Optional blocks',
    ],
    'models' => [
        'id' => 'ID',
        'slug' => 'Slug',
        'order_column' => 'Order position',
        'visible_status' => 'Status',
        'is_main' => 'Main in section',
        'city' => [
            'region' => 'Region',
            'title' => 'City',
        ],
        'news' => [
            'additional_signs' => 'News scope',
            'announce' => 'Announce',
            'content' => 'Content',
            'cover' => 'Cover',
            'publish_date' => 'Publish date',
            'title' => 'Title',
            'video_link' => 'Video link',
            'likes' => 'Likes count',
        ],
        'region' => [
            'cities' => 'Cities',
            'code' => 'Region code',
            'title' => 'Title',
        ],
        'sharing' => [
            'description' => 'Sharing description',
            'image' => 'Sharing image',
            'title' => 'Sharing title',
        ],
        'user' => [
            'avatar' => 'Avatar',
            'birth_date' => 'Birth date',
            'city' => 'City',
            'email' => 'Email',
            'first_name' => 'First name',
            'full_name' => 'Name',
            'last_name' => 'Last name',
            'phone' => 'Phone number',
            'region' => 'Region',
        ],
        'success_history' => [
            'description' => 'Description',
            'short_description' => 'Short description',
            'short_title' => 'Subtitle',
            'title' => 'Title',
            'image' => 'Image',
            'video_link' => 'Video link',
            'likes' => 'Likes count',
        ],
        'lead' => [
            'name' => 'Name',
            'short_description' => 'Short description',
            'description' => 'Description',
            'photo' => 'Photo',
        ],
        'course' => [
            'description' => 'Description',
            'leads' => 'Leads',
            'name' => 'Name',
        ],
        'age_group' => [
            'title' => 'Title',
            'min_age' => 'Min age',
            'max_age' => 'Max age',
        ],
        'master_class' => [
            'title' => 'Title',
            'video_link' => 'Video link',
            'age_group' => 'Age group',
            'lead' => 'Lead',
            'additional_signs' => 'Additional markups',
            'content' => 'Content',
            'image' => 'Image',
            'courses' => 'Courses',
            'likes' => 'Likes',
        ],
        'partner' => [
            'link' => 'External link',
            'logo' => 'Logo',
            'background' => 'Background',
            'slider' => 'Slider image',
            'title' => 'Title',
            'description' => 'Description',
        ],
        'slider' => [
            'short_title' => 'Subtitle',
            'title' => 'Title',
            'link' => 'External link',
            'description' => 'Description',
            'image' => 'Image',
            'image_mobile' => 'Mobile image',
        ],
        'feedback' => [
            'name' => 'Full name',
            'email' => 'Email',
            'content' => 'Content',
            'processing_status' => 'Processing status',
            'user' => 'User',
        ],
        'document' => [
            'name' => 'Name',
            'file_name' => 'File name',
            'file' => 'Document',
        ],
        'user_activity' => [
            'user' => 'User',
            'interacted_type' => 'Section',
            'interacted_id' => 'Record ID',
        ],
        'user_total_credit' => [
            'user' => 'User',
            'count_register' => 'Registration credits',
            'count_likes' => 'Likes credits',
            'count_works' => 'Works credits',
            'count_total' => 'Total credits',
            'likes_count' => 'Likes count',
            'works_count' => 'Works count',
        ],
        'settings' => [
            CountersSettings::group() => [
                'fake_credits' => 'Add to credits',
                'fake_likes' => 'Add to likes',
            ],
            MainTextsSettings::group() => [
                'meta_title' => 'Meta title',
                'meta_description' => 'Meta description',
                'meta_keywords' => 'Meta keywords',
                'sharing_title' => 'Sharing title',
                'sharing_description' => 'Sharing description',
                'sharing_image' => 'Sharing image',
            ],
            PointsExchangeSettings::group() => [
                'exchange_rate' => 'Points to credits rate exchange',
                'points_registration' => 'Registration points',
                'points_like' => 'Likes points',
                'points_work_add' => 'Add work points',
            ],
        ],
        'prizes' => [
            'title' => 'Name',
            'description' => 'Description',
            'link' => 'Link',
            'win_position' => 'Win place',
            'image' => 'Image',
        ],
        'competition' => [
            'title' => 'Title',
            'period' => 'Text with period end',
            'content' => 'Competition content',
            'short_content' => 'Competition short content',
            'titles_content' => 'Additional titles',
            'tile_size' => 'Tile size',
            'cover' => 'Cover in header',
            'tile' => 'Tile in list',

            'pivot' => [
                'like_text' => 'Like success text',
                'add_work_enabled' => 'Show "Add Work"',
                'add_work_text' => 'Text "Add work" button',
                'works_enabled' => 'Show competition works entries',
                'works_filtration_enabled' => 'Show competition works filtering',
            ],

            'work_types' => [
                'title' => 'Work types',
                'formats' => 'File formats',
            ],
            'age_groups' => [
                'title' => 'Age groups',
                'pivot' => [
                    'visible_status' => 'Show in list',
                ],
            ],
            'themes' => [
                'title' => 'Themes',
                'pivot' => [
                    'titles_content' => [
                        'section_name' => 'Title of the "Themes" section',
                        'default' => 'If left blank, there will be no title.<br>If no themes is added, the section will not be displayed.',
                    ],
                ],
            ],
            'leads' => [
                'title' => 'Leads',
                'pivot' => [
                    'titles_content' => [
                        'section_name' => 'Section heading "Leads"',
                        'default' => 'If left blank, there will be no title.<br>If no lead is added, the section will not be displayed.',
                    ],
                ],
            ],
            'prizes' => [
                'title' => 'Prizes',
            ],
            'prizes_info' => [
                'title' => 'Prize with additional info',
                'default' => 'If left blank all fields, this prize will not be displayed',
                'pivot' => [
                    'titles_content' => [
                        'gift' => 'Text "Gift"',
                        'gift_default' => 'If left blank, "Gift" will not be displayed',
                        'like' => 'Text like',
                        'like_default' => 'If left blank, "Like" will not be displayed',
                    ],
                ],
            ],
            'master_classes' => [
                'title' => 'Master classes',
                'pivot' => [
                    'titles_content' => [
                        'section_name' => 'Header of the "Class Master" section',
                        'default' => 'If left blank, there will be no title.<br>If no master class is added, the section will not be displayed.',
                    ],
                    'is_main' => 'Main (displayed separately)',
                ],
            ],
            'partners' => [
                'title' => 'Partners',
                'pivot' => [
                    'titles_content' => [
                        'section_name' => 'Partners section title',
                        'partner_text' => 'Partner description text',
                        'default' => 'If left blank, there will be no title.<br>Main partner is required',
                    ],
                    'is_main' => 'Main',
                ],
            ],
        ],

        'theme' => [
            'title' => 'Theme title',
            'description' => 'Description',
            'tile' => 'Tile',
            'tile_size' => 'Tile size',
            'cover' => 'Cover',
            'leads' => 'Leads',
        ],

        'work_author' => [
            'name' => 'Work author name',
            'age' => 'Work author age',
            'birth_date' => 'Birth date',
        ],

        'work' => [
            'user' => 'User',
            'competition' => 'Competition',
            'theme' => 'Theme',
            'work_type' => 'Work type',
            'content' => 'Work content',
            'audio' => [
                'title' => 'Audio attachment',
                'content' => 'Audiofile',
            ],
            'video' => [
                'title' => 'Video attachment',
                'content' => 'Video link',
            ],
            'text' => [
                'title' => 'Text attachment',
                'content' => 'Content',
            ],
            'image' => [
                'title' => 'Image attachment',
                'content' => 'Image',
            ],
            'images' => [
                'content' => 'Multiple images',
            ],
            'video_text' => [
                'content' => 'Video + text',
            ],
            'image_text' => [
                'content' => 'Image + text',
            ],
            'likes' => 'Likes count',
        ],
    ],
];
