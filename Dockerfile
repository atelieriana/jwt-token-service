FROM ibnuauliana/php82:alpine-octane-openswoole-mysql

COPY . /application

RUN composer install

RUN php artisan octane:install --server=swoole
