# BibleStudyMan
My Bible study website

- MySQL is required - build the database from files in the 'database' folder
- The application is configured using environment variables. Copy `.env.example` to `.env` and modify it with your database credentials.

## Local Environment Setup with Docker
### Prerequisites
Ensure [Docker](https://www.docker.com/get-started/) is installed:

```sh
docker compose version
```

Example output:
```
Docker Compose version v2.32.4
```

### Setup Steps

1. Copy the environment template:
   ```sh
   cp .env.example .env
   ```
2. Edit `.env` if needed.
3. Start the environment:
   ```sh
   docker compose up
   ```
4. Seed the database in a separate terminal:
   ```sh
   ./scripts/seed-db.sh
   ```
   Ignore password warnings from MySQL if they appear.
5. Access the site:
   [http://localhost:8080/site/](http://localhost:8080/site/)

### Safety checks

Run PHP syntax checks:

```sh
./scripts/php-lint.sh
```

Run basic local smoke tests after starting and seeding the Docker environment:

```sh
python3 scripts/smoke-test.py
```

These checks are deliberately simple: they prove the old site still starts and key pages do not show obvious PHP/database fatal errors.

### Managing the Environment

##### Stop
Press `Ctrl+C` in the terminal.

##### Restart
  ```sh
  docker compose up
  ```

##### Terminate
  ```sh
  docker compose down
  ```

##### Remove Database Volume
  ```sh
  docker volume rm biblestudyman_dbdata
  ```

  If you encounter an error about a missing volume, list available volumes with:
  ```sh
  docker volume ls
  ```

  Then replace `biblestudyman_dbdata` with the correct name if a matching one exists
