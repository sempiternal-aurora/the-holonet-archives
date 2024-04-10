FROM php:8.0-apache
WORKDIR /var/www/holonet.myria.dev

# get mysql extensions
RUN docker-php-ext-install pdo_mysql

COPY apache2_conf_files/holonet.myria.dev-le-ssl.conf /etc/apache2/sites-available/holonet.myria.dev-le-ssl.conf
COPY apache2_conf_files/holonet.myria.dev.conf /etc/apache2/sites-available/holonet.myria.dev.conf
RUN a2dissite 000-default && a2ensite holonet.myria.dev
RUN a2enmod ssl && a2ensite holonet.myria.dev-le-ssl.conf

EXPOSE 80
EXPOSE 443
