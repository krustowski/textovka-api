#!/bin/bash

# prod.sh
# skript pro efektivni aktualizaci textovky na produkci
# by krusty / 28. 3. 2020

cd && tar -czf textovka.tar.gz textovka-api/ && \
 scp ~/textovka.tar.gz frank:~/ && \
 ssh frank 'rm -rf ~/textovka-api && tar -xzf ~/textovka.tar.gz && chmod o+w ~/textovka-api/data ~/textovka-api/game.log && chmod o-rx ~/textovka-api/game.log ~/textovka-api/README.md ~/textovka-api/prod.sh' && \
 echo "ok" || \
 echo "fail"
