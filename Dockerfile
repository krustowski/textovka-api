# textovka-api Dockerfile

FROM debian:buster-slim
RUN apt update && apt upgrade -yy && apt install apt-utils git vim nginx curl jq php7.3 php7.3-json php7.3-fpm -yy && apt clean
RUN git clone https://github.com/krustowski/textovka-api.git /var/www/textovka-api
RUN chown -R www-data:www-data /var/www/textovka-api
RUN sed -i 's|listen = /run/php/php7.3-fpm.sock|listen = 9000|' /etc/php/7.3/fpm/pool.d/www.conf
RUN rm -f /etc/nginx/sites-enabled/*
COPY nginx-textovka-api.conf /etc/nginx/sites-enabled/textovka-api.conf
RUN ls /etc/nginx/sites-enabled/
RUN nginx -t
RUN service nginx restart
RUN service php7.3-fpm start
RUN php-fpm7.3 -y /etc/php/7.3/fpm/php-fpm.conf
ENV BUILD_FROM_DOCKER 1 
RUN /var/www/textovka-api/engine-test.sh
RUN service nginx stop
EXPOSE 80
#RUN nginx
#CMD php-fpm7.3 -c /etc/php/7.3/fpm/pool.d/www.conf
#CMD nginx -g "daemon off;"

COPY start.sh /start.sh
CMD /start.sh
