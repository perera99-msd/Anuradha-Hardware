# Anuradha Hardware Store 🛠️

A complete e-commerce web application developed for a hardware store to manage online sales, inventory, and customer orders. The system features a responsive customer-facing storefront and a robust admin panel for store management.

## 🚀 Features

### Customer Side
* **User Authentication**: Secure Login and Registration system.
* **Product Browsing**: Browse hardware products by categories (Power Tools, Plumbing, Paints, etc.).
* **Shopping Cart**: Add items, adjust quantities, and view total costs.
* **Checkout System**: Streamlined process for placing orders.
* **Wishlist**: Save items for later.
* **User Account**: Manage profile and view order history.
* **Contact Form**: Direct messaging to store administration.

### Admin Panel
* **Dashboard**: Visual overview of recent orders, top products, and summary statistics.
* **Product Management**: Add, edit, and delete products with image uploads.
* **Order Management**: View and update order statuses (Pending, Processing, Completed).
* **Category Management**: Organize products into different sections.
* **Customer Management**: View registered user details.
* **Reports**: Generate sales reports and inventory summaries.

## 💻 Tech Stack

* **Frontend**: HTML5, CSS3, JavaScript
* **Backend**: PHP (Vanilla)
* **Database**: MySQL
* **Server**: Apache (XAMPP/WAMP or Live Hosting)

## 📂 Folder Structure

```text
Anuradha-Hardware/
├── Hardware/                 # Main User Website
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   ├── images/               # Asset images
│   ├── includes/             # Shared components (header, footer, db)
│   ├── uploads/              # Product images
│   ├── index.php             # Homepage
│   ├── products.php          # Product catalog
│   ├── cart.php              # Shopping cart
│   └── ...
├── Hardware/anuradha-admin/  # Admin Control Panel
│   ├── includes/             # Admin shared files
│   ├── uploads/              # Admin uploads
│   ├── dashboard.php         # Admin dashboard
│   ├── products.php          # Product CRUD
│   └── ...
└── if0_40855726_....sql      # Database Dump File