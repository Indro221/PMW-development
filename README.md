# PickMyWaste – International E-Waste Platform 🌍♻️

Official source code repository for **PickMyWaste**, a live international e-waste collection and recycling enterprise operating across **India, the Middle East, and Europe**. 

This repository houses a production-grade, high-performance WordPress custom child theme engineered from scratch for commercial and residential lead generation, featuring interactive 3D visualizations, automated event routing, and robust dual-layer security.

---

## 🚀 Executive Summary & Scope
* **Platform Status:** Live for over 6 months, operating internationally.
* **Core Services:** Commercial enterprise recycling (corporate office collection, data center decommissioning, battery waste recycling, ESG & E-Waste Awareness) & Residential e-waste drives / CSR initiatives (including relief campaigns).
* **Development Approach:** Solo full-stack architecture, frontend/backend engineering, deployment, hosting, and domain management.
* **Philosophy:** **No-bloat design.** Heavy reliance on native WordPress core hooks and custom lightweight PHP/JavaScript instead of bloated third-party plugins, only the necessary plugins.

---

## 🛠️ Tech Stack & Architecture

| Layer | Technologies & Tools |
| :--- | :--- |
| **Core CMS** | WordPress, Hello Elementor (Parent Theme), Custom Fields, Custom Child Theme |
| **Backend & Logic** | PHP 8+, WordPress Hooks, API |
| **Frontend & UI** | Vanilla JavaScript (ES6+), CSS3, Elementor Pro (Layout Builder), Globe.gl (3D Data Visualization) |
| **Forms & Security** | Contact Form 7, Custom Client-Side Constraints, Server-Side PHP Validation Filters |

---

## 💎 Key Technical Achievements

### 1. Interactive 3D Globe.gl Integration
* Engineered a high-performance 3D interactive globe visualization representing international operations.
* Implemented via highly optimized inline script blocks utilizing `IntersectionObserver` and `ResizeObserver` to lazy-load rendering loops, eliminate layout jank, reduce CPU/GPU overhead, and ensure buttery-smooth mobile performance.

### 2. Automated Event Category Routing (`cron/init` logic)
* Built a custom automated workflow that queries event posts and checks their date metadata (`event_date`).
* Automatically switches post categories between `upcoming-events` and `past-events` dynamically based on the current date, streamlining content management without manual administrative overhead.

### 3. Native Metadata Extraction (Zero ACF Bloat)
* Developed a high-performance compiled meta engine (`pmw_get_compiled_event_meta`) that extracts native custom fields (`event_date`, `event_time`, `event_duration`, `event_society`, `event_location`) on-the-fly.
* Includes runtime date formatting (`YYYY-MM-DD` to `DD-MM-YY`) and semantic icon rendering injected into Elementor post grid widgets via output filters.

### 4. Dual-Layer Form Validation & Security
* **Client-Side:** Real-time JavaScript input constraints restricting telephone fields strictly to numeric values and name fields to letters/spaces, plus dynamic field conditional visibility (e.g., waste description toggles).
* **Server-Side:** Hardened PHP filters (`wpcf7_validate_*`) verifying input integrity on submission (e.g., exact 10-digit phone validation, 6-digit PIN code enforcement, and strict 14-day booking window constraints) to prevent bypasses via browser dev tools.

### 5. Custom Post Types (CPT) & Taxonomies
* Registered a minimalist, highly optimized custom post type for **Blogs** (`blog`) paired with a custom hierarchical taxonomy (`blog_category`) with full REST API and Gutenberg/Elementor support.

---

## 📁 Repository Structure

```text
├── functions.php                    # Core logic, enqueue scripts & hooks
├── header.php                       # Custom header override
├── footer.php                       # Custom footer override
├── style.css                        # All css
├── cf7-whatsapp-integration.js     # Form field secure logic
└── globe-integration.js            # 3D Globe visualization script
