FROM php:7.3-cli

RUN apt-get update && apt-get install nano net-tools iputils-ping -y

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions
RUN sync
RUN install-php-extensions gd pcntl sockets zip pdo_mysql

WORKDIR /var/bodyologists

COPY . /var/bodyologists

EXPOSE 9000
RUN ln -sf "/var/bodyologists/docker/php/php.ini" "$PHP_INI_DIR/php.ini"

COPY ./docker/bash/* /usr/local/bin/

ENTRYPOINT ["entrypoint.sh"]
