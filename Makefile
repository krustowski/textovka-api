#
# textovka-api / Makefile
# May 7, 2022 / krusty@savla.dev
#


#
# runtime variables and constants
#

-include .env
include .env.example

# just a tiny control const for bin/engine-test.sh executable (ensure env vars are loaded)
MAKEFILE_CALL=true

# define standard colors
# https://gist.github.com/rsperl/d2dfe88a520968fbc1f49db0a29345b9
ifneq (,$(findstring xterm,${TERM}))
	BLACK        := $(shell tput -Txterm setaf 0)
	RED          := $(shell tput -Txterm setaf 1)
	GREEN        := $(shell tput -Txterm setaf 2)
	YELLOW       := $(shell tput -Txterm setaf 3)
	LIGHTPURPLE  := $(shell tput -Txterm setaf 4)
	PURPLE       := $(shell tput -Txterm setaf 5)
	BLUE         := $(shell tput -Txterm setaf 6)
	WHITE        := $(shell tput -Txterm setaf 7)
	RESET        := $(shell tput -Txterm sgr0)
else
	BLACK        := ""
	RED          := ""
	GREEN        := ""
	YELLOW       := ""
	LIGHTPURPLE  := ""
	PURPLE       := ""
	BLUE         := ""
	WHITE        := ""
	RESET        := ""
endif

export


#
# main targets
#

all: info

.PHONY: info
info:
	@echo -e "\n${GREEN} ${APP_NAME} / Makefile ${RESET}\n"
	@echo -e "${YELLOW} make run${RESET} \t build and run the container"
	@echo -e "${YELLOW} make test${RESET}  \t test the container\n"

	@echo -e "${YELLOW} make exec${RESET}  \t enter inner shell"
	@echo -e "${YELLOW} make logs${RESET}  \t tail running container's logs"
	@echo -e "${YELLOW} make rebuild${RESET} \t rebuild image and recreate the container\n"
#@echo -e "${YELLOW} make push${RESET}  \t push image into the registry\n"

# TODO: test script is broken!
#run: run_test
.PHONY: run
run: start


#
# aux targets as "a pipe"
#

.PHONY: git_pull
git_pull:
	@echo -e "\n${YELLOW} Git pull ...${RESET}\n"
	@git pull -f

.PHONY: composer 
composer: git_pull
	@echo -e "\n${YELLOW} Composer update ...${RESET}\n"
	@composer install && composer update

.PHONY: build
build:  composer
	@echo -e "\n${YELLOW} Building the image ...${RESET}\n"
	@docker-compose build --no-cache

.PHONY: start
start:	build
	@echo -e "\n${YELLOW} Starting the container ...${RESET}\n"
	@docker-compose up --detach

.PHONY: run_test
run_test: start
	@echo -e "\n${YELLOW} Testing the container ...${RESET}\n"
	@bash ./bin/engine-test.sh

.PHONY: stop
stop:
	@echo -e "\n${YELLOW} Stopping the container ...${RESET}\n"
	@docker-compose down


#
# others, dev targets
#

.PHONY: rebuild
rebuild:
	@echo -e "\n${YELLOW} Rebuilding and reruning the container ...${RESET}\n"
	@git pull 2> /dev/null && \
		docker-compose build && \
		docker-compose up --detach

.PHONY: sh
sh:
	@echo -e "\n${YELLOW} Entering container's shell ...${RESET}\n"
	@docker exec -it ${DOCKER_CONTAINER_NAME} sh
	

.PHONY: logs
logs:
	@echo -e "\n${YELLOW} Tailing logs ...${RESET}\n"
	@docker logs -f ${DOCKER_CONTAINER_NAME}

