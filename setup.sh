#!/bin/bash

# Setup script to help on *nix systems with local development prerequisites

if ! command -v docker-compose &> /dev/null
then
  echo "Docker compose not found. Please install it"
  echo "(https://docs.docker.com/compose/) and run this again."
  exit
fi

composever="$(docker-compose version --short)"
requiredver="2.0.0"
 if [ "$(printf '%s\n' "$requiredver" "$composever" | sort -V | head -n1)" = "$requiredver" ]; then
        echo "Dcoker Compose greater than or equal to ${requiredver} (${composever}) found"
 else
        echo "Docker Compose less than ${requiredver} (${composever}) found. Please upgrade and re-run this script."
        exit
 fi


echo
echo "Creating and setting permisions:"
echo " - logs/var/apache2"
mkdir -p logs/var/apache2 & chmod 777 logs/var && chmod 777 logs/var/apache2
echo " - logs/www"
mkdir -p logs/www & chmod 777 logs/www
echo " - tmp"
mkdir -p tmp && chmod 777 tmp
echo " - vendor"
mkdir -p vendor && chmod 777 vendor
echo "Done. All local directories and permissions should be set"

echo
echo "Congratulations! If there are no errors above, you should be ready to run"
echo "the calendar locally. Start it up with 'docker-compose up' and then open"
echo "http://localhost:8000 in your browser."
