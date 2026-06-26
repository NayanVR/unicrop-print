# Unicrop Print

Print & Cutting job management system. Laravel + Blade + Tailwind, MySQL for data, [Garage](https://garagehq.deuxfleurs.fr/) (S3-compatible) for design file storage.

## Roles

- **admin** — full access: dashboard, uploader, print station, cutting station, billing logs, settings.
- **uploader** — uploads design files and sends them to the print queue.
- **printer** — runs the print station and cutting station.

Roles are enforced by the `role` middleware (`app/Http/Middleware/EnsureRole.php`), see `routes/web.php`.

## Job lifecycle

`pending` (uploaded, waiting to print) → `cutting` (printed, waiting to cut) → `completed` (cut, billed). See `App\Models\PrintJob` and `App\Enums\JobStatus`.

## File storage & serving

Design files are stored on Garage via the `s3` filesystem disk and are never exposed to the public internet directly. The Garage container has no published ports — the `app` container talks to it over the internal Docker network (`AWS_ENDPOINT=http://garage:3900`), and browsers download files through `GET /jobs/{printJob}/file` (`App\Http\Controllers\FileController`), which streams the file from Garage through Laravel. This means you don't need a second domain/subdomain for storage, and `AWS_URL` is unused.

## Running locally with Docker

```bash
cp .env.example .env   # already done if you cloned this repo as-is — review the values
docker compose up -d --build
```

This starts:

| Service | Purpose                                   |
|---------|--------------------------------------------|
| `app`   | PHP-FPM running the Laravel app, runs migrations on boot |
| `nginx` | Web server, exposed on `APP_PORT` (default `8080`) |
| `mysql` | MySQL 8.4 database |
| `garage`| S3-compatible object storage for uploaded designs |

### One-time Garage setup

Garage needs its single-node layout applied and a bucket/access key created before the app can upload files. This runs entirely over the internal Docker network via `docker compose exec` — no ports need to be published. After `docker compose up -d`, run:

```bash
docker compose exec garage /scripts/garage-setup.sh
```

Copy the printed `Key ID` / `Secret` into `.env` as `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY`, then:

```bash
docker compose restart app
```

### Seed an admin account

```bash
docker compose exec app php artisan db:seed
```

Creates `admin@unicrop.test` / `password` with the `admin` role, default sizes, and a default cutting rate. Create `uploader`/`printer` users from the admin's `/register` flow or via `php artisan tinker`, then set their `role` column.

Visit `http://localhost:8080`.

## Deploying (e.g. Dokploy)

The repo's `docker-compose.yml` can be deployed as-is on any Docker Compose-based platform (Dokploy, Coolify, plain `docker compose` on a VM):

- Set all secrets (`APP_KEY`, `DB_PASSWORD`, `GARAGE_RPC_SECRET`, `GARAGE_ADMIN_TOKEN`, `AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY`) through the platform's environment variable UI rather than committing a real `.env`.
- Run the Garage one-time setup (above) once against the deployed `garage` container, then put the resulting key/secret back into the platform's env vars and redeploy `app`.
- Point your platform's reverse proxy / domain at the `nginx` service's container port `80` — that's the only port that needs to be publicly reachable. Neither `mysql` nor `garage` need a public port.

## Local development without Docker

Requires PHP 8.3+, Composer, pnpm, and a MySQL instance.

```bash
composer install
pnpm install
php artisan key:generate
php artisan migrate --seed
pnpm build   # or `pnpm dev` while developing
php artisan serve
```

You'll still need a Garage (or other S3-compatible) endpoint reachable for file uploads — point the `AWS_*` env vars at it.
