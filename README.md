# DMS Calendar

## Development Quickstart Guide

For an overview of how the calendar works, see [./docs/README.md](./docs/README.md).

1. Fork this repo in Github.
2. Clone your forked version to your development computer using whatever tool works best for you (CLI/GUI/IDE/etc). Make sure to use the recursive clone flag.
3. If you forget to use the recursive clone, make sure any submodules are up to date with `git submodule update --init`
4. Create a local branch to hold your changes, you'll want to leave the master branch alone and keep it synced with the upstream repo.
5. Install a version of [Docker Desktop](https://www.docker.com/products/docker-desktop/), or something like [Rancher Desktop](https://rancherdesktop.io/)
6. (For *nix systems eg Linux, MacOS, etc): Run `./setup.sh` to create local directories with the correct permissions and check your docker installation.
7. After installation, you should be able to navigate to the root directory of this repository. Then type `docker compose up`
8. After some time, the application will be available at http://localhost:8000
9. Mail will be available at http://localhost:8025. PhpMyAdmin is at http://localhost:8081. Open LDAP is at http://localhost:8888. Keycloak (OpenID Connect) is at http://keycloak:8080 (Add local DNS entry for `keycloak 127.0.0.1`).
10. The docker environment automatically maps your local filesystem into the server. Any changes you make in the PHP files will be reflected. You may need to rebuild the app container if you change dependencies.
11. VS Code launch settings and xdebug files are included for convenience.

Ctrl+C will stop your containers
`docker compose down` will reset your containers along with your data
Log into OpenLDAP with `cn=admin,dc=dms,dc=local`, password `Adm1n!`
Default credentials for application will be `user1` and `password`
user2 may be used for admin rights

More details about local development of the calendar system can be found in the [local development documenation](docs/local-dev.md).
