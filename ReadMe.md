# Exchange Rates service

Cервис на symfony4, который раз в день получает курсы валют, заданных в конфиге (задаются валютные пары), и сохраняет в бд средний курс  для каждой валюты. При недоступности одного из сервиса попытка повторяется до 10 раз. Это значение так же задаётся в конфиге. 
Сервис предоставляет api для получения курса переданной  пары валют на переданную дату. Значения берутся из бд

Курсы валют берутся отсюда:

https://www.cbr.ru/development/SXML/

https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=USD&currency_to=RUR&source=cbrf&sum=1&date=

### С чего начать
```angular2html
$ git clone https://github.com/dryyyyy/ExhangeRatesApp.git
```

### Пример использования
Сперва необходимо получить значения и положить их в БД командой ниже:
```angular2html
php bin/console app:store_rates
```
*Чтобы ежедневно получать значения валют, можно запускать команду через cron или другой планировщик задач*

По умолчанию, значения валютных пар берутся из конфига.
```angular2html
# config/services.yaml

parameters:
    locale: 'en'

    app.currency_from: 'EUR'
    app.currency_to: 'RUR'
```
Значения валютных пар можно задать через команду.
```angular2html
php bin/console app:store_rates USD RUR
```
Для получения значения за какое-то число, нужно воспользоваться api, например:
```angular2html
localhost/api/01-01-2001
``` 
