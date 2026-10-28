Example of how to download a specific version from github:
git pull
git checkout tags/v2.1.0 -b v2.1.0

uploaded files are in ./files
CakePHP logs (error.log, debug.log) are in /opt/calendar/logs on the host. Create it once, owned by the
container's www-data user (uid 33), before starting:  sudo install -d -o 33 -g 33 /opt/calendar/logs
keys, etc are in ./.env

docker compose up -d --env-file ./.env-prod -f ./calendar/docker-compose.prod.yml -f ./volume-override.yml

