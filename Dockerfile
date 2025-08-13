FROM php:8.1-fpm
# RUN apt-get update -y
# RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev
# RUN docker-php-ext-install pdo pdo_mysql bcmath
#RUN apt-get update && apt-get install -y supervisor

RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
            nginx \
            supervisor \
            unzip \
            libpq-dev \
            libcurl4-gnutls-dev \
            curl \
            libmemcached-dev \
            libz-dev \
            libzip-dev \
            zip \
            libjpeg-dev \
            libpng-dev \
            libfreetype6-dev \
            libssl-dev \
            libwebp-dev \
            libxpm-dev \
            libmcrypt-dev \
            libonig-dev; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    # Install the PHP pdo extention
    docker-php-ext-install pdo; \
    # Install the PHP pdo_mysql extention
    docker-php-ext-install pdo_mysql; \
    # Install the PHP pdo_pgsql extention
    docker-php-ext-install pdo_pgsql; \
    docker-php-ext-install opcache; \
     # Install the PHP bcmath
    docker-php-ext-install bcmath; \
    # Install the PHP gd library
    docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-xpm \
            --with-freetype; \
    docker-php-ext-install gd; \
    docker-php-ext-install sockets; \
    docker-php-ext-install zip; \
    php -r 'var_dump(gd_info());'
RUN echo 'max_execution_time = 120' >> /usr/local/etc/php/conf.d/docker-php-maxexectime.ini;
RUN echo 'memory_limit = 1024M' >> /usr/local/etc/php/conf.d/docker-php-maxexectime.ini;
RUN echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "opcache.enable=1" > /usr/local/etc/php/conf.d/docker-php-opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/docker-php-opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-opcache.ini

RUN echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_children = 100" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.start_servers = 10" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.min_spare_servers = 10" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_spare_servers = 20" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.process_idle_timeout = 60" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_requests = 0" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/www.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ng.conf /etc/nginx/nginx.conf
COPY nginx.conf /etc/nginx/sites-enabled/default
COPY --chown=www-data:www-data . /var/www/
RUN chmod -R 777 /var/www
WORKDIR /var/www
COPY . .
#RUN composer install --no-scripts --no-autoloader
#for staging
#RUN chown -R www-data:www-data /var/www
RUN chmod -R 777 /var/www
#RUN chmod -R 755 /var/www/bootstrap/cache
COPY --from=composer:2.8.1 /usr/bin/composer /usr/bin/composer


RUN chmod a+x ./Docker/entrypoint.sh

ENTRYPOINT [ "sh", "./Docker/entrypoint.sh" ]
EXPOSE 80
CMD ["php-fpm"]
