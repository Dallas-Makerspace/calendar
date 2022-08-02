Example of how to download a specific version from github:
git pull
git checkout tags/v2.1.0 -b v2.1.0

uploaded files are in ./files
keys, etc are in ./.env

docker-compose up -d --env-file ./.env-prod -f ./calendar/docker-compose.prod.yml -f ./volume-override.yml

