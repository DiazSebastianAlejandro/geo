# GEO Base Project

This project sets up a GEO environment using Docker, PHP 8.3, Apache, and MySQL. It includes Symfony's `EventDispatcher` library for event handling.

## Prerequisites

- Docker and Docker Compose installed on your system.
- PHP 8.3-compatible setup.

## Setup Instructions

### 1. Clone the Repository

Clone this repository to your local machine:

```bash
git clone <repository-url>
cd <repository-folder>
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

Create uploads folder:
```bash
mkdir -p uploads uploads/changelog uploads/temp uploads/error_handler
```

### 4. Update Your Hosts File

Add the following entry to your system's `hosts` file to access the CMS locally:

```text
127.0.0.1 mac.GEO.com
```

### 5. Access the Application

- **HTTP:** [http://mac.GEO.com:8080](http://mac.GEO.com:8080)
- **HTTPS:** [https://mac.GEO.com:8443](https://mac.GEO.com:8443)

## Installed Packages

This project includes the following PHP packages:

- Symfony EventDispatcher (`symfony/event-dispatcher`)

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

- **Run Composer Commands:**
  ```bash
  docker exec www bash
  <command>
  ```

## Additional Notes

Ensure that all required volumes and configurations (e.g., certificates for SSL) are set up correctly before starting the containers.

---

For issues or contributions, please open an issue or submit a pull request.