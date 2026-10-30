# THREADLINE Clothing Store

A responsive clothing-store demo made with HTML, CSS, JavaScript, PHP, and MySQL.

## Run with XAMPP
1. Copy the `threadline_clothing_store` folder to `C:\xampp\htdocs\threadline_clothing_store`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/threadline_clothing_store/`.
4. On the first visit, `db.php` creates the `threadline_db` database, its tables, and the sample products.
5. View the database at `http://localhost/phpmyadmin` and select `threadline_db`.

XAMPP does not automatically open phpMyAdmin when it starts. Open its URL in your browser after starting MySQL; the database is created when you first visit the store.

The default connection uses XAMPP's MySQL settings (`root` with an empty password). To override them, set `THREADLINE_DB_HOST`, `THREADLINE_DB_PORT`, `THREADLINE_DB_USER`, or `THREADLINE_DB_PASS` in the Apache/PHP environment before starting Apache.

If your MySQL `root` account has a password, add `THREADLINE_DB_PASS` to your Windows user environment variables and restart Apache. Do not put a real database password in public source code.

New customer registrations and customer sign-ins use MySQL. Passwords are saved as secure password hashes in the `users` table. Older demo accounts created before this change exist only in that browser's local storage; create the account again with the same email to save it to MySQL. Orders, reviews, and admin changes remain browser-local in this prototype.

## Features
- Clothing catalog with search and category filters
- Product detail view, size selection, shopping bag and stock checks
- Demo customer registration/sign-in
- Detailed demo checkout with contact details, a structured delivery address, delivery options, and an itemized order total (including shipping)
- Demo checkout that creates order numbers (`TL-1001`, etc.)
- Customer order tracking card with status milestones, estimated delivery, and address/item details
- Customer reviews (only for delivered orders)
- Dark admin dashboard with recent sales, order activity, weekly sales chart, top products, customers, and management views for inventory, orders, and reviews
- Admin product-photo uploads for new products and existing inventory items; photos are resized and shown in the storefront
- Seven supplied product photos shown across the starter catalog; replace or add photos from the admin Products page
- Browser-local persistence with `localStorage`

## Try the admin panel
1. Open the site in Chrome.
2. Click **Sign in**.
3. Use email `admin@threadline.test` and password `admin123`.
4. Open **Admin Panel** from the account drawer to view the dashboard and manage products, orders, customers, and reviews.
5. In **Products**, choose **Add photo** to attach an image to an existing item, or upload an optional photo while adding a product. Photos are resized and appear in the storefront. Edit stock/price or change an order's status. To test ratings, create a customer account, place an order, and set its status to **Delivered** in the admin panel.

## Important limitations
This is a front-end prototype for demonstration and learning. It is **not ready for a real public store**:
- Data is stored only in the current browser. It is not shared between customers/admins or devices.
- Product photos are stored in that browser's local storage too, so uploads are not shared across devices and can run into browser storage limits.
- Demo authentication is not secure; sample account passwords are stored in browser storage.
- Checkout does not charge payments, send emails, or create shipping-label integrations. Payment methods are demo selections only; never enter payment credentials.
- Order tracking and estimated delivery dates are demo estimates based on status changes entered in the admin panel, not live courier tracking.

Before launching for real customers, build a backend API and database (for example Node.js/Express + MySQL or PostgreSQL), use hashed passwords and server-side role permissions, validate stock/order transactions on the server, and integrate a trusted payment provider and courier service. Never use the demo credentials on a production site.

Demo shipping is ₱99 for standard delivery (free for orders of ₱2,500 or more) and ₱149 for express delivery. The tracking card estimates delivery five days after order placement. These are prototype values and are not connected to a courier.

## Publish
For a static preview, open `index.html` directly. GitHub Pages cannot run the PHP/MySQL setup; use a PHP-capable host for `index.php` and `db.php`.
