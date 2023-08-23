##Настройка почтового клиента

> **WARNING**: по рассылке писем необходимо соблюдать несколько правил:
> 1. если получателей несколько, добавляем их в BCC!
> 2. нельзя тестировать рассылку писем на корпоративные email. Используем толь тестовые

###Yandex
```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=smtp.yandex.ru
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=$USERNAME
MAIL_PASSWORD=$PASSWORD
MAIL_FROM_ADDRESS=$USERNAME #адрес отправителя должен соответствовать логину
MAIL_FROM_NAME=$FROM_NAME
```
###Gmail
```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=smtp.googlemail.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=$USERNAME
MAIL_PASSWORD=$PASSWORD
MAIL_FROM_ADDRESS=$USERNAME #адрес отправителя должен соответствовать логину
MAIL_FROM_NAME=$FROM_NAME
```
###Send Grid
```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=apikey
MAIL_PASSWORD=$PASSWORD
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME=$FROM_NAME
```
