# GEO Base Project

This project sets up a GEO environment using Docker, PHP 8.3, Apache, and MySQL.

## Prerequisites

- Docker and Docker Compose installed on your system.
- PHP 8.3-compatible setup.

## Setup Instructions

### 1. Clone the Repository

Clone this repository to your local machine:

```bash
git clone git@github.com:DiazSebastianAlejandro/geo.git
cd geo
```

### 2. Build and Start the Docker Containers

Run the following command to build and start the Docker containers:

```bash
docker-compose up -d --build
```

This will:
- Build the PHP 8.3 and Apache container.
- Start the MySQL container with `root/root` credentials.

### 3. Install Composer Dependencies

Once the containers are up, enter the PHP container and run `composer install`:

```bash
docker exec www bash
composer install
```

### 4. Update Your Hosts File

Add the following entry to your system's `hosts` file to access the CMS locally:

```text
127.0.0.1 mac.geo.com
```

### 5. Access the Application

- **HTTP:** [http://mac.geo.com:8080](http://mac.GEO.com:8080)
- **HTTPS:** [https://mac.geo.com:8443](https://mac.GEO.com:8443)

## Available Commands

- **Start Docker Containers:**
  ```bash
  docker-compose up -d
  ```

- **Stop Docker Containers:**
  ```bash
   docker-compose down
  ```

- **Rebuild Docker Images:**
  ```bash
  docker-compose up -d --build
  ```
  
- **Enter the PHP Container:**
  ```bash
  docker compose exec www bash
    ```
  
- **Enter Mysql Containder** 
  ```bash
  docker exec -it geo-db8-1 bash
  ```

- **Create MySQL Database:**
  ```bash
  CREATE DATABASE geo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE DATABASE geo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```
 
- **Run migrations:**
  ```bash
   vendor/bin/phinx migrate
  ```
  - **Run migrations for db testing:**
  ```bash
   vendor/bin/phinx migrate -e testing
  ```
