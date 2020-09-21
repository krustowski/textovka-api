#!/bin/bash

# start.sh
# workaround for php-fpm service
# by krusty / 21. 9. 2020

service php7.3-fpm start && \
nginx -g "daemon off;"
