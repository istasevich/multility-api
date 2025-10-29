FROM php:8.3-fpm

# --- базовые пакеты и PHP расширения ---
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip procps net-tools curl \
 && docker-php-ext-install pdo pdo_pgsql zip bcmath \
 && rm -rf /var/lib/apt/lists/*

# --- установка Composer (глобально) ---
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

RUN pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /var/www/html/src

# --- копируем проект (если есть локальные файлы) ---
COPY . .

# --- создаем кастомный конфиг FPM ---
RUN rm -f /usr/local/etc/php-fpm.d/*.conf && \
    printf "[global]\n\
daemonize = no\n\
\n\
[www]\n\
listen = 0.0.0.0:9000\n\
clear_env = no\n\
user = www-data\n\
group = www-data\n\
pm = dynamic\n\
pm.max_children = 5\n\
pm.start_servers = 2\n\
pm.min_spare_servers = 1\n\
pm.max_spare_servers = 3\n" > /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000

CMD ["php-fpm", "-F"]
