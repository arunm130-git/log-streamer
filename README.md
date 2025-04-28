
# Symfony App - Docker Setup

This is a basic Symfony application running with Docker and MySQL.  
The setup is kept minimal for easy development use.

---

## Prerequisites

- Docker
- Docker Compose
- (Optional) PHP and Composer installed locally if you want to run commands without Docker.

---

## Installation

### 1. Clone the repository

```bash
git clone <your-repo-url>
cd <your-project-folder>
```

### 2. Copy or update the `.env` file

Ensure your `.env` or `.env.local` contains the correct database URL:

```dotenv
DATABASE_URL="mysql://root:root@db:3306/app"
```

### 3. Build and start the containers

```bash
docker-compose up --build
```

### 4. Install Symfony dependencies

In a new terminal window:

```bash
docker-compose exec <container_name> composer install
```

### 5. (Optional) Run database migrations

If you have migrations ready, run:

```bash
docker-compose exec <container_name> php bin/console doctrine:migrations:migrate
```

### 6. Access the application

Open your browser and visit:  
👉 [http://localhost:8000](http://localhost:8000)

---

## Useful Commands

- **Run Symfony console commands**:

```bash
docker-compose exec <container_name> php bin/console <command>
```

- **Run Composer inside container**:

```bash
docker-compose exec <container_name> composer <command>
```

- **Stop containers**:

```bash
docker-compose down
```

---
