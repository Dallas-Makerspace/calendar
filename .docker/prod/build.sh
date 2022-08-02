#!/bin/bash

docker-compose --env-file ./.env -f ./calendar/docker-compose.prod.yml -f ./volume-override.yml build
