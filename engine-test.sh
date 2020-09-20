#!/bin/bash

# engine-test.sh
# simple demo map batch test
# by krusty / 20. 9. 2020

trap die SIGINT SIGKILL

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

i=0         # iterator in the actions loop
apikey=""   # apikey for playing
endpoint="https://text.n0p.cz/"

#
# functions
#

function die {
    echo $1
    rm -rf .tmp
    exit 1
}

function tools_check {
    which curl  &> /dev/null || die "curl tool required..."
    which jq    &> /dev/null || die "jq tool required..."
}

function api_init {
    # connection test
    curl -sSL "$endpoint" &> /dev/null || die "connection error - endpoint cannot be reached..."

    # register call
    unistring=$(date +%s | shasum -a 256 | cut -d' ' -f1)
    apikey=$(curl -sSL "$endpoint?register=$unistring" | jq -r '.api.apikey')

    [[ $apikey ]] || die "no apikey received from server..."

    echo $apikey
}

function api_call {
    curl -sSL "$endpoint?apikey=$apikey&action=$1"
}

#
# script start
#

# init
cd $(dirname $0)
echo $(basename $0)
[[ -d .tmp ]] || mkdir .tmp
tools_check
apikey=$(api_init)

# loop through actions set
for action in ${actions[@]}; do
    i=$((i+1))
    api_call $action > .tmp/$i
done

# final check if game ended
[[ $(cat .tmp/$i | jq -r '.player.game_ended') = "true" ]] \
    && echo "test successful." \
    || die "game not eneded, check .tmp dir for curl logs..."

rm -rf .tmp