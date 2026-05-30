# Dev Container Configuration

This directory contains the configuration for developing the DMS Calendar application in a VS Code Dev Container.

## Prerequisites

- [Visual Studio Code](https://code.visualstudio.com/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/Mac) or Docker Engine (Linux)
- [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers) for VS Code

## Getting Started

1. Clone the repository including submodules:
   ```bash
   git clone --recursive https://github.com/Dallas-Makerspace/calendar.git
   cd calendar
   ```

2. Open the project in VS Code:
   ```bash
   code .
   ```

3. When prompted, click "Reopen in Container" or use the Command Palette (F1) and select "Dev Containers: Reopen in Container"

4. Wait for the container to build and start. This may take several minutes on first run.

5. Once ready, the application will be available at:
   - Calendar: http://localhost:8000
   - Mail (MailHog): http://localhost:8025
   - PhpMyAdmin: http://localhost:8081
   - OpenLDAP Admin: http://localhost:8888

## Features

The dev container includes:

- **PHP 7.4** with Apache
- **Xdebug 3.1.6** pre-configured for debugging
- **Composer** for dependency management
- **MariaDB** database
- **OpenLDAP** for authentication testing
- **Keycloak** for OpenID Connect testing
- **MailHog** for email testing
- **Useful VS Code extensions**:
  - Intelephense (PHP IntelliSense)
  - PHP Debug
  - Twig language support
  - GitLens
  - Docker support

## Default Credentials

- **Application**:
  - Regular user: `user1` / `password`
  - Admin user: `user2` / `password`
- **OpenLDAP**: `cn=admin,dc=dms,dc=local` / `Adm1n!`
- **Database**: `calendar` / `calendar`

## Development Workflow

### Running Tests

```bash
composer test
```

### Running Unit Tests Only

```bash
composer unit
```

### Checking Code Standards

```bash
composer cs
```

### Database Migrations

```bash
bin/cake migrations migrate
bin/cake migrations seed --seed DatabaseSeed
```

### Debugging with Xdebug

Xdebug is pre-configured and ready to use:

1. Set breakpoints in your PHP code
2. Press F5 or use the "Run and Debug" panel
3. Select "Listen for Xdebug" configuration
4. Make a request to the application
5. Execution will pause at your breakpoints

## Troubleshooting

### Container won't start

- Ensure Docker is running
- Try rebuilding the container: Command Palette → "Dev Containers: Rebuild Container"
- Check Docker logs for errors

### Permission issues

The container runs as root by default to avoid permission issues with mounted volumes.

### Database connection errors

Ensure all containers are running:
```bash
docker compose ps
```

### Apache not starting

The devcontainer uses `sleep infinity` to keep the container running. To start Apache manually:
```bash
/var/www/.docker/startup.sh
```

Or use the provided startup script in a new terminal.

## Customization

You can customize the dev container by editing:
- `.devcontainer/devcontainer.json` - VS Code settings, extensions, and features
- `.devcontainer/docker-compose.devcontainer.yml` - Container-specific overrides
- Main `docker-compose.yml` - Service definitions (use carefully)

## Differences from Standard Docker Compose

The dev container configuration:
- Overrides the app container's command to `sleep infinity` for interactive development
- Automatically forwards common development ports
- Installs development-focused VS Code extensions
- Configures Xdebug for remote debugging from VS Code

You can still use `docker compose` commands normally if needed.
