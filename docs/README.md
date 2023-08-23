<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
</p>

## О Larabase

Larabase является основным шаблоном для проектов, основанном на одной из последних версий Laravel с некоторыми дополнительными предустановленными пакетами, а так же настройками. 

## Особенности
- Debug режим доступен только для IP указанных в настройках переменных окружения
- Предустановлена админка(Laravel-Admin)([Официальная документация](https://laravel-admin.org/docs/en/))
- Управление файлом robots.txt через админку(хранится в БД)
- Документация на базе Swagger(l5-swagger).
- Предустановлен пакет логирования `Telescope` с настройкой для работы в development режиме
- Предустановлен пакет для работы с файлами Media-Library, сразу настроенный для загрузки файлов в S3 хранилищe([Официальгная документация](https://spatie.be/docs/laravel-medialibrary/v8/introduction))
- Добавлена поддержка пагинации через offset/limit
- Предустановлен пакет генерации аннотаций(для автокомплита в IDE) ide-helper
- ~~Уведомление об ошибках в Telegram Bot.~~

## Требования
###### #TODO


## Установка

0. Единоразово выполнить следующие команды
- `composer --global config gitlab-token.gitlab.devup.cc fNRVXf1YeQucJ4txztit`
- `composer --global config gitlab-domains gitlab.devup.cc`
- `composer --global config repositories.gitlab.devup.cc/202 '{"type": "composer", "url": "https://gitlab.devup.cc/api/v4/group/202/-/packages/composer/packages.json"}'`

1. `composer create-project dvlp/larabase`

2. Заполнить все необходимые переменные окружения в файле `.env`

3. Выполнить команду `php artisan app:install` и ввести запрашиваемые данные


