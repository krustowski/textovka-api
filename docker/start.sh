#!/bin/bash

# start.sh
# workaround for php-fpm service + engine test
# by krusty / 21. 9. 2020

service php7.3-fpm start && \
service nginx start && \
/var/www/textovka-api/docker/engine-test.sh > /dev/null && \
service nginx stop && \
nginx -g "daemon off;"
