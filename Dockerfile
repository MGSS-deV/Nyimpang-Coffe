FROM php:8.2-apache

# Salin semua file project ke folder web server apache
COPY . /var/www/html/

# Ubah document root apache langsung ke folder public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -s 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -s 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Aktifkan ekstensi database mysql jika diperlukan
RUN docker-php-ext-install pdo pdo_mysql

EXPOSE 80