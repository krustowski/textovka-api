# textovka API

PHP REST API text-based game engine v1

API endpoint:\
https://text.n0p.cz/

Documentation (CZ):\
https://wiki.n0p.cz/doku.php?id=misc:textovka

## Structure of the repo

`prod.sh`

A bash script used for a rapid update of the repo on the production server (`frank`) –
a whole data structure there is removed and newely untared (`data/` directory is *flushed*).

`maps/demo.json`

A JSON file with the server-side map. The required format is detaily described in the documentation. Otherwise an API exception could be raised.

`game.log`

A file containing the server-side log with the following format:

```
timestamp / nickname / action / IP
```

`src/Game.php`

The engine file.

`data/`

A directory where players' data are stored as JSON files.
