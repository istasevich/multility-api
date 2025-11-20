FROM php:8.3-fpm

# --- базовые пакеты и PHP расширения ---
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip procps net-tools curl \
    ca-certificates fonts-liberation libasound2 libatk-bridge2.0-0 libatk1.0-0 \
    libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 \
    libglib2.0-0 libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 \
    libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 \
    libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
    xdg-utils chromium wget gnupg \
 && docker-php-ext-install pdo pdo_pgsql zip bcmath \
 && rm -rf /var/lib/apt/lists/*

# --- Node.js LTS (для Browsershot) ---
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y nodejs \
 && npm install -g puppeteer-core \
 && node -v && npm -v

# --- Composer ---
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# --- PHP Redis extension ---
RUN pecl install redis && docker-php-ext-enable redis

# --- Env vars ---
ENV BROWSERSHOT_CHROMIUM_PATH=/usr/bin/chromium
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV PUPPETEER_SKIP_DOWNLOAD=true
ENV NODE_PATH=/usr/local/lib/node_modules

WORKDIR /var/www/html/src
COPY ./src /var/www/html/src

RUN composer install --no-dev --optimize-autoloader

# --- PHP-FPM конфиг ---
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
