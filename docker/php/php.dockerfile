FROM php:8.1.9-fpm

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

RUN apt update

RUN apt install -y \
        git \
        libzip-dev\
        libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
        libicu-dev \
        libpq-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j$(nproc) gd intl zip pdo_pgsql pgsql

COPY php.ini-development "$PHP_INI_DIR/php.ini"
