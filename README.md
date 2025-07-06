# ZedMemes - Meme Sharing Platform

A full-stack, single-page meme sharing platform built with PHP, MySQL, Bulma, jQuery, and AJAX.

---

## Table of Contents
- [Features](#features)
- [Project Structure](#project-structure)
- [Setup Instructions](#setup-instructions)
- [Database Schema](#database-schema)
- [How to Use](#how-to-use)
- [Design Notes](#design-notes)

---

## Features

- User registration and login (with password hashing)
- Meme upload (image file validation, only logged in users)
- Responsive meme feed grid (Bulma CSS)
- Like and Upvote reactions (one per user per post per type)
- Share (copy meme link) and Download functionality
- Persistent login via PHP sessions
- AJAX for all interactions (no page reloads)
- Secure input validation

---

## Project Structure

```
zedmemes/
│
├── assets/
│   └── memes/           # Uploaded meme images
├── css/
│   └── bulma.min.css    # Download from https://bulma.io/
├── js/
│   └── main.js
├── php/
│   ├── auth.php
│   ├── upload.php
│   ├── fetch_memes.php
│   └── react.php
├── index.php
├── README.txt
└── zedmemes.sql
```

---

## Setup Instructions

1. **Clone or copy this repository.**

2. **Download Bulma:**
   - Visit [https://bulma.io/](https://bulma.io/) and download the latest `bulma.min.css`.
   - Place it in `css/bulma.min.css`.

3. **Set up the database:**
   - Open **phpMyAdmin**.
   - Create a new database named `zedmemes`.
   - Import the SQL schema (`zedmemes.sql`) or run the following SQL:

```sql
-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Memes Table
CREATE TABLE memes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reactions Table
CREATE TABLE reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meme_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type ENUM('like', 'upvote') NOT NULL,
    reacted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

4. **Configure your PHP/MySQL details if needed:**
   - In `php/auth.php`, `php/upload.php`, `php/fetch_memes.php`, and `php/react.php`, set your MySQL username and password if different from default (`root`/blank).

5. **Set permissions:**
   - Make sure the `assets/memes/` directory is writable by the web server for meme uploads.

6. **Run the app:**
   - Place the project in your `htdocs` (XAMPP) or `www` (WAMP).
   - Access via `http://localhost/zedmemes/`.

---

## How to Use

- **Register** a new account or **log in**.
- **Upload** memes (image files only).
- **React** to memes with Like or Upvote (once per type per meme).
- **Share** memes with the copy link button.
- **Download** memes with the download button.
- **Log out** using the button in the navigation bar.

---

## Design Notes

- **SPA Approach**: All page actions are AJAX-driven for a seamless experience.
- **Frontend**: Bulma for layout/modals, jQuery for DOM/AJAX.
- **Backend**: PHP handles authentication, meme uploads, reactions, and meme feed.
- **Security**:
  - Passwords are securely hashed.
  - All user input is validated and sanitized.
  - Only images are allowed for upload.
  - Users must be logged in to upload or react.
- **Database**: All primary and foreign keys are indexed for performance.
- **No extra features** outside the assignment requirements. The code is clean and easy to extend.

---

## Contact

For questions or bug reports, please open an issue on the GitHub repository or contact Jacksonmofu2002.
