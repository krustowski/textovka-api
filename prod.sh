#!/bin/bash

# prod.sh
# skript pro efektivni aktualizaci textovky na produkci
# by krusty / 28. 3. 2020

cd && tar -czf textovka.tar.gz textovka-api/ && \
 scp ~/textovka.tar.gz frank:~/ && \
 ssh frank 'rm -rf ~/textovka-api && tar -xzf ~/textovka.tar.gz && chmod o+w ~/textovka-api/data ~/textovka-api/game.log' && \
 echo "ok" || \
 echo "fail"
