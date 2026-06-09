# MET MUSEUM WEBSITE DML DATABASE RECONSTRUCTION REPORT

This document represents a comprehensive Data Manipulation Language (DML) reverse-engineering of the entire **MET Museum Ticketing & Collections Management application** codebase. It maps out each high-level feature, details its end-to-end system flow (Route -> Controller -> Service -> Model -> Database), and maps out the exact reconstructed SQL DML queries that are executed against the relational engine under the hood.

---

## 1. PUBLIC COLLECTIONS SEARCH & CATALOG

### FEATURE: Public Catalog Indexing & Faceted Advanced Search
* **Purpose**: Allows public visitors and users to browse, search, filter, and sort through the museum's catalog of artworks across thousands of master items, curatorial departments, classifications, types, and materials.
* **Feature Flow**:
  * `Route::get('/art', [ArtController::class, 'index'])` / `Route::get('/art/search', [ArtController::class, 'search'])`
  * `ArtController@index` / `ArtController@search`
  * Eloquent query builder filters target parameters (`department_id`, `classification_id`, `type_id`, `search` text).
  * `ArtWork` model with eager loaded associations is queried, paginated, and returned to the frontend.
* **DML Queries Used**:
  * **SELECT**:
    ```sql
    -- Fetch paginated artworks with text search and department filtering
    SELECT * FROM art_works 
    WHERE (title LIKE '%rembrandt%' OR accession_number LIKE '%rembrandt%' OR description LIKE '%rembrandt%')
      AND department_id = 12 
    ORDER BY art_work_id DESC 
    LIMIT 20 OFFSET 0;
    
    -- Eager load Department relation
    SELECT * FROM departments WHERE department_id IN (12, 15, 18);
    
    -- Eager load Images relation
    SELECT * FROM art_work_images WHERE art_work_id IN (101, 102, 103, 104);
    
    -- Eager load Constituents (Artists) relation via Pivot
    SELECT constituents.*, art_work_constituents.art_work_id AS pivot_art_work_id 
    FROM constituents 
    INNER JOIN art_work_constituents ON constituents.constituent_id = art_work_constituents.constituent_id 
    WHERE art_work_constituents.art_work_id IN (101, 102, 103, 104);
    
    -- Fetch dropdown list of classifications for filters
    SELECT * FROM classifications ORDER BY classification_name ASC;
    ```
  * **JOIN**:
    ```sql
    -- Joining with constituents in deep search queries
    SELECT art_works.* FROM art_works
    LEFT JOIN art_work_constituents ON art_works.art_work_id = art_work_constituents.art_work_id
    LEFT JOIN constituents ON art_work_constituents.constituent_id = constituents.constituent_id
    WHERE constituents.display_name LIKE '%Van Gogh%';
    ```
  * **LIMIT / OFFSET**:
    * Utilized implicitly by the Eloquent pagination pipeline: `LIMIT 20 OFFSET 20` (Page 2).
  * **ORDER BY**:
    * Dynamically controlled via request query parameters: `ORDER BY title ASC` or `ORDER BY accession_year DESC`.

### FEATURE: Curatorial Recommendation Engine
* **Purpose**: Generates random collections of artworks within collection pages to keep the UI engaging.
* **Feature Flow**:
  * `ArtWorkController@show`
  * Calls `inRandomOrder()->limit(8)` relation lookup on artworks to recommend other exhibits.
* **DML Queries Used**:
  * **SELECT**:
    ```sql
    -- Fetch randomized collection recommendations
    SELECT * FROM art_works 
    ORDER BY RANDOM() 
    LIMIT 8;
    ```

---

## 2. USER IDENTITY & GUEST AUTHENTICATION

### FEATURE: Secure Registration & Transactional Relational Account Creation
* **Purpose**: Ensures that when a user registers, their postal code verification, login account, and customer profile are created in a strict atomic sequence. If any step fails, the entire stack is rolled back.
* **Feature Flow**:
  * `Route::post('/account/register', [RegisterController::class, 'store'])`
  * `RegisterController@store`
  * Calls `DB::transaction` to wrap `PostalCode::firstOrCreate`, `User::create`, and `UserProfile::create`.
* **DML Queries Used**:
  * **TRANSACTION**:
    * Initiates `BEGIN TRANSACTION` and finishes with `COMMIT` (or `ROLLBACK` on error).
  * **SELECT**:
    ```sql
    -- Check if postal code is already in database
    SELECT * FROM postal_codes WHERE postal_code = '10028' LIMIT 1;
    ```
  * **INSERT**:
    ```sql
    -- Insert postal code if it did not exist (firstOrCreate)
    INSERT INTO postal_codes (postal_code, city, state, country, created_at, updated_at) 
    VALUES ('10028', 'New York', 'NY', 'United States', '2026-05-19 10:00:00', '2026-05-19 10:00:00');
    
    -- Insert new login account
    INSERT INTO users (email, password, is_admin, created_at, updated_at) 
    VALUES ('user@example.com', '$2y$10$abcdef...', 0, '2026-05-19 10:00:00', '2026-05-19 10:00:00');
    
    -- Insert user profile referencing new user_id
    INSERT INTO user_profiles (user_id, first_name, last_name, phone, street_address, postal_code, created_at, updated_at) 
    VALUES (45, 'John', 'Doe', '555-1234', '1000 5th Ave', '10028', '2026-05-19 10:00:00', '2026-05-19 10:00:00');
    ```

### FEATURE: Guest Identity Management & UPSERT Flow
* **Purpose**: Captures guest details during ticket checkout without forcing them to register, establishing or updating a profile in a single operational step.
* **Feature Flow**:
  * `GuestCheckoutController@store`
  * Calls `Guest::updateOrCreate(['email' => $email], $guestDetails)`
* **DML Queries Used**:
  * **SELECT**:
    ```sql
    -- Look for prior guest record with the same email
    SELECT * FROM guests WHERE email = 'guest@example.com' LIMIT 1;
    ```
  * **UPSERT (Emulated in Laravel)**:
    ```sql
    -- If guest exists:
    UPDATE guests 
    SET name = 'Jane Smith', phone = '555-9876', updated_at = '2026-05-19 10:05:00' 
    WHERE guest_id = 12;
    
    -- If guest does not exist:
    INSERT INTO guests (email, name, phone, created_at, updated_at) 
    VALUES ('guest@example.com', 'Jane Smith', '555-9876', '2026-05-19 10:05:00', '2026-05-19 10:05:00');
    ```

---

## 3. RELATIONAL SHOPPING CART & SESSION MIGRATION

### FEATURE: Guest-to-User Shopping Cart Relational Migration
* **Purpose**: Migrates a session-backed anonymous shopping cart into physical database tables once a guest registers, logs in, or identifies themselves during checkout.
* **Feature Flow**:
  * `CartController::migrateSessionCartToDb` (invoked during authentication hooks)
  * System locks database operations, deletes any existing stale DB carts, and inserts session elements back into structured tables.
* **DML Queries Used**:
  * **TRANSACTION**:
    * Runs the entire migration inside a transactional block to ensure all cart items transfer safely.
  * **SELECT**:
    ```sql
    -- Fetch active cart matching user ID to clean up legacy sessions
    SELECT * FROM carts WHERE user_id = 45 LIMIT 1;
    ```
  * **DELETE**:
    ```sql
    -- Delete prior DB cart lines to avoid duplicates before importing session
    DELETE FROM carts WHERE user_id = 45;
    ```
  * **INSERT (Bulk Insert emulation)**:
    ```sql
    -- Create new Cart header
    INSERT INTO carts (user_id, guest_id, expires_at, created_at, updated_at) 
    VALUES (45, NULL, '2026-05-19 11:00:00', '2026-05-19 10:00:00', '2026-05-19 10:00:00');
    
    -- Create new Cart group
    INSERT INTO cart_groups (cart_id, created_at, updated_at) VALUES (15, '2026-05-19 10:00:00', '2026-05-19 10:00:00');
    
    -- Insert all cart items from session state (Iterated inserts)
    INSERT INTO cart_items (cart_group_id, ticket_availability_id, quantity, created_at, updated_at) 
    VALUES (8, 4, 2, '2026-05-19 10:00:00', '2026-05-19 10:00:00');
    
    INSERT INTO cart_items (cart_group_id, ticket_availability_id, quantity, created_at, updated_at) 
    VALUES (8, 5, 1, '2026-05-19 10:00:00', '2026-05-19 10:00:00');
    ```

---

## 4. CHECKOUT TICKETING & PAYMENTS

### FEATURE: Pessimistic Locked Cart Checkout & Overbooking Prevention
* **Purpose**: Locks the shopping cart and ticket availability states during payment generation to ensure capacities are validated in real-time, preventing concurrent overbooking.
* **Feature Flow**:
  * `Route::post('/tickets/checkout', [CheckoutController::class, 'checkout'])`
  * `CheckoutController@checkout`
  * Starts a database transaction.
  * Locks the active cart database record via `$cart->lockForUpdate()`.
  * Checks ticket capacity limit vs already sold tickets.
  * Creates an `Order` and a pending `Payment`.
* **DML Queries Used**:
  * **TRANSACTION**:
    * Strict isolation wrapper (`DB::beginTransaction` / `DB::commit`).
  * **SELECT with PESSIMISTIC LOCK**:
    ```sql
    -- SELECT and LOCK the active cart record (preventing other threads from mutating it)
    SELECT * FROM carts WHERE cart_id = 15 FOR UPDATE;
    
    -- Count already sold tickets to verify capacity limits
    SELECT COUNT(*) AS aggregate FROM tickets 
    WHERE ticket_availability_id = 4 AND status != 'cancelled';
    ```
  * **INSERT**:
    ```sql
    -- Create transaction order
    INSERT INTO orders (order_code, user_id, guest_id, order_date, expired_at, total_amount, order_status, created_at, updated_at) 
    VALUES ('5c7d8b1e-...', 45, NULL, '2026-05-19 10:10:00', '2026-05-19 10:30:00', 45.00, 'pending_payment', '2026-05-19 10:10:00', '2026-05-19 10:10:00');
    
    -- Create payment transaction reference
    INSERT INTO payments (order_id, payment_method, amount, payment_status, created_at, updated_at) 
    VALUES (23, 'Credit Card', 45.00, 'Pending', '2026-05-19 10:10:00', '2026-05-19 10:10:00');
    ```

### FEATURE: Payment Settlement, Cart Deletion, & Secure Ticket Generation
* **Purpose**: Confirms order completion, marks order status as paid, generates secure UUID tickets, and clears the user's shopping cart permanently.
* **Feature Flow**:
  * `CheckoutController@pay`
  * Loads order, validates transaction status inside `DB::transaction`.
  * Locks payment record (`Payment::where('order_id', ...)->lockForUpdate()->first()`).
  * Updates payment status to 'Paid' and order status to 'paid'.
  * Generates separate `Ticket` records with secure UUID qr_codes.
  * Deletes shopping cart items and shopping cart headers.
* **DML Queries Used**:
  * **SELECT / LOCK**:
    ```sql
    SELECT * FROM payments WHERE order_id = 23 FOR UPDATE;
    ```
  * **UPDATE**:
    ```sql
    -- Update payment settlement details
    UPDATE payments SET payment_status = 'Paid', paid_at = '2026-05-19 10:12:00', updated_at = '2026-05-19 10:12:00' WHERE payment_id = 14;
    
    -- Update order state to paid
    UPDATE orders SET order_status = 'paid', updated_at = '2026-05-19 10:12:00' WHERE order_id = 23;
    ```
  * **INSERT**:
    ```sql
    -- Generate distinct tickets (UUID token-based)
    INSERT INTO tickets (order_id, ticket_availability_id, qr_code, status, created_at, updated_at) 
    VALUES (23, 4, '9d8f7e6d-5c4b-3a21...', 'valid', '2026-05-19 10:12:00', '2026-05-19 10:12:00');
    
    INSERT INTO tickets (order_id, ticket_availability_id, qr_code, status, created_at, updated_at) 
    VALUES (23, 4, '1a2b3c4d-5e6f-7a8b...', 'valid', '2026-05-19 10:12:00', '2026-05-19 10:12:00');
    ```
  * **DELETE**:
    ```sql
    -- Delete cart items
    DELETE FROM cart_items WHERE cart_group_id = 8;
    
    -- Delete cart groups
    DELETE FROM cart_groups WHERE cart_id = 15;
    
    -- Delete cart header
    DELETE FROM carts WHERE cart_id = 15;
    ```

### FEATURE: Cashier POS Instant Transaction Processing
* **Purpose**: Point-of-Sale interface allowing physical cashiers to generate and print onsite physical tickets instantly.
* **Feature Flow**:
  * `Route::post('/admin/tickets/checkout', [TicketController::class, 'checkout'])`
  * `Admin/TicketController@checkout`
  * Inside transaction: Validates capacities, checks disability companion limitations.
  * Creates paid `Order` and `Payment` (Cash method) immediately.
  * Generates tickets instantly.
* **DML Queries Used**:
  * **SELECT**:
    ```sql
    -- Companion validation check
    SELECT * FROM ticket_types WHERE LOWER(ticket_type_name) = 'companion' LIMIT 1;
    ```
  * **INSERT**:
    ```sql
    -- Create Order marked paid immediately
    INSERT INTO orders (order_code, user_id, order_date, expired_at, total_amount, order_status) 
    VALUES ('f83d712e-...', 1, '2026-05-19 10:15:00', '2026-05-19 10:45:00', 30.00, 'paid');
    
    -- Create Paid Cash Payment
    INSERT INTO payments (order_id, payment_method, amount, payment_status, paid_at) 
    VALUES (24, 'Cash', 30.00, 'Paid', '2026-05-19 10:15:00');
    ```

### FEATURE: Order Expiration and Capacity Reclaim System
* **Purpose**: Identifies pending orders that failed to pay within the 20-30 minute threshold, canceling them securely and freeing allocated stock back to the public pool.
* **Feature Flow**:
  * Triggered by `CheckoutController@expireOrderIfTimedOut` during checkout pages reload.
  * Checks orders expiration threshold.
  * Changes order status to 'expired' and marks payment to 'Failed'.
* **DML Queries Used**:
  * **UPDATE**:
    ```sql
    -- Expire outdated payments
    UPDATE payments SET payment_status = 'Failed' WHERE order_id = 23;
    
    -- Cancel outdated orders
    UPDATE orders SET order_status = 'expired', expired_at = '2026-05-19 10:30:00' WHERE order_id = 23;
    ```

---

## 5. MEMBERSHIP CLAIMS & LIFECYCLES

### FEATURE: Membership Purchases & Renewals
* **Purpose**: Manages membership subscriptions and handles dynamic updates to duration blocks.
* **Feature Flow**:
  * `MembershipController@purchase`
  * Creates an Order and Payment matching the selected membership plan (Individual, Family, Patron).
  * Paid memberships are activated via `MembershipService@activateMembership`.
* **DML Queries Used**:
  * **INSERT**:
    ```sql
    -- Create order for membership catalog plan
    INSERT INTO orders (order_code, user_id, order_date, expired_at, total_amount) 
    VALUES ('8c7a6b5d-...', 45, '2026-05-19 10:20:00', '2026-05-19 10:40:00', 199.00);
    ```
  * **UPDATE**:
    ```sql
    -- Activate/extend membership dates
    UPDATE memberships 
    SET membership_status = 'active', start_date = '2026-05-19 00:00:00', end_date = '2027-05-19 23:59:59' 
    WHERE membership_id = 3;
    ```

### FEATURE: Gift Membership Claims & Extension Concurrency
* **Purpose**: Allows users to claim gift tokens, verifying token states and extending months while locking rows to prevent double-claiming.
* **Feature Flow**:
  * `MembershipService@claimGiftMembership`
  * Locks existing membership for update to securely modify active duration dates.
* **DML Queries Used**:
  * **SELECT with LOCK**:
    ```sql
    -- Fetch target membership with strict updates locking
    SELECT * FROM memberships WHERE user_id = 45 AND membership_status = 'active' FOR UPDATE;
    ```
  * **UPDATE**:
    ```sql
    -- Extend expiration end date by 1 year (12 months extension)
    UPDATE memberships 
    SET end_date = DATE_ADD(end_date, INTERVAL 12 MONTH), updated_at = '2026-05-19 10:22:00' 
    WHERE membership_id = 3;
    ```

### FEATURE: Membership Expiration Sweeper
* **Purpose**: Sweeps active users memberships during authentication to ensure profiles drop back to standard tier if expired.
* **Feature Flow**:
  * `LoginController@login` -> invokes `MembershipService@expireMembershipsForUser`
  * Scans active memberships that have passed the `end_date` threshold.
  * Updates statuses to 'expired' and adjusts user roles dynamically.
* **DML Queries Used**:
  * **UPDATE**:
    ```sql
    -- Expire memberships that have passed the date threshold
    UPDATE memberships 
    SET membership_status = 'expired', updated_at = '2026-05-19 09:00:00' 
    WHERE user_id = 45 AND end_date < '2026-05-19 09:00:00';
    ```

---

## 6. ADMIN DASHBOARD ANALYTICS

### FEATURE: Real-time Live Operations Dashboard Metrics
* **Purpose**: Compiles structural stats across all modules (artwork catalog, user memberships, ticket sales, cash flow) to present operational statuses instantly.
* **Feature Flow**:
  * `Admin/DashboardController@index`
  * Aggregates statistics through optimized DML selections.
* **DML Queries Used**:
  * **SELECT with AGGREGATE**:
    ```sql
    -- Tickets sold today
    SELECT SUM(order_details.quantity) AS sum_quantity 
    FROM order_details 
    INNER JOIN orders ON order_details.order_id = orders.order_id 
    WHERE orders.created_at BETWEEN '2026-05-19 00:00:00' AND '2026-05-19 23:59:59';
    
    -- Cash revenue generated today
    SELECT SUM(total_amount) AS sum_revenue FROM orders 
    WHERE created_at BETWEEN '2026-05-19 00:00:00' AND '2026-05-19 23:59:59' AND order_status = 'completed';
    
    -- Count operations
    SELECT COUNT(*) AS aggregate FROM orders WHERE status = 'pending';
    SELECT COUNT(*) AS aggregate FROM payments WHERE payment_status = 'pending';
    SELECT COUNT(*) AS aggregate FROM users;
    SELECT COUNT(*) AS aggregate FROM art_works;
    ```

### FEATURE: Ticket Sales Analytics & Visitor Insights
* **Purpose**: Evaluates daily cash flow, unique visitor calculations, monthly conversions, ticket sales trends, occupancy metrics, and validation statistics.
* **Feature Flow**:
  * `Admin/TicketAnalyticsController@index`
  * Aggregates statistics through complex DML selections.
* **DML Queries Used**:
  * **SELECT with deep AGGREGATE**:
    ```sql
    -- Total Unique Visitors (Guest + Registered) from tickets sold
    SELECT COUNT(DISTINCT CASE 
        WHEN orders.user_id IS NOT NULL THEN orders.user_id 
        WHEN orders.guest_id IS NOT NULL THEN orders.guest_id 
        END) AS total 
    FROM orders 
    INNER JOIN tickets ON orders.order_id = tickets.order_id 
    WHERE tickets.status != 'cancelled' AND tickets.created_at BETWEEN '2026-04-19 00:00:00' AND '2026-05-19 23:59:59';
    
    -- Daily sales trend aggregation for chart
    SELECT DATE(created_at) AS date_group, SUM(amount) AS total_amount 
    FROM payments 
    WHERE payment_status = 'Paid' AND created_at BETWEEN '2026-04-19 00:00:00' AND '2026-05-19 23:59:59' 
    GROUP BY DATE(created_at);
    
    -- Repeat Visitors (having more than 1 order)
    SELECT COUNT(*) FROM (
        SELECT CASE 
            WHEN orders.user_id IS NOT NULL THEN CONCAT('user_', orders.user_id) 
            WHEN orders.guest_id IS NOT NULL THEN CONCAT('guest_', orders.guest_id) 
            END AS visitor_key, 
            COUNT(DISTINCT orders.order_id) AS order_count 
        FROM orders 
        INNER JOIN tickets ON orders.order_id = tickets.order_id 
        WHERE tickets.created_at BETWEEN '2026-04-19 00:00:00' AND '2026-05-19 23:59:59' 
        GROUP BY visitor_key 
        HAVING COUNT(DISTINCT orders.order_id) > 1
    ) AS repeat_visitor_subquery;
    
    -- Guest vs Registered breakdown
    SELECT CASE WHEN orders.user_id IS NOT NULL THEN 'registered' ELSE 'guest' END AS type, 
           COUNT(DISTINCT CASE WHEN orders.user_id IS NOT NULL THEN orders.user_id WHEN orders.guest_id IS NOT NULL THEN orders.guest_id END) AS count 
    FROM orders 
    INNER JOIN tickets ON orders.order_id = tickets.order_id 
    WHERE tickets.created_at BETWEEN '2026-04-19 00:00:00' AND '2026-05-19 23:59:59' AND tickets.status != 'cancelled' 
    GROUP BY type;
    ```

---

## 7. MASTER DATA CRUD & SMART RELATION AUTOMATION

### FEATURE: Dynamic Inline Master Records Creation
* **Purpose**: Automatically resolves new metadata terms (new classifications, cultures, materials, tags, etc.) input during artwork additions/updates, inserting records on-the-fly and assigning new IDs dynamically.
* **Feature Flow**:
  * `Admin/ArtworkController@store` -> invokes `resolveInlineMasterRecords`
  * Matches tags, classifications, object types, and departments using case-insensitive parameters.
  * If a term does not exist, it inserts it instantly and updates validation arrays dynamically.
* **DML Queries Used**:
  * **SELECT**:
    ```sql
    -- Check if classification exists (case-insensitive lookup)
    SELECT * FROM classifications WHERE LOWER(classification_name) = 'oil painting' LIMIT 1;
    ```
  * **INSERT**:
    ```sql
    -- Create new term dynamically if it did not exist
    INSERT INTO classifications (classification_name, created_at, updated_at) 
    VALUES ('Oil Painting', '2026-05-19 10:40:00', '2026-05-19 10:40:00');
    ```

### FEATURE: Artworks Many-to-Many Pivot Syncing
* **Purpose**: Maps and synchronizes highly relational attributes (e.g. materials, mediums, tags, classifications, geographies) during artwork creation or update, synchronizing pivot tables securely.
* **Feature Flow**:
  * `Admin/ArtworkController@store` / `update` -> invokes `syncM2MRelationships`
  * Syncs arrays of IDs for `materials`, `mediums`, `tags`, etc.
* **DML Queries Used**:
  * **PIVOT SYNC (Laravel's sync() operations)**:
    ```sql
    -- 1. Detach records that were removed from selection
    DELETE FROM art_work_materials 
    WHERE art_work_id = 101 AND material_id NOT IN (3, 5, 8);
    
    -- 2. Identify already attached records to avoid duplicate inserts
    SELECT material_id FROM art_work_materials WHERE art_work_id = 101;
    
    -- 3. Insert newly attached links
    INSERT INTO art_work_materials (art_work_id, material_id) VALUES (101, 3);
    INSERT INTO art_work_materials (art_work_id, material_id) VALUES (101, 8);
    ```

### FEATURE: Artworks Deletion Cascades Detaching
* **Purpose**: Cleans up intermediate pivot files when an artwork is deleted to prevent orphan references and foreign key constraint errors.
* **Feature Flow**:
  * `Admin/ArtworkController@destroy`
  * Detaches all relationships from pivot models manually before deleting the primary model instance.
* **DML Queries Used**:
  * **DELETE**:
    ```sql
    -- Clear pivot references
    DELETE FROM art_work_materials WHERE art_work_id = 101;
    DELETE FROM art_work_mediums WHERE art_work_id = 101;
    DELETE FROM art_work_tags WHERE art_work_id = 101;
    DELETE FROM art_work_constituents WHERE art_work_id = 101;
    DELETE FROM art_work_cultures WHERE art_work_id = 101;
    DELETE FROM art_work_periods WHERE art_work_id = 101;
    DELETE FROM art_work_dynasties WHERE art_work_id = 101;
    DELETE FROM art_work_reigns WHERE art_work_id = 101;
    DELETE FROM art_work_portfolios WHERE art_work_id = 101;
    
    -- Clear physical child records
    DELETE FROM art_work_images WHERE art_work_id = 101;
    
    -- Delete primary artwork record
    DELETE FROM art_works WHERE art_work_id = 101;
    ```

---

## SUMMARY OF DML STATEMENTS

| DML Category | Key Tables Targeted | Primary Operations / Functions |
|---|---|---|
| **SELECT** | `art_works`, `users`, `orders`, `tickets`, `payments`, `memberships`, `constituents`, `postal_codes`, `guests` | Faceted search filters, unique constraints checks, pessimistic locks checks, session status lookups, validation checks |
| **INSERT** | `orders`, `payments`, `tickets`, `users`, `user_profiles`, `postal_codes`, `guests`, pivot tables | Transactional order processing, new account creation, companion auto-fallback generation, onsite POS checkouts, dynamic inline master terms creation |
| **UPDATE** | `orders`, `payments`, `tickets`, `memberships`, `guests`, `art_works` | Payment status updates, order expiration triggers, ticket scanning validations, gift membership claim extensions, catalog updates |
| **DELETE** | `carts`, `cart_groups`, `cart_items`, pivot tables | Shopping cart clears upon checkout completion, guest session cart deletes during migration, artwork cascade cleaning |
| **JOIN** | `order_details`, `orders`, `tickets`, `payments`, `constituents`, `art_works` | Unified search, repeat visitor analysis, daily revenue charts calculations, guest vs registered distributions |
| **AGGREGATE** | `order_details`, `orders`, `tickets`, `payments` | `SUM()`, `COUNT()`, `DISTINCT`, `HAVING` filters for daily analytics and visitor metrics |
| **PESSIMISTIC LOCK** | `carts`, `payments`, `memberships` | `lockForUpdate()` (`FOR UPDATE`) for month extensions, checkout processes, settlement concurrency control |
| **PIVOT SYNC** | M2M intermediate tables | Detaches missing elements and inserts new relational links under a single logic |
