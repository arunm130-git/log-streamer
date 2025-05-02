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
