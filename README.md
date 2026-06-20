#  KUET Old & New Used Product Sale and Inventory Management System

A campus-oriented marketplace designed specifically for the students of KUET. This system allows students to safely and securely buy, sell, and bid on used and new academic materials, electronics, and personal items rather than in facebook market place.

## Used 

* **Backend Framework:** Laravel (PHP)
* **Database System:** Oracle 11g Express Edition
* **Database Driver:** Oracle Instant Client & PHP OCI8 (`yajra/laravel-oci8`)
* **Frontend:** Laravel Blade, Bootstrap/Tailwind CSS 

## Core Features

### 1. Exclusive KUET Student Verification
Security and trust are paramount. The system enforces strict domain-level verification. During Sign-Up and Sign-In, user emails are validated at both the application and database level to ensure they contain `@stud.kuet.ac.bd`. 

### 2. Smart Inventory & Product Management
Sellers can easily list products as either `NEW` or `OLD` with a minimum proposed base price. Products are organized into structured categories to keep the marketplace clean:
* Books | Laptops | Mobiles | Electronics | Furniture | Cycles | Calculators

### 3. Dynamic Bargaining (Bidding) System
Buyers don't just add to a cart; they negotiate. Buyers can place customized bids on available items. Sellers view a sorted list of all active bids (highest to lowest) and can choose to `ACCEPT` a specific offer. Accepting a bid automatically changes the status of all other competing bids to `REJECTED`.

### 4. Automated & Manual Wishlist Fallback
* **Manual:** Users can save available items to their personal wishlist for later viewing.
* **Automated:** If a seller accepts a bid and a product is marked as `SOLD`, the system automatically transfers the product into the wishlist of all other buyers who had pending bids, ensuring they can track the unavailability of items they were interested in.

### 5. Secure Transactions
Once a bid is accepted, a locked transaction record is generated, sealing the `final_price` and linking the specific buyer, seller, and product. A product can only be successfully transacted once (1-to-1 relationship).

### 6. Trusted Reputation & Rating System
To maintain a high-quality community, buyers and sellers can rate each other (1 to 5 stars) after a successful transaction. The system dynamically calculates average ratings. **Note:** Self-rating is strictly prohibited and blocked at the database constraint level.

### 7. Mutual Reporting System
A secure channel to prevent fraud. Users can file reports against suspicious buyers, sellers, or fake product listings. 

### 8. Advanced Search, Filter, & Sort
The central dashboard utilizes highly optimized Oracle SQL Views to deliver fast data retrieval. Users can filter by category, sort by price or condition, and search directly by product title.

## Database Architecture

This project strictly for **Oracle 11g Express Edition**:
* **Sequences & Triggers:** Utilized for Primary Key generation in place of modern auto-increment features.
* **CHECK Constraints:** Heavy use of database-level constraints to enforce business logic (e.g., verifying KUET emails, preventing self-rating, locking rating values between 1-5).
