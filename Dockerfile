FROM php:8.0-apache
WORKDIR /var/www/holonet.myria.dev

# get mysql extensions
RUN docker-php-ext-install pdo_mysql

COPY holonet.myria.dev.conf /etc/apache2/sites-available/holonet.myria.dev.conf
RUN a2dissite 000-default && a2ensite holonet.myria.dev

EXPOSE 80
