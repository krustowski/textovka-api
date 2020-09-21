#!/bin/bash

# deploy.sh
# script for easy prod deploy + test + rollback
# by krusty / 28. 3. 2020

#
# functions
#

function die {
    echo $1
    exit 1
}

function rollback {
    echo "engine test failed, doing a rollback..."

    ssh prod 'rm -rf ~/textovka-api && mv ~/textovka-api-bak ~/textovka-api' \
        || die "cannot perform a rollback on prod..."

    die "rollback successful."
}

#
# script start
#

cd $(dirname $0)/../ \
    || die "cannot leave current dir..."

# tar the actual version
tar -czf textovka.tar.gz textovka-api/ \
    || die "cannot tar this git repo..."

scp ~/textovka.tar.gz frank:~/ \
    || die "cannot copy this repo to prod..."

# deploy new version
ssh prod 'mv ~/textovka-api ~/textovka-api-bak && tar -xzf ~/textovka.tar.gz && chmod o+w ~/textovka-api/data ~/textovka-api/game.log && chmod o-rx ~/textovka-api/game.log ~/textovka-api/README.md ~/textovka-api/deploy.sh' \
    || die "cannot perform operations on prod..."

cd -
docker/engine-test.sh \
    || rollback

ssh prod 'rm -rf ~/textovka-api-bak' \
    || die "cannot delete backup on prod..."

echo "new version successfully deployed."
