# textovka-api Dockerfile
#
# krustowski <k@n0p.cz>

FROM alpine:latest

#
# vars / env
#

ENV PHP_VERSION 8.0
ENV APP_ROOT "/var/www/textovka-api"
ENV TZ "Europe/Prague"
ENV BUILD_FROM_DOCKER 1 

#
# essentials
#

RUN apk update && \
    apk upgrade && \
    apk add --no-cache \
    	bash \
	nginx \
	curl \
	jq \
	php8 \
	php8-json \
	php8-fpm \
	tzdata

#
# clone the repo
#

COPY . ${APP_ROOT}
RUN cd /var/www && rm -rf html localhost && \
    chmod a+w ${APP_ROOT} && \
    chown -R nginx:nginx ${APP_ROOT}

#
# reconfigure
#

RUN rm -f /etc/nginx/http.d/* && \
    ln -s ${APP_ROOT}/docker/nginx-textovka-api.conf /etc/nginx/http.d/ 
RUN mkdir /run/nginx && \
    chown nginx:nginx /run/nginx && \
    nginx -t && \
    php-fpm8 -t && \
    ln -sf /dev/stdout /var/www/textovka-api/game.log

#
# final batch
#

WORKDIR ${APP_ROOT}
EXPOSE 80
ENTRYPOINT ["docker/entrypoint.sh"]
