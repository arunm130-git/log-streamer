# Symfony App - Docker Setup

Basic Symfony app running with Docker and MySQL.

## Prerequisites

- Docker & Docker Compose
- `make` (Install via: `sudo apt install make` on Ubuntu)
- (Optional) PHP and Composer locally

## Installation

1. **Clone the project**

   ```bash
   git clone <your-repo-url>
   cd <your-project-folder>
   ```

2. **Configure environment**

   Update `.env` or `.env.local`:

   ```env
   DATABASE_URL="mysql://root:root@db:3306/app"
   ```

3. **Start application**

   ```bash
   make build
   make up
   make install
   ```

4. **Access**

   👉 `http://localhost:8000`

## Running Tests

```bash
make test
```

## Useful Commands

- **Start containers**: `make up`
- **Stop containers**: `make down`
- **Rebuild containers**: `make rebuild`
- **Install PHP dependencies**: `make install`
- **Run Symfony console commands**: `docker compose run --rm app php bin/console <command>`
- **Run Composer inside container**: `docker compose run --rm app composer <command>`