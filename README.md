# textovka API

PHP REST API text-based game engine

API testing endpoint:\
https://text.n0p.cz/

## Installation

The easiest way to deploy this app is using Docker. This repo is automatically synced with Docker Cloud Build and the stable image is put on https://hub.docker.com/r/krustowski/textovka-api.

### Pull from Docker hub

To run a container, just type following into you server running Docker (app is ported locally to `:8080`):

```bash
# the image is pulled automatically by docker
docker run -d -p 8080:80 --name textovka-api krustowski/textovka-api
```

The API endpoint is then located on http://localhost:8080.

### Using Dockerfile

```bash
git clone https://github.com/krustowski/textovka-api.git ~/textovka-api/
docker build ~/textovka-api --tag=textovka-api-build
docker run -d -p 8080:80 --name textovka-api textovka-api-build
```

### Manually 

To run this API properly, you need PHP 7.3+ with `php-fpm` (PHP processor for proxy, voluntarily) and `php-json` (JSON processor) packages installed. Then I recommend using `nginx` as proxy, as it is very easy to set up.

A raw step-by-step solution can be observed in Dockerfile.

## Structure of the repo

`index.php`

An index file executed while endpoint is opened.

`src/Game.php`

The engine file.

`prod.sh`

A bash script used for a rapid update of the repo on the production server (`frank`) –
a whole data structure there is removed and newely untared (`data/` directory is *flushed*).

`maps/demo.json`

A JSON file with the server-side map. The required format is described in detail below. Otherwise, an API exception could be raised.

`game.log`

A file containing the server-side log with the following format:

```
timestamp / nickname / action / IP
```

`data/`

A directory where players' data are stored as JSON files.

## Registration

For valid HTTPS communication `cURL` tool can be used. The registration is performed via GET request:

```bash
ENDPOINT="https://text.n0p.cz"

curl -sSL "$ENDPOINT/?register=user_name"
```

API key (`apikey`) can be extracted using `jq` tool:

```bash
APIKEY=$(curl -sSL "$ENDPOINT/?register=user_name" | jq '.api.apikey')
```

New user is "registred" as a new JSON file `data/$HASH.json`. The map is assigned randomly from `maps/` directory. For the further playing, the `apikey` has to be included in the query string:

```bash
curl -sSL "$ENDPOINT/?apikey=$APIKEY&action=go-north"
```

## Player variables and structure

| variable | desc | range | default |
|----|----|----|----|
| nickname | user nickname | 32 chars max | |
| hp | health level | 0-100 | 100 |
| inventary | JSON array of picked items | JSON array | `[]` |
| room | current room | | `start_room` defined in map |
| time_registred | self-explanatory | | current UNIX timestamp |
| time_ended | self-explanatory | | game ended UNIX timestamp |
| game_ended | self-explanatory | boolean | false |
| map | randomly assigned map | | `maps/demo.json` |

User JSON structure example (`map` part trimmed):

```json
{
  "nickname": "krusty",
  "hp": 86,
  "inventary": [
    "magic-key"
  ],
  "room": "gg",
  "time_registred": 1586542166,
  "time_ended": 1586542238,
  "game_ended": true,
  "map": {
  }
}
```

## Map structure

Maps are stored in `maps/` directory. The default one is `demo.json` and is very simple. Each map has rooms. These are stored in the `room` object with their proper names (`0001`, `well`, `aa`, `ly` etc). Each room should have its `description` - shown as message to the user when entered. Furthermore, directions (`north`, `south`, `east`, `west`) should be defined as other room names; but it is not neccessary (trap rooms). Objects and items are listed in arrays `objects` and `items`. Actions can be enumerated in `actions` array. If so, `effects` array HAS to be filled properly too, otherwise the API will throw an exception (Invalid map). Each room-defined action can execute an hidden effects (`description` is overwitten, new `direction` is unlocked etc).

| variable | purpose | required |
|----|----|----|
| name | pointer for other rooms | **yes** |
| description | used as message for user when entered | **no**, but strongly recommended |
| directions | defines further paths from such room | **no** (in case of the trap room) |
| items | list of items (for pick for example) | **no** if not defined in `effects` as `required-item` |
| objects | list of room objects (water, fire etc) | **no** if not required in `effects` as `object` |
| actions | list of room defined actions | **no** (transit room, blank room) |
| effects | specifications of room actions | **yes** if actions are listed! (otherwise results in Invalid map exception) |
| hidden | defines room variables to be overwriten by an action | **no** if not defined in `effects` as `show-hidden` |

Simple map example (only one room):

```json
{
   "room": {
      "0001": {
         "description": "A very dark room",
         "north": "0010",
         "south": "0100",
         "west":  "0011",
         "items": [
            "bucket"
         ],
         "objects": [
            "water"
         ],
         "actions": [
            "pick-water"
         ],
         "effects": {
            "pick-water": {
               "type": "fill",
               "required-item": "bucket",
               "object": "water",
               "show-hidden": true,
               "damage-hp": [
                  0, 5
               ]
            }
         },
         "hidden": {
            "description": "The very same room, but the water level is lower now.",
            "east":  "0101"
         }
      }
   }
}
```

## Log structure

The main game log in stored in root directory as `game.log`.

| variable | purpose |
|----|----|
| timestamp| UNIX timestamp (API response timestamp) |
| name | user nickname |
| action | action sent by the user to API |
| IP | IP address of the user |

Trimmed example of the log:

```
1586542166 / krusty / register / IP
1586542166 / krusty / none / IP
1586542169 / krusty / go-south / IP
1586542170 / krusty / pick-bucket / IP
1586542171 / krusty / go-north / IP
1586542172 / krusty / go-north / IP
1586542173 / krusty / pick-water / IP
1586542174 / krusty / go-south / IP
1586542175 / krusty / go-east / IP
1586542177 / krusty / quench-fire / IP
1586542200 / krusty / go-east / IP
```

## Actions

By default, each room has four actions (directions): `go-north`, `go-south`, `go-east`, `go-west`. It is on the map designer, whether these are going to be 
implemented in such room. 

Room-defined action types:

| type | desc |
|----|----|
| pick | used for picking items in room (those have to be listed in `items`) |
| dismiss | self-explanatory, used for fire quenching for example |
| fill | used to fill other objects in `invetary` (bucket with water etc) |
| fight | _to be implemented in v2_ |
| * | generic action (door unlocking etc) |

## TUI

Python3 npyscreen TUI:\
https://github.com/krustowski/textovka-tui

## Version 2 (v2) ideas

- fights with NPCs (used item defines its damage)
- basic multiplayer support (shared maps, paired map solving, fighting etc)
- map choosing @ registration (list given before registration, optional argument in GET request)
- player's password (hashed in JSON, prolly sha512)
- locking oneself at their house (flats/blocks in the map)
- possibility of killing others inside rooms/blocks/flats (how the defend oneself?)
- API returns other room names @ its directions -> can be projected directly to TUI
- queue of requests, or map locks (shared map is being overwritten, or compare/diff players' maps)
- ~~dockerfile + nginx simple config (simple install)~~
- return `"room_visited": true/false` in `room` array
- assign `engine_build` to player @ registration -> 'new API version' notification
- map API logs to docker logs
