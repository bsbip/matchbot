FROM php:7.2-fpm

RUN mkdir -p /var/www/matchbot/api

COPY composer.lock composer.json /var/www/matchbot/api/

WORKDIR /var/www/matchbot/api

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    cron \
    supervisor

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
RUN docker-php-ext-install gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/matchbot/api

RUN chown -R www-data:www-data /var/www/matchbot/api
RUN chmod -R 755 /var/www/matchbot/api

COPY ./crontab/laravel /etc/cron.d/laravel
RUN chmod 0644 /etc/cron.d/laravel
RUN crontab /etc/cron.d/laravel

COPY ./supervisor /etc/supervisor/conf.d

EXPOSE 9000

CMD bash -c "service supervisor start && cron && php-fpm"
