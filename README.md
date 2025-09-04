# 📚 WPBooks – Custom Books Manager for WordPress

WPBooks is a sample boilerplate plugin for managing custom entities (in this case: **Books**) in WordPress.  
It uses a custom database table for storing book records and demonstrates how to build a professional, scalable plugin with WordPress best practices.

---

## ✨ Features

- Creates a custom database table for storing book records.
- Full **CRUD** functionality (Create, Read, Update, Delete).
- Admin menu with pages for listing and adding/editing books.
- Book list displayed with WordPress native `WP_List_Table` class.
- Supports **pagination**, **bulk actions**, and **row actions**.
- Built with **OOP** structure following **PSR-4 autoloading**.
- Ready for extension into other custom entities.
- Includes a **shortcode** for displaying books on the frontend.
---

## 📂 Folder Structure
wp-books/
│
├── src/
│ ├── Database.php # Handles database operations
│ ├── Admin/
│ │ └── BookListTable.php # WP_List_Table implementation
│ │ └── Admin.php # Handles admin menu
│ ├── AjaxHandler.php # Handles ajax request
│ ├── Shortcode.php # Register and manage shortcode
│ └── Plugin.php # Core plugin loader
│
├── views/
│ ├── admin-list.php # Admin: List books
│ └── admin-add.php # Admin: Add/Edit book form
|
├── assets/
│ └── book-frontend.js # Frontend js file
│
├── wpbooks.php # Main plugin bootstrap file
└── README.md


---

## 🚀 Installation

1. Upload the `wpbooks` folder to the `/wp-content/plugins/` directory.
2. Go to **Plugins → Installed Plugins** in your WordPress dashboard.
3. Activate **WPBooks**.
4. A new **Books** menu will appear in your WordPress admin sidebar.

---

## 🛠 Usage

### Admin (Backend)
- Navigate to **Books → Add New** to add a new book.
- Go to **Books → All Books** to:
  - View all books
  - Edit or delete a single record
  - Bulk delete multiple records
  - Navigate with pagination

### Shortcode (Frontend)
You can display the list of books on any post or page using the shortcode:
[book_list]

---

## 👨‍💻 Developers

- **Author:** Amin Moghadas  
- **Experience:** Senior WordPress Developer with 12+ years of expertise  
- **Focus:** Custom plugin & theme development, scalable WordPress solutions

---

## 📜 License

This plugin is licensed under the **GPL v2 or later**.  
You are free to modify and redistribute it for personal or commercial use.

---

## 📝 Notes

This project is an **educational and extendable boilerplate**.  
You can adapt the same structure to manage any kind of custom entity (e.g., products, events, orders, etc.).
