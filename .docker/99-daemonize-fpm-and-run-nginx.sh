#!/bin/sh

# tiny workaround for php-fpm service + engine test
# May 7, 2022 / krusty@savla.dev

# old hack
#php-fpm8 -D && \
#	nginx -g "daemon off;"

php-fpm8 -D
