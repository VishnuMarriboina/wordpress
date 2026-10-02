# WordPress Training Project

Local WordPress development environment using Docker.

| Service    | URL                         |
|------------|-----------------------------|
| WordPress  | http://localhost:8080       |
| WP Admin   | http://localhost:8080/wp-admin |
| phpMyAdmin | http://localhost:8081       |

## Requirements

- Docker Desktop (on Windows: enable **Settings → Resources → WSL Integration** for your distro)

## First-time setup

```bash
cp .env.example .env   # already done; edit values if you want
./setup.sh
```

This starts the containers, installs WordPress, activates `training-theme` and
`training-core`, and sets pretty permalinks. Default login: `admin` / `admin123`
(change in `.env` before running).

## Everyday commands

```bash
docker compose up -d                          # start
docker compose down                           # stop
docker compose down -v                        # stop AND delete database + core files
docker compose logs -f wordpress              # logs
docker compose run --rm wpcli wp plugin list  # WP-CLI
docker compose exec wordpress tail -f /var/www/html/wp-content/debug.log
```

## Structure

```
.
├── docker-compose.yml       # WordPress, MariaDB, phpMyAdmin, WP-CLI
├── .env / .env.example      # ports, DB credentials, admin user
├── config/php.ini           # upload size, memory limits
├── setup.sh                 # one-time install script
└── wp-content/
    ├── themes/training-theme/   # your custom theme (live-mounted)
    └── plugins/training-core/   # your custom plugin (live-mounted)
```

WordPress core lives in a Docker volume; only your theme and plugin are in this
folder, so edits show up instantly. Install other plugins/themes from WP Admin.
