# BibleStudyMan
My Bible study website

- MySQL is required - build the database from files in the 'database' folder
- sqlCon.php must be modified to suit your local configuration

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
   docker exec -i biblestudyman-db-1 mysql -uroot -pmyrootpass bible < ./database/bibleComplete.sql
   ```
   Ignore password warnings
5. Access the site:
   [http://localhost:8080/site/](http://localhost:8080/site/)

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
