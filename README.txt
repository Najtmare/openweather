How-To:
Install and enable redis
Run composer install

Set the Open Weather API KEY in the .env file (see .env.example)

Run php artisan migrate && php artisan weather:summary, this will create the summary table and queue up 
the jobs needed to fetch the latest weather for each city in the "current.city.list.json" file.

Running "php artisan weather:fetch Skopje" will display some basic info for the requested city. This is a DB/Cache call.

You may run "php artisan test" for the unit tests. If you don't want to hit the dev database, an .env.testing.example file is included

