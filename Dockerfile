# textovka-api Dockerfile

FROM debian:buster-slim

RUN apt update && apt upgrade -yy && apt install apt-utils git vim nginx curl jq php7.3 php7.3-json php7.3-fpm -yy && apt clean
COPY . /var/www/textovka-api
RUN chown -R www-data:www-data /var/www/textovka-api
RUN sed -i 's|listen = /run/php/php7.3-fpm.sock|listen = 9000|' /etc/php/7.3/fpm/pool.d/www.conf
RUN rm -f /etc/nginx/sites-enabled/*
COPY docker/nginx-textovka-api.conf /etc/nginx/sites-enabled/textovka-api.conf
RUN ln -sf /dev/stdout /var/www/textovka-api/game.log

ENV BUILD_FROM_DOCKER 1 
EXPOSE 80
COPY docker/start.sh /start.sh
ENTRYPOINT /start.sh
