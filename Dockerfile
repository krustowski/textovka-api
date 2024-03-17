# textovka-api Dockerfile
#
# krustowski <krusty@savla.dev>

FROM nginx:1.24-alpine3.17


#
# args / env
#

ARG PHP_VERSION "php8"
ARG APP_ROOT "/var/www/textovka-api"
ARG TZ "Europe/Oslo"

ENV PHP_VERSION ${PHP_VERSION}
ENV APP_ROOT ${APP_ROOT}
ENV TZ ${TZ}
ENV BUILD_FROM_DOCKER 1 


#
# runntime tools
#

RUN apk update && \
    apk upgrade && \
    apk add --no-cache \
	curl \
	jq \
	${PHP_VERSION} \
	${PHP_VERSION}-json \
	${PHP_VERSION}-fpm \
	tzdata


#
# "clone" the repo, and inject nginx and php-fpm configs
#

COPY . ${APP_ROOT}
COPY .docker/99-daemonize-fpm-and-run-nginx.sh /docker-entrypoint.d/99-daemonize-fpm-and-run-nginx.sh
COPY .docker/php-fpm.d_www.conf /etc/${PHP_VERSION}/php-fpm.d/www.conf

#
# run runtime tests
#

RUN nginx -t && \
    php-fpm8 -t && \
    ln -sf /dev/stdout ${APP_ROOT}/game.log


#
# final batch
#

USER ${DOCKER_USER}
WORKDIR ${APP_ROOT}
EXPOSE ${DOCKER_EXPOSE_PORT}
#ENTRYPOINT ["/entrypoint.sh"]

