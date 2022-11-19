#!/bin/sh

# engine-test.sh
# simple demo map batch engine test
# by krusty / 20. 9. 2020

#trap die SIGKILL


# solution to maps/demo.json
actions=(
    "go-south"
    "pick-bucket"
    "go-north"
    "go-north"
    "pick-water"
    "go-south"
    "go-east"
    "quench-fire"
    "go-east"
    "pick-key"
    "go-west"
    "go-west"
    "go-west"
    "unlock-door"
    "go-west"
)


#
# vars
#

i=0         # iterator in actions loop
apikey=""   # apikey for playing
endpoint="http://localhost:${DOCKER_EXPOSE_PORT}/"
repodir=$(dirname $0)/..


#
# functions
#

function die {
    echo $1
    #rm -rf $repodir/.tmp
    exit 1
}

function tools_check {
    which curl  &> /dev/null || die "curl tool required..."
    which jq    &> /dev/null || die "jq tool required..."
}

function api_init {
    # connection test
    curl -sL "${endpoint}" &> /dev/null || die "connection error - endpoint cannot be reached..."

    # register call
    local unistring=$(date +%s | shasum -a 256 | cut -d' ' -f1)
    local apikey=$(curl -sL "${endpoint}?register=${unistring}" | jq -r '.api.apikey')

    [[ -n $apikey ]] || die "no apikey received from server..."

    echo $apikey
}

function api_call {
    curl -sL "${endpoint}?apikey=${apikey}&action=${1}"
}

#
# script start
#


# init
[[ -z ${MAKEFILE_CALL} ]] && die "This has to be run from Makefile ..."

cd $repodir
[[ -d ${repodir}/.tmp/ ]] || mkdir -p ${repodir}/.tmp/
tools_check
apikey=$(api_init)


# loop through actions set
for action in ${actions[@]}; do
    i=$((i+1))

    # executed from docker build
    if [ ! -z ${BUILD_FROM_DOCKER+x} ]; then
        api_call $action;
    else 
        api_call $action > $repodir/.tmp/$i;
    fi;
done


# final check if game ended
api_call "random-cmd" | jq '.player.game_ended' | grep -wq true \
    && echo "test successful." \
    || die "game not ended, check $repodir/.tmp for curl logs..."


# clean test outputs if succ'd
rm -rf $repodir/.tmp

