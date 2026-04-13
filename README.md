# 📸 Photos: Next-Gen Media Management

A high-performance, technically sophisticated web application built for the modern digital archivist. **Photos** provides a seamless, secure, and intelligent environment to manage, discover, and relive your media collections through advanced search and geospatial visualization.

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed on your machine:

- **Docker Desktop**: Version 20.10+
- **Docker Compose**: Version 2.0+
- **Git**: For version control
- **Composer**: (Optional) For local development without Docker

---

## 🚀 Quick Start (The Docker Way)

The fastest way to get your environment up and running is using Docker Compose. This project is pre-configured with a hardened three-tier architecture.

### 1. Build and Run
Run the following command in the project root:
```bash
docker compose up -d --build
```

### 2. Access the Application
Once the containers are healthy, access the services via these URLs:

| Service | URL | Port |
| :--- | :--- | :--- |
| **App** | [http://localhost:8080](http://localhost:8080) | 8080 |
| **phpMyAdmin** | [http://localhost:8081](http://localhost:8081) | 8081 |

---

## 📂 Directory Structure

```text
.
├── app/                # Application logic (Controllers, Models, Views)
├── public/             # Entry point (index.php, CSS, JS, Images)
├── system/             # CodeIgniter 4 framework core
├── writable/           # Temporary files, logs, and uploads (Auto-managed)
├── docker/             # Docker configuration files (PHP/Apache)
├── Dockerfile          # Production-hardened application image
├── docker-compose.yml  # Service orchestration
└── .dockerignore       # Build optimization rules
```

> [!NOTE]
> The `writable/` directory is critical for application state. In the Docker environment, permissions are managed automatically via the entrypoint script.

---

## ⚙️ Environment Configuration

1. **Setup `.env`**: Copy the provided template to create your environment file.
   ```bash
   cp .env.docker .env
   ```
2. **Database Connectivity**: The Docker environment uses `db` as the hostname. Ensure your `.env` matches the services in `docker-compose.yml`.

---

## 🏗️ Optimization & Build Details

This project uses a **Zero-Junk Build** strategy via the `.dockerignore` file.

- **Weight Reduction**: Strips `node_modules`, `.git`, and documentation from the final image.
- **Security**: Ensures sensitive local logs and cache files are never baked into the container image.
- **Speed**: Reduces the Docker build context size, leading to significantly faster deployments.

---

## 🗄️ Database Migrations

To run CodeIgniter 4 migrations within the containerized environment, use the following `docker exec` command:

```bash
docker exec -it photos-app php spark migrate
```

- **Seeders**: `docker exec -it photos-app php spark db:seed [SeederName]`
- **Status**: `docker exec -it photos-app php spark migrate:status`

---

## 🛡️ Security & Permissions

- **Principle of Least Privilege**: The application runs under the `www-data` user.
- **Writable Directory**: The `docker-entrypoint.sh` script automatically ensures the `writable/` folder structure exists and has the correct permissions (`777` for the group/user) upon container startup.
- **Production Hardening**: The image is based on `php:8.2-apache`, with `mod_rewrite` enabled and `opcache` tuned for high-performance script execution.

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the Project.
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the Branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

Please ensure your code follows the **PSR-12** coding standard.

---

## 📜 License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
