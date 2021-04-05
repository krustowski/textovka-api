#!/bin/bash

# start.sh
# workaround for php-fpm service + engine test
# by krusty / 21. 9. 2020

php-fpm8 -D && \
	nginx -g "daemon off;"
