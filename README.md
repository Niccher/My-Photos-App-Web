# 📸 Photos: Next-Gen Media Management

A high-performance, technically sophisticated web application built for the modern digital archivist. **Photos** provides a seamless, secure, and intelligent environment to manage, discover, and relive your media collections.

---

## ⚡ Core Pillars

### 1. High-Speed Discovery & Search
- **Multidimensional Search**: Query your library using filenames, EXIF metadata (Camera Model, Software), or temporal filters. 
- **Lightning-Fast UI**: Integrated debounced search with natural "Enter-to-Submit" triggers to prevent disruptive reloads.
- **Geospatial Explore**: Native **Leaflet.js** integration. Visualize your journey through interactive Markers and high-density **Heatmaps** powered by embedded GPS metadata.

### 2. Intelligent Organization
- **Smart Memories**: Automatically surfaces "On This Day" retrospectives from past years and 6-month timeline highlights.
- **Dynamic Albums**: Create structured collections with instant assignment and curated grid layouts.
- **Bulk Orchestration**: Multi-select mode for batch operations: mass Archiving, Favoriting, Deletion, and Album Assignment.

### 3. Analytics & Insights
- **Heuristic Dashboard**: Real-time telemetry on storage consumption, MIME-type distribution, and monthly upload velocity.
- **Public/Internal Metrics**: Track sharing activity and link generation statistics at a glance.

---

## 🛠 Technical Architecture

- **Backend**: **CodeIgniter 4** (PHP 8.2+) — Utilizing a robust MVC architecture with high-security route grouping.
- **Security**: Powered by **CodeIgniter Shield**. Enterprise-grade session management, password hashing, and role-based data boundaries.
- **Geospatial Engine**: **Leaflet.js** for high-performance GIS rendering on the client side.
- **Data Layer**: Optimized MySQL/MariaDB queries with advanced `DATE_FORMAT` and `GROUP BY` patterns for efficient time-series grouping.
- **Frontend**: **Bootstrap 5** with a sleek, dark-themed dashboard and HSL-tailored visual tokens.

---

## 🚀 The AI Roadmap: "Deep Intelligence"

We are currently architecting a **Python-based AI Sidecar** to bring state-of-the-art vision models directly to your server:

- **OCR (Optical Character Recognition)**: Extracting searchable text from documents and screenshots using **Tesseract**.
- **Neural Object Detection**: Automated tagging of pets, vehicles, and landscapes using **YOLO/MobileNet**.
- **Semantic Scene Understanding**: Advanced indexing using **CLIP** to allow natural language queries like *"Sunset at the beach with mountains"*.

---

## 📦 Deployment

### Prerequisites
- PHP 8.1+ (with GD/Imagick for thumbnail processing)
- MySQL 8.0+ or MariaDB 10.11+
- Composer

### Installation
1. Clone the repository to your server.
2. Run `composer install` to pull in dependencies (Shield, etc.).
3. Configure your `.env` with database credentials and `app.baseURL`.
4. Run migrations: `php spark migrate`.
5. Start your server: `php spark serve`.

---

## 🛡 License
This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
