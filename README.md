# 📦 Fast Media WordPress Shortcodes Repository

This repository contains the full collection of WordPress shortcode snippets used to power the Fast Media platform. These code snippets handle everything from content ingestion and user management to licensing, search, and UI behavior — modular, structured, and WPCode-ready.

---

## 🗂️ Folder Structure

Each folder below represents a major functionality area:

### 🔍 `/search/`
Universal and custom search features for images, collections, authors, and filtered results. Includes:
- Universal search bar & results
- Solwee-specific galleries (author, similar, collection)
- Detail views and pagination

### 📤 `/ingestion/`
Image ingestion workflows, including:
- IPTC/EXIF metadata extraction
- GCS upload handlers
- ACF conversion logic
- Manual metadata overrides

### 👥 `/membership/`
All membership and user/team logic:
- Team roles and dashboards
- Invite system
- Storage tracking
- Shared folders and access logic
- Solwee auto-login per user

### 🧾 `/purchase path/`
Full licensing and checkout flow:
- Solwee cart and licensing logic
- WooCommerce status tracking
- High-res download unlock
- Licensing banners and logic

### 🧠 `/projects/`
Project-level tools and views:
- Lightbox and folder managers
- Toggle view
- Project dashboards

### 🛠️ `/API/`
API integration tools:
- Solwee proxy endpoints
- API status checkers

### 🧩 `/HUB/`
Fast Media's internal dashboard stack:
- Brand approvals
- Asset uploads and metadata
- Snapshot tools
- Menu and layout tabs

> Subfolder: `/HUB/Admin/` – Includes all admin dashboard tools

### 🎨 `/UI/`
Interface enhancements:
- Elementor layout fixes
- Login label behavior
- Rotating headings
- All Solwee tickers and newsletter visuals

### 📄 `/pages/`
Standalone content pages:
- AI Learning
- Music feature page

### 💰 `/Pricing/`
Pricing calculator and logic blocks

---

## ✅ How to Use

Each file is a standalone shortcode following WPCode best practices.

To deploy:
1. Copy the contents of any `.php` file
2. Paste into a WPCode snippet (Auto Insert → Run Everywhere)
3. Optionally wrap with a `[shortcode]` if needed for use in posts, pages, or Elementor

You can also group files into mu-plugins or load via GitHub sync (see future roadmap).

---

## 🧠 Best Practices

- All code is additive and modular — avoid rewriting working logic
- Use `// 🔒 LOCKED BASELINE` headers for confirmed, do-not-touch versions
- Commit messages should reflect logical naming and stack
- Do not rename files unless function scope changes

---

## 🧑‍💻 Author

Fast Media © 2025  
Developed and maintained by Marco Oonk

---

## 🔒 Private Repository Notice

This repo is private and internal to the Fast Media development workflow. Do not share or clone externally without explicit permission.

