# textovka API

PHP REST API text-based game v1

API endpoint:\
https://text.n0p.cz/

Documentation:\
https://wiki.n0p.cz/doku.php?id=misc:textovka

## Structure of the repo

`prod.sh`

A bash script used for a rapid update of the repo on the production server (`frank`) –
a whole data structure there is removed and newely untared (`data/` directory is *flushed*).

`map.json`

A JSON file with the server-side map. The required format is detaily described in the documentation.

`game.log`

A file containing the server-side log with the following format:

```
timestamp / nickname / action / IP
```
