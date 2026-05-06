<div align="center">

# 🗳️ Online Voting System

### *Final Year Graduation Project*

> A secure, full-stack digital election platform — built to demonstrate real-world web development skills across authentication, role-based access control, database design, and analytics.

[![Live Demo](https://img.shields.io/badge/🌐%20Live%20Demo-Visit%20Site-0A66C2?style=for-the-badge)](https://voting-system.ct.ws/)

</div>

---

## 📌 Overview

The **Online Voting System** is a secure, web-based election management platform built with PHP and MySQL. It allows registered voters to cast ballots digitally while giving administrators complete control over candidates, users, and election analytics.

Key design goals:
- **One vote per user** — enforced at both application and database level
- **Role-based access** — separate interfaces for voters and administrators
- **Real-time results** — live vote counts with election analytics
- **Image support** — candidate and voter profile photo uploads

**🔗 Live:** [https://voting-system.ct.ws/](https://voting-system.ct.ws/)

| Role | Username | Password |
|------|----------|----------|
| 👑 Admin | `admin@123` | `Admin@654321` |
| 🗳️ Voter | `jyoti` | `Jyoti@461` |

> ⚠️ **Note:** These are demo credentials for evaluation purposes only. Rotate all credentials before any production deployment.

---

## ✨ Features

### 🗳️ Voter Portal
- Secure registration and login
- Update profile details and upload a profile photo
- Cast a vote — strictly enforced once per registered user
- View live voting results after voting

### 🛠️ Admin Dashboard
- Add, edit, and delete election candidates (with photo upload)
- Manage registered voter accounts
- View all contact form submissions
- Election analytics dashboard with vote summaries and snapshots

### 🖼️ Image Uploads
- Candidate photos → `uploads/candidates/`
- Voter profile photos → `uploads/profiles/`

---

## 🧰 Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, JavaScript |
| **Backend** | PHP with PDO (prepared statements) |
| **Database** | MySQL 5.7+ |
| **Server** | Apache via XAMPP |
| **Auth** | PHP Sessions |

---

## 📁 Project Structure

```
voting-system/
│
├── admin/                    # Admin-only pages and logic
├── pages/                    # Voter-facing pages
├── config/
│   └── db.php                # Database connection (PDO)
├── uploads/
│   ├── candidates/           # Candidate profile images
│   └── profiles/             # Voter profile images
├── assets/
│   └── default-profile.jpg   # Fallback avatar image
└── index.php                 # Application entry point
```

---

## ⚙️ Local Setup Guide

### Prerequisites

| Tool | Purpose | Download |
|------|---------|----------|
| VS Code | Code editor | [Download](https://code.visualstudio.com/) |
| XAMPP | Apache + MySQL local server | [Download](https://www.apachefriends.org/) |
| phpMyAdmin | Database GUI (bundled with XAMPP) | — |

---

### Step 1 — Start XAMPP

Open the XAMPP Control Panel and enable:

- ✅ Apache
- ✅ MySQL

---

### Step 2 — Place the Project

Copy the project folder into the XAMPP web root:

```
C:\xampp\htdocs\voting-system\
```

---

### Step 3 — Create the Database

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **New** and create a database named `voting_system`

---

### Step 4 — Import the SQL Schema

Select the `voting_system` database → click the **SQL** tab → paste and run the schema below:

<details>
<summary>📄 Click to view full SQL Schema</summary>

```sql
-- ============================================================
-- Online Voting System — Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS `voting_system`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `voting_system`;

-- Users (voters + admins)
CREATE TABLE `users` (
  `id`            INT(11)                       NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)                   NOT NULL,
  `password`      VARCHAR(255)                  NOT NULL,
  `email`         VARCHAR(100)                  NOT NULL,
  `mobile`        VARCHAR(10)                   DEFAULT NULL,
  `gender`        ENUM('Male','Female','Other') DEFAULT NULL,
  `dob`           DATE                          DEFAULT NULL,
  `nationality`   VARCHAR(22)                   DEFAULT NULL,
  `profile_photo` VARCHAR(255)                  DEFAULT NULL,
  `description`   VARCHAR(255)                  NOT NULL,
  `has_voted`     TINYINT(1)                    DEFAULT 0,
  `is_admin_dba`  TINYINT(1)                    DEFAULT 0,
  `created_at`    TIMESTAMP                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email`    (`email`),
  UNIQUE KEY `uq_mobile`   (`mobile`)
);

-- Candidates
CREATE TABLE `candidates` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `party`      VARCHAR(100) NOT NULL,
  `bio`        TEXT         NOT NULL,
  `photo_url`  VARCHAR(255) DEFAULT NULL,
  `votes`      INT(11)      DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- Contact form submissions
CREATE TABLE `contacts` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100) NOT NULL,
  `email`        VARCHAR(100) NOT NULL,
  `mobile`       VARCHAR(20)  NOT NULL,
  `description`  TEXT         NOT NULL,
  `submitted_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- Election snapshots / analytics
CREATE TABLE `elections` (
  `id`               INT(11)   NOT NULL AUTO_INCREMENT,
  `election_date`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_votes`      INT(11)   NOT NULL,
  `total_candidates` INT(11)   NOT NULL,
  `total_voters`     INT(11)   NOT NULL,
  `snapshot_data`    LONGTEXT,
  PRIMARY KEY (`id`)
);

-- Vote records (one per user, enforced via FK + application logic)
CREATE TABLE `votes` (
  `id`           INT(11)   NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)   NOT NULL,
  `candidate_id` INT(11)   NOT NULL,
  `voted_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user`      (`user_id`),
  KEY `fk_candidate` (`candidate_id`),
  CONSTRAINT `votes_ibfk_1`
    FOREIGN KEY (`user_id`)      REFERENCES `users`      (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_ibfk_2`
    FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
);
```

</details>

---

### Step 5 — Configure Database Connection

Edit `config/db.php`:

```php
<?php
$host   = 'localhost';
$dbname = 'voting_system';
$user   = 'root';
$pass   = '';  // Update if you have set a MySQL root password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

---

### Step 6 — Create Upload Directories

Make sure these folders exist in the project root:

```
uploads/
├── candidates/
└── profiles/
```

Add a fallback avatar at:

```
assets/default-profile.jpg
```

---

### Step 7 — Launch the App

Open your browser and go to:

```
http://localhost/voting-system/
```

**Flow:** Register → Log In → Cast Vote → View Results

---

## 🔐 Security Notes

- Passwords must be hashed using `password_hash()` and verified with `password_verify()`
- All queries use **PDO prepared statements** to prevent SQL injection
- Voting integrity is enforced via the `has_voted` flag on the `users` table
- Admin access is gated by `is_admin_dba` — never expose this via the public registration form

> Before going live: enable HTTPS, restrict phpMyAdmin access, and rotate all default credentials.

---

## 🤝 Contributing

Contributions are welcome! To get started:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m "feat: describe your change"`
4. Push and open a Pull Request

Please open an issue first to discuss major changes.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE) — free to use, modify, and distribute with attribution.

---

<div align="center">

**Built with ❤️ as a Final Year Graduation Project**

*Demonstrating full-stack development with PHP · MySQL · Apache*

</div>
