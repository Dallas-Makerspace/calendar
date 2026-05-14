# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

DMS Calendar is a CakePHP 3.10 application used by Dallas Makerspace for managing calendar events, registrations, and honoraria. It integrates with LDAP/Active Directory for authentication, and third-party services (Braintree, SparkPost, Twilio) for payments and notifications.

## Development Commands

### VS Code Dev Container (Recommended)

The fastest way to develop is using VS Code with Dev Containers:
1. Open the project in VS Code
2. Click "Reopen in Container" when prompted (or use Command Palette → "Dev Containers: Reopen in Container")
3. VS Code will automatically set up the environment with all dependencies, extensions, and Xdebug

See `.devcontainer/README.md` for detailed information.

### Docker Development (Manual)

Start the development environment:
```bash
docker compose up
```

Stop containers:
```bash
# Ctrl+C to stop
docker compose down  # Reset containers and data
```

The application will be available at:
- Calendar: http://localhost:8000
- Mail (MailHog): http://localhost:8025
- PhpMyAdmin: http://localhost:8081
- OpenLDAP Admin: http://localhost:8888
- Keycloak (OpenID): http://keycloak:8080 (requires DNS entry: `keycloak 127.0.0.1`)

Default credentials:
- Application: user1/password (user2 for admin)
- OpenLDAP: cn=admin,dc=dms,dc=local / Adm1n!

### Testing & Code Quality

Run all tests and code standards:
```bash
composer test
```

Run unit tests only:
```bash
composer unit
# or
./vendor/bin/phpunit -c phpunit.xml.dist
```

Run specific test:
```bash
./vendor/bin/phpunit tests/TestCase/Path/To/SpecificTest.php
```

Check code standards (PSR-2 + CakePHP):
```bash
composer cs
# or
./vendor/bin/phpcs -p --extensions=php --standard=vendor/cakephp/cakephp-codesniffer/CakePHP ./src ./tests
```

### Database Migrations

Run migrations:
```bash
bin/cake migrations migrate
```

Create new migration:
```bash
bin/cake bake migration MigrationName
```

Seed database:
```bash
bin/cake migrations seed --seed DatabaseSeed
```

### Dependencies

Install/update PHP dependencies:
```bash
composer install
```

After changing dependencies in `composer.json`, rebuild the Docker container:
```bash
docker compose build app
docker compose up
```

## Architecture

### Framework & Patterns

This is a CakePHP 3 MVC application following standard CakePHP conventions:

- **Entry Point**: `index.php` → `src/Application.php`
- **Routing**: `config/routes.php` defines URL-to-Controller mappings
- **Controllers**: `src/Controller/` - Handle HTTP requests, use CRUD plugin for common operations
- **Models**:
  - Tables: `src/Model/Table/` - Database operations, validations, associations
  - Entities: `src/Model/Entity/` - Individual record representation
- **Views/Templates**: `src/Template/` - Twig templates (via WyriHaximus/TwigView plugin)
- **Configuration**: `config/` directory, bootstrapped via `config/bootstrap.php`

### Key Plugins Used

- **Crud**: Provides scaffolded CRUD actions in controllers
- **BootstrapUI**: Bootstrap 4 styling for views/forms
- **Josegonzalez/Upload**: File upload handling
- **CsvView**: CSV export functionality
- **Migrations**: Database schema versioning (Phinx-based)

### Authentication System

The application supports two authentication methods configured in `src/Controller/AppController.php`:

1. **LDAP/Active Directory** (`src/Auth/AdAuthenticate.php`): Primary authentication against DMS Active Directory
2. **OpenID Connect** (`src/Auth/OpenIDConnectService.php`): Integration with Keycloak for SSO

Authentication configuration is loaded from environment variables (see `.env`).

### Main Controllers

- **EventsController**: Core calendar functionality - largest/most complex controller
  - Event creation, editing, viewing
  - Registration management
  - Honoraria workflow (pending/accepted/rejected/upcoming)
  - Feed generation (iCal, RSS)
- **RegistrationsController**: Event registration/attendance tracking
- **UsersController**: User login/logout
- **HonorariaController**: Payment tracking for instructors
- **CalendarAdminController**: Administrative functions

### Database Schema

Migrations in `config/Migrations/`:
- `20180505225727_Initial.php`: Core tables (events, registrations, categories, etc.)
- `20191214222903_Logging.php`: Audit logging
- `20211025111200_SuperConfig.php`: Super configuration table
- `20231108194647_AddInstructorNotificationsPerEvent.php`: Per-event notification settings

Seeds in `config/Seeds/` provide initial data for categories, committees, configurations, rooms, tools, and prerequisites.

### External Service Integrations

- **MariaDB**: Primary data store (MySQL-compatible)
- **LDAP/Active Directory**: DMS account authentication
- **Braintree**: Credit card processing for event fees
- **SparkPost**: Email notifications
- **Twilio**: SMS notifications

All credentials configured via `.env` environment variables.

### File Structure

```
src/
├── Application.php          # App bootstrap, plugin loading, middleware
├── Auth/                    # Custom authentication adapters
├── Controller/
│   ├── AppController.php    # Base controller with Auth/CRUD setup
│   ├── EventsController.php # Main calendar logic (largest file)
│   └── Component/           # Reusable controller components
├── Model/
│   ├── Table/              # Database table classes
│   ├── Entity/             # Entity classes
│   └── Behavior/           # Model behaviors
├── Template/               # Twig view templates
└── View/                   # View layer customizations

config/
├── app.default.php         # Main configuration template
├── bootstrap.php           # App initialization
├── routes.php              # URL routing
├── Migrations/             # Database migrations
└── Seeds/                  # Database seed files

tests/
├── TestCase/              # Unit tests mirror src/ structure
└── Fixture/               # Test fixtures
```

## Development Notes

### Docker Environment

- Local filesystem is volume-mounted into containers, so code changes are immediately reflected
- Exception: Changes to `composer.json`, `Dockerfile`, or `docker-compose.yml` require rebuilding containers
- Logs are accessible at:
  - `logs/www/error.log`: CakePHP errors
  - `logs/var/apache2/error.log`: Apache errors
  - `logs/var/apache2/access.log`: HTTP access logs

### Xdebug

Xdebug 3.1.6 is installed in the development container. VS Code launch settings are included in `.vscode/`.

### CakePHP Version

This project uses **CakePHP 3.10**. Always consult [CakePHP 3.x documentation](https://book.cakephp.org/3/en/) when working with framework features. The request cycle and controller flow documentation is particularly helpful.

### Common Pitfalls

- Forgetting recursive clone: Run `git submodule update --init` if `dms-ad-openldap/` is missing
- Permission errors on *nix: Run `./setup.sh` or manually create `logs/www`, `logs/var`, `tmp`, `vendor` directories
- Docker Compose version: Requires docker-compose v2+
- Scripts not executable: Run `chmod +x .docker/*.sh` if needed
