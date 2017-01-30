FROM php:5-cli
MAINTAINER Dimitris Zervas <dzervas@dzervas.gr>

RUN apt-get update && apt-get install -y libncursesw5-dev && \
	pecl install ncurses && docker-php-ext-enable ncurses

WORKDIR /usr/src/myapp
COPY mame.php /usr/src/myapp/
COPY info.xml /usr/src/myapp/

CMD [ "php", "mame.php" ]
