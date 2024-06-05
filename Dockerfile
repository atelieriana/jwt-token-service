FROM alpine:3.19.0
LABEL author="Kementerian Keuangan - Direktorat Jenderal Kekayaan Negara"
LABEL maintener="ibnuauliana@kemenkeu.go.id"
LABEL version="1.0.0"
LABEL description="JWT Token Service"

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV TZ='Asia/Jakarta'

WORKDIR /application

# Add supervisord alpine
ADD docker/supervisord.conf /etc/supervisord.conf

# Install general utility
RUN apk update
RUN apk add bash
RUN apk add curl
RUN apk add supervisor

# Install NGINX and setup
RUN apk add nginx
COPY docker/nginx.conf /etc/nginx
COPY --chown=nginx:nginx . /application

# Install PHP82 and Create softlink to binary PHP
RUN apk add --no-cache php82 \
    php82-common \
    php82-fpm \
    php82-opcache \
    php82-cli \
    php82-curl \
    php82-openssl \
    php82-mbstring \
    php82-tokenizer \
    php82-fileinfo \
    php82-json \
    php82-xml \
    php82-pdo_mysql \
    php82-pecl-redis \
    php82-phar \
    php82-dom \
    php82-xmlwriter \
    php82-gmp \
    php82-bcmath

# Config PHP
RUN ln -sf /usr/bin/php82 /usr/bin/php
RUN mkdir /var/run/php
RUN sed -i.bak 's@127.0.0.1:9000@/var/run/php/php82-fpm.sock@g' /etc/php82/php-fpm.d/www.conf
RUN sed -i.bak 's@nobody@nginx@g' /etc/php82/php-fpm.d/www.conf
RUN sed -i.bak 's@;listen.owner@listen.owner@g' /etc/php82/php-fpm.d/www.conf
RUN sed -i.bak 's@;listen.group@listen.group@g' /etc/php82/php-fpm.d/www.conf
RUN sed -i.bak 's@;listen.mode@listen.mode@g' /etc/php82/php-fpm.d/www.conf
# References: https://stackoverflow.com/questions/30822695/how-to-get-php-to-be-able-to-read-system-environment-variables
RUN sed -i.bak 's@;clear_env@clear_env@g' /etc/php82/php-fpm.d/www.conf
# Upgrade execution Time
RUN sed -i.bak 's@max_execution_time = 30@max_execution_time = 60@g' /etc/php82/php.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN composer install

# Creating .env file
RUN cp .env.example .env

# Generate Key
RUN php artisan key:generate

# Expose port 80
EXPOSE 80

# Entry Point
# Equaly create tail -f /var/log/nginx/access.log
ENTRYPOINT ["/usr/bin/supervisord"]
CMD ["-c","/etc/supervisord.conf"]
