FROM  php:8.2-apache
RUN apt-get update

RUN docker-php-ext-install pdo pdo_mysql mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite


RUN mkdir -p /var/html/www/
RUN mkdir -p /var/html/www/web

RUN chown -R www-data:www-data /var/html/www/web && chmod -R 755 /var/html/www/web


# Create a custom Apache configuration file
RUN echo "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/web\n\
    <Directory /var/www/html/web>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf



RUN apt-get install -y \
    git \
    unzip \
    libzip-dev



RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



RUN ln -sf /bin/bash /bin/sh
WORKDIR /var/www/html
