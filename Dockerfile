FROM php:7.2-apache

RUN apt update && apt -y upgrade && apt -y install libicu-dev && \
	docker-php-ext-install -j5 pdo_mysql intl && \
	mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
	sed -i 's#DocumentRoot \+/var/www/html$#DocumentRoot /var/www/html/public#' /etc/apache2/sites-enabled/000-default.conf

VOLUME /var/www/html
EXPOSE 80
