# Full Project CRUD Queries

This document contains a comprehensive reverse-engineered SQL analysis of all features in the Laravel application. It maps each system feature to its corresponding CRUD actions, sequential logical flows, realistic database SQL queries, join conditions, transactional commands, and involved database tables.

---

# SECTION A: PUBLIC FEATURES

---

## FEATURE: Homepage
### Purpose
Display the museum's landing page, welcoming visitors with links to collections, admissions, and general information.
### Feature Flow
1. Visitor loads `/`
2. Routing matches `'/'` and renders the static welcome view without database fetches.
### CREATE (C)
*None*
### READ (R)
*None*
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
*None*

---

## FEATURE: Artwork Browse
### Purpose
List collections with general paginated display, category area links, and filters.
### Feature Flow
1. Visitor loads `/art/collection`
2. Controller calls `index()` in `ArtController`.
3. Performs eager loading for `department`, `objectType`, `location`, and `images` with offset-based pagination.
### CREATE (C)
*None*
### READ (R)
```sql
-- Fetch main artwork records
SELECT art_work_id, title, accession_number, accession_year, description, gallery_number, department_id, type_id, location_id, repository_id FROM art_works LIMIT 12 OFFSET ?;

-- Fetch preloaded categories for filters
SELECT department_id, department_name FROM departments ORDER BY department_name ASC;
SELECT type_id, object_type_name FROM object_types ORDER BY object_type_name ASC;
SELECT location_id, location_name FROM locations ORDER BY location_name ASC;
```
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
Eager-loaded relationships generate secondary queries using `IN` filters instead of explicit JOINs:
```sql
SELECT * FROM departments WHERE department_id IN (?);
SELECT * FROM object_types WHERE type_id IN (?);
SELECT * FROM locations WHERE location_id IN (?);
SELECT * FROM art_work_images WHERE art_work_id IN (?);
```
### Transaction Query
*None*
### Involved Tables
`art_works`, `departments`, `object_types`, `locations`, `art_work_images`

---

## FEATURE: Artwork Search
### Purpose
Execute complex searches using keyword matching, sorting rules, curatorial filter subsets (highlights, public domain, 3D links), and multi-selection parameters.
### Feature Flow
1. Visitor inputs search query, clicks checkbox filters, and triggers `/art/collection/search`
2. Request processed in `ArtController@search`
3. Dynamically appends `WHERE` clauses for highlights, online view, medium, department, or date range.
4. Returns paginated results with sorting order.
### CREATE (C)
*None*
### READ (R)
```sql
-- Reconstructed dynamically composed SQL query based on filters
SELECT * FROM art_works 
WHERE (
    title LIKE ? 
    OR description LIKE ? 
    OR gallery_number LIKE ? 
    OR accession_number LIKE ? 
    OR EXISTS (SELECT 1 FROM constituents WHERE constituents.constituent_id = art_work_constituents.constituent_id AND display_name LIKE ?)
) 
AND is_highlight = 1 
AND is_on_view = 1 
AND is_public_domain = 1 
AND (object_url IS NOT NULL OR object_wikidata_url IS NOT NULL)
AND EXISTS (SELECT 1 FROM object_types WHERE object_types.type_id = art_works.type_id AND object_type_name IN (?))
AND department_id = ?
AND object_end_date >= ? 
AND object_begin_date <= ?
ORDER BY object_begin_date DESC, art_work_id DESC 
LIMIT 12 OFFSET ?;
```
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
Uses dynamic correlation subqueries (`EXISTS` / `IN`) rather than traditional inner/outer JOIN:
```sql
-- Check artist name M2M relationship
SELECT 1 FROM constituents 
INNER JOIN art_work_constituents ON constituents.constituent_id = art_work_constituents.constituent_id 
WHERE art_work_constituents.art_work_id = art_works.art_work_id 
AND display_name LIKE ?;
```
### Transaction Query
*None*
### Involved Tables
`art_works`, `departments`, `object_types`, `locations`, `art_work_images`, `art_work_constituents`, `constituents`, `art_work_cultures`, `cultures`, `credit_lines`, `art_work_mediums`, `mediums`

---

## FEATURE: Artwork Detail
### Purpose
View details of a specific artwork, including measurements, references, visual assets, signatures, and exhibition history.
### Feature Flow
1. Visitor loads `/art/collection/{id}` or `/art/{slug}`
2. Controller `ArtController@show` queries database by primary key or unique slug.
3. Preloads measurements, SIM markers, exhibition history, and images.
### CREATE (C)
*None*
### READ (R)
```sql
-- Select artwork details
SELECT * FROM art_works WHERE art_work_id = ? LIMIT 1;

-- Preload relations
SELECT * FROM art_work_images WHERE art_work_id = ? ORDER BY display_order ASC;
SELECT * FROM art_work_measurements WHERE art_work_id = ? ORDER BY display_order ASC;
SELECT * FROM exhibition_histories WHERE art_work_id = ? ORDER BY display_order ASC;
SELECT * FROM art_work_references WHERE art_work_id = ? ORDER BY display_order ASC;
SELECT * FROM art_work_sims WHERE art_work_id = ?;
```
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
*None* (Laravel preloads using separate primary key indexing queries)
### Transaction Query
*None*
### Involved Tables
`art_works`, `art_work_images`, `art_work_measurements`, `exhibition_histories`, `art_work_references`, `art_work_sims`

---

## FEATURE: Guest Login & Session Capture
### Purpose
Create a guest visitor session using a non-authenticated email identity for booking workflows.
### Feature Flow
1. Visitor fills out the guest checkout form.
2. POST triggers `GuestLoginController@store`
3. Checks if guest profile exists or creates a new one.
4. Stores guest metadata inside the HTTP session.
### CREATE (C)
```sql
INSERT INTO guests (email, first_name, last_name, created_at, updated_at) 
VALUES (?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
SELECT guest_id, email, first_name, last_name FROM guests WHERE email = ? LIMIT 1;
```
### UPDATE (U)
```sql
UPDATE guests SET first_name = ?, last_name = ?, updated_at = NOW() WHERE guest_id = ?;
```
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`guests`

---

## FEATURE: User Registration
### Purpose
Enable a public user to sign up, storing credentials and setting up their billing and address profile.
### Feature Flow
1. Visitor fills register form `/register`.
2. Controller `RegisterController@store` runs validation.
3. Triggers database transaction.
4. Auto-creates/checks for dynamic `PostalCode` records.
5. Inserts new authenticated `User` and `UserProfile` records.
6. Auto-logs the user in.
### CREATE (C)
```sql
-- Insert postal code metadata
INSERT INTO postal_codes (postal_code, postal_city, postal_state, postal_country, created_at, updated_at) 
VALUES (?, ?, ?, ?, NOW(), NOW());

-- Insert credentials
INSERT INTO users (email, password, created_at, updated_at) 
VALUES (?, ?, NOW(), NOW());

-- Insert user profile
INSERT INTO user_profiles (first_name, last_name, phone_number, address1, address2, postal_code_id, user_id, created_at, updated_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Check unique constraints
SELECT 1 FROM users WHERE email = ? LIMIT 1;

-- Check postal code existence
SELECT * FROM postal_codes WHERE postal_code = ? AND postal_city = ? AND postal_state = ? AND postal_country = ? LIMIT 1;
```
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- (inserts and checks execute here)
COMMIT; -- (or ROLLBACK on failure)
```
### Involved Tables
`users`, `user_profiles`, `postal_codes`

---

## FEATURE: User Login
### Purpose
Authenticate standard and admin credentials, clearing session leftovers and migrating dynamic carts.
### Feature Flow
1. Visitor fills login form and submits credentials.
2. `LoginController@login` checks email matches in `users` table.
3. Re-verifies credentials with Laravel `Auth::attempt`.
4. Performs dynamic cleanup of guest sessions.
5. Invokes `CartController::migrateSessionCartToDb` to port any anonymous items to database cart.
6. Calls `MembershipService@expireMembershipsForUser` to check active memberships.
### CREATE (C)
*None*
### READ (R)
```sql
-- Look up user record
SELECT * FROM users WHERE email = ? LIMIT 1;

-- Query expired memberships
SELECT * FROM memberships WHERE user_id = ? AND membership_status = 'active' AND expires_at < NOW();
```
### UPDATE (U)
```sql
-- Update expired membership states
UPDATE memberships SET membership_status = 'expired', updated_at = NOW() WHERE membership_id = ?;

-- Disable premium status on user
UPDATE users SET premium_ended_at = NOW(), is_premium = 0 WHERE user_id = ?;
```
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`users`, `memberships`, `carts`

---

# SECTION B: USER FEATURES

---

## FEATURE: Profile Update
### Purpose
Allow registered users to edit their first/last name, phone number, address details, and postal code.
### Feature Flow
1. Logged-in user submits the profile form at `/account`.
2. Triggers `AuthController@update` (or account dashboard method).
3. Evaluates and registers the `postal_codes` reference.
4. Performs update queries in the profile record.
### CREATE (C)
```sql
-- If postal code is brand new
INSERT INTO postal_codes (postal_code, postal_city, postal_state, postal_country, created_at, updated_at) 
VALUES (?, ?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Lookup postal code
SELECT * FROM postal_codes WHERE postal_code = ? AND postal_city = ? LIMIT 1;
```
### UPDATE (U)
```sql
UPDATE user_profiles 
SET first_name = ?, last_name = ?, phone_number = ?, address1 = ?, address2 = ?, postal_code_id = ?, updated_at = NOW() 
WHERE user_id = ?;
```
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- (operations)
COMMIT;
```
### Involved Tables
`user_profiles`, `postal_codes`

---

## FEATURE: Add to Cart (Standard Admissions)
### Purpose
Add admission tickets for specific dates, quantities, and categories to the cart.
### Feature Flow
1. User visits `/tickets/{schedule}`, selects tickets, and clicks "Add to Cart".
2. Triggers `CartController@add` or `CartController@storeAdmission`.
3. Validates business constraints: **Companion ticket quantity must not exceed Disabilities ticket quantity**.
4. Eagerly resolves/creates the active database-backed `Cart` record (valid for 2 hours).
5. Inserts corresponding `CartGroup` and `CartItem` links.
### CREATE (C)
```sql
-- If no active cart exists
INSERT INTO carts (user_id, guest_id, expires_at, created_at, updated_at) 
VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR), NOW(), NOW());

-- Insert cart group
INSERT INTO cart_groups (cart_id, created_at, updated_at) 
VALUES (?, NOW(), NOW());

-- Insert cart item mapping
INSERT INTO cart_items (cart_group_id, ticket_availability_id, quantity, created_at, updated_at) 
VALUES (?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Find existing unexpired cart
SELECT * FROM carts WHERE expires_at > NOW() AND (user_id = ? OR guest_id = ?) LIMIT 1;

-- Check ticket availability configuration
SELECT * FROM ticket_availability WHERE ticket_type_id = ? AND visit_schedule_id = ? LIMIT 1;

-- Retrieve type names to enforce constraints (e.g. disabilities/companion check)
SELECT * FROM ticket_types WHERE ticket_type_id = ? LIMIT 1;
```
### UPDATE (U)
```sql
-- If cart exists but was expired
UPDATE carts SET expires_at = DATE_ADD(NOW(), INTERVAL 2 HOUR), updated_at = NOW() WHERE cart_id = ?;
```
### DELETE (D)
```sql
-- Safe update replacement: clears old target group items when modifying
DELETE FROM cart_items WHERE cart_group_id = ?;
DELETE FROM cart_groups WHERE cart_group_id = ?;
```
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- (runs checks, creates parent cart, stores items, deletes replacement target if modifying)
COMMIT;
```
### Involved Tables
`carts`, `cart_groups`, `cart_items`, `ticket_availability`, `ticket_types`

---

## FEATURE: Cart Management & Modification
### Purpose
View, remove, or modify existing groups in the cart.
### Feature Flow
1. User visits `/cart`
2. `CartController@index` resolves cart contents.
3. Visitor clicks "Remove" to delete a cart group, or "Modify" to change ticket choices.
### CREATE (C)
*None*
### READ (R)
```sql
-- Load user cart
SELECT * FROM carts WHERE expires_at > NOW() AND (user_id = ? OR guest_id = ?) LIMIT 1;

-- Preload nested objects
SELECT * FROM cart_groups WHERE cart_id = ?;
SELECT * FROM cart_items WHERE cart_group_id IN (?);
SELECT * FROM ticket_availability WHERE ticket_availability_id IN (?);
```
### UPDATE (U)
*None*
### DELETE (D)
```sql
-- Triggered during removeGroup()
START TRANSACTION;
DELETE FROM cart_items WHERE cart_group_id = ?;
DELETE FROM cart_groups WHERE cart_group_id = ?;
COMMIT;
```
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- Delete operations inside removeGroup
COMMIT;
```
### Involved Tables
`carts`, `cart_groups`, `cart_items`

---

## FEATURE: Ticket Booking & Checkout Process
### Purpose
Process the checkout request, verifying ticket availability, capacity limits, and constraints, then generating a pending order.
### Feature Flow
1. Logged-in user or guest submits the checkout form.
2. POST maps to `CheckoutController@checkout`
3. Checks for an existing pending order to enforce idempotency.
4. Executes the booking transaction:
   - Locks cart records for update.
   - Evaluates disabilities/companion constraints.
   - Calculates the total cost.
   - Inserts records into `orders` and `payments`.
5. Redirects to `/checkout/payments/{order}`.
### CREATE (C)
```sql
-- Insert pending order
INSERT INTO orders (order_code, user_id, guest_id, order_date, expired_at, total_amount, order_status, created_at, updated_at) 
VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 20 MINUTE), ?, 'pending_payment', NOW(), NOW());

-- Insert pending payment transaction
INSERT INTO payments (order_id, payment_method, amount, payment_status, created_at, updated_at) 
VALUES (?, 'Credit Card', ?, 'Pending', NOW(), NOW());
```
### READ (R)
```sql
-- Check for existing pending order (Idempotency)
SELECT * FROM orders 
WHERE (user_id = ? OR guest_id = ?) 
AND expired_at > NOW() 
AND EXISTS (SELECT 1 FROM payments WHERE payments.order_id = orders.order_id AND payment_status = 'Pending') 
ORDER BY order_date DESC LIMIT 1;

-- Lock cart and items for processing
SELECT * FROM carts WHERE user_id = ? FOR UPDATE;
```
### UPDATE (U)
*None* (Cart and stock updates are finalized only after payment is completed)
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- Select for update runs
-- Order and payment creation query executes
COMMIT;
```
### Involved Tables
`carts`, `cart_groups`, `cart_items`, `orders`, `payments`, `ticket_availability`

---

## FEATURE: Payment Settlement & Ticket Issuance
### Purpose
Settle payment for an order, update order status, issue QR-coded tickets, and clear items from the cart.
### Feature Flow
1. User clicks "Pay Now" on `/checkout/payments/{order}`
2. Controller `CheckoutController@pay` handles payment routing:
   - Verifies the order hasn't expired.
   - Settle payment status in a transaction.
   - Generates unique QR keys for each ticket.
   - Deletes active cart entries to avoid duplicates.
3. Redirects to the booking confirmation screen `/checkout/success/{order}`.
### CREATE (C)
```sql
-- Generate new tickets
INSERT INTO tickets (order_id, ticket_availability_id, qr_code, status, created_at, updated_at) 
VALUES (?, ?, ?, 'valid', NOW(), NOW());
```
### READ (R)
```sql
-- Load order and lock payment details
SELECT * FROM payments WHERE order_id = ? FOR UPDATE;

-- Select user's dynamic cart to generate tickets
SELECT * FROM carts WHERE user_id = ? OR guest_id = ? LIMIT 1;
```
### UPDATE (U)
```sql
-- Update payment status to paid
UPDATE payments SET payment_status = 'Paid', paid_at = NOW(), updated_at = NOW() WHERE payment_id = ?;

-- Complete order status
UPDATE orders SET order_status = 'paid', updated_at = NOW() WHERE order_id = ?;
```
### DELETE (D)
```sql
-- Clean up cart items and headers after successful checkout
DELETE FROM cart_items WHERE cart_group_id IN (SELECT cart_group_id FROM cart_groups WHERE cart_id = ?);
DELETE FROM cart_groups WHERE cart_id = ?;
DELETE FROM carts WHERE cart_id = ?;
```
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- Lock payment for update
-- Update payment state
-- Update order state
-- Loop insert tickets
-- Delete cart records
COMMIT;
```
### Involved Tables
`orders`, `payments`, `tickets`, `carts`, `cart_groups`, `cart_items`

---

## FEATURE: Membership Purchase
### Purpose
Allow authenticated users or guests to sign up for membership tiers (Individual, Family, Patron).
### Feature Flow
1. User visits `/members/membership`, selects a tier, and submits their details.
2. Triggers `MembershipController@purchase`.
3. Resolves cost from catalog array.
4. Starts a transaction to create a membership-type order and a pending payment.
5. Redirects to `/checkout/payments/{order}`.
### CREATE (C)
```sql
-- Insert membership order
INSERT INTO orders (order_code, user_id, guest_id, order_date, expired_at, total_amount, order_status, created_at, updated_at) 
VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 20 MINUTE), ?, 'pending_payment', NOW(), NOW());

-- Insert pending payment mapping
INSERT INTO payments (order_id, payment_method, amount, payment_status, created_at, updated_at) 
VALUES (?, 'Membership', ?, 'Pending', NOW(), NOW());
```
### READ (R)
*None*
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- Creates order and payment
COMMIT;
```
### Involved Tables
`orders`, `payments`

---

## FEATURE: Membership Claim & Activation
### Purpose
Activate a membership after successful payment, or claim a membership gifted via an activation token.
### Feature Flow
1. When a user completes membership payment, the system triggers `MembershipService@createPendingMembership` (creating a pending verification status or a gift claim record).
2. For standard purchases, `activateMembership` activates the membership immediately, setting expiration dates and syncing premium status in `users`.
3. For gifted memberships, a user visits `/member/gift/claim/{token}`, triggering `claimGiftMembership` which links the membership to the user, updates status to `claimed` or `active`, and extends any active membership dates.
### CREATE (C)
```sql
-- Save pending membership record
INSERT INTO memberships (order_id, user_id, recipient_email, membership_status, is_gift, auto_renewal, activation_token, token_expires_at, created_at, updated_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW(), NOW());
```
### READ (R)
```sql
-- Lookup existing active membership to extend dates
SELECT * FROM memberships 
WHERE user_id = ? 
AND membership_status = 'active' 
AND expires_at > NOW() 
ORDER BY expires_at DESC LIMIT 1 FOR UPDATE;
```
### UPDATE (U)
```sql
-- Case A: Activate/Claim membership
UPDATE memberships 
SET user_id = ?, membership_status = 'active', activated_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 1 MONTH), activation_token = NULL, token_expires_at = NULL, updated_at = NOW() 
WHERE membership_id = ?;

-- Case B: Extend existing membership duration
UPDATE memberships SET expires_at = DATE_ADD(expires_at, INTERVAL 1 MONTH), updated_at = NOW() WHERE membership_id = ?;

-- Sync premium flag and timeline limits inside users table
UPDATE users SET premium_started_at = ?, premium_ended_at = ?, is_premium = 1 WHERE user_id = ?;
```
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- Find existing membership for update
-- Update/Claim/Extend membership records
-- Sync users premium columns
COMMIT;
```
### Involved Tables
`memberships`, `users`, `orders`

---

## FEATURE: Ticket View & Verification
### Purpose
Display order summaries and dynamic QR codes for purchased tickets, and allow staff to scan/validate tickets.
### Feature Flow
1. User accesses `/order/show/{order}` to view details and QR codes.
2. Staff scans a QR code, triggering `TicketController@scan` or `AdminOrderController@validateTicket`.
3. System verifies the ticket code in the `tickets` table and marks it as used.
### CREATE (C)
*None*
### READ (R)
```sql
-- Read order details
SELECT * FROM orders WHERE order_id = ? LIMIT 1;

-- Preload ticket items
SELECT * FROM tickets WHERE order_id = ?;

-- Look up ticket via QR code
SELECT * FROM tickets WHERE qr_code = ? LIMIT 1;
```
### UPDATE (U)
```sql
-- Set ticket status to used on successful scan
UPDATE tickets SET status = 'used', updated_at = NOW() WHERE ticket_id = ?;

-- Transition order status to completed if all tickets are scanned
UPDATE orders SET order_status = 'completed', updated_at = NOW() WHERE order_id = ?;
```
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`orders`, `tickets`

---

# SECTION C: ADMIN FEATURES

---

## FEATURE: Artwork CRUD
### Purpose
Create, read, update, and delete artwork records, including details, categories, descriptions, medium choices, and custom measurements.
### Feature Flow
1. Admin loads the artwork dashboard at `/admin/artworks`.
2. Admin submits the artwork form (via POST to `store` or PUT to `update`).
3. Executes dynamic inline auto-creation for custom items (categories, locations, materials).
4. Inserts or updates the `art_works` table.
5. Detaches and rebuilds M2M links (`syncM2MRelationships`) and child tables (`saveChildRecords` via a Safe-Replace strategy) to keep data clean.
6. Admin can also delete records via DELETE, which removes child items first before detaching relationships.
### CREATE (C)
```sql
-- Insert main artwork metadata
INSERT INTO art_works (met_object_id, title, slug, accession_number, accession_year, description, gallery_number, object_date_display, object_begin_date, object_end_date, dimensions_display, rights_and_reproduction, provenance, department_id, type_id, location_id, repository_id, classification_id, credit_line_id, is_on_view, is_highlight, is_public_domain, is_timeline_work, created_at, updated_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Load paginated artworks with search keywords and category filters
SELECT * FROM art_works 
WHERE (title LIKE ? OR accession_number LIKE ? OR met_object_id LIKE ? OR slug LIKE ?) 
AND department_id = ? 
ORDER BY art_work_id DESC LIMIT 20 OFFSET ?;
```
### UPDATE (U)
```sql
-- Update artwork details
UPDATE art_works 
SET met_object_id = ?, title = ?, accession_number = ?, accession_year = ?, description = ?, gallery_number = ?, object_date_display = ?, object_begin_date = ?, object_end_date = ?, dimensions_display = ?, rights_and_reproduction = ?, provenance = ?, department_id = ?, type_id = ?, location_id = ?, repository_id = ?, classification_id = ?, credit_line_id = ?, is_on_view = ?, is_highlight = ?, is_public_domain = ?, is_timeline_work = ?, updated_at = NOW() 
WHERE art_work_id = ?;
```
### DELETE (D)
```sql
-- Detach M2M relationships
DELETE FROM art_work_materials WHERE art_work_id = ?;
DELETE FROM art_work_mediums WHERE art_work_id = ?;
DELETE FROM art_work_constituents WHERE art_work_id = ?;
DELETE FROM art_work_tags WHERE art_work_id = ?;
DELETE FROM art_work_cultures WHERE art_work_id = ?;
DELETE FROM art_work_periods WHERE art_work_id = ?;
DELETE FROM art_work_dynasties WHERE art_work_id = ?;
DELETE FROM art_work_reigns WHERE art_work_id = ?;
DELETE FROM art_work_portfolios WHERE art_work_id = ?;

-- Delete child records
DELETE FROM art_work_images WHERE art_work_id = ?;
DELETE FROM art_work_measurements WHERE art_work_id = ?;
DELETE FROM exhibition_histories WHERE art_work_id = ?;
DELETE FROM art_work_references WHERE art_work_id = ?;
DELETE FROM art_work_sims WHERE art_work_id = ?;

-- Delete main artwork record
DELETE FROM art_works WHERE art_work_id = ?;
```
### JOIN Query
*None*
### Transaction Query
```sql
START TRANSACTION;
-- Creates/Updates parent artwork record
-- Runs sync M2M queries
-- Replaces child tables
COMMIT;
```
### Involved Tables
`art_works`, `departments`, `object_types`, `locations`, `repositories`, `classifications`, `credit_lines`, `materials`, `mediums`, `tags`, `cultures`, `periods`, `dynasties`, `reigns`, `portfolios`, `art_work_materials`, `art_work_mediums`, `art_work_tags`, `art_work_cultures`, `art_work_periods`, `art_work_dynasties`, `art_work_reigns`, `art_work_portfolios`, `art_work_constituents`, `art_work_measurements`, `art_work_references`, `art_work_sims`, `exhibition_histories`, `art_work_images`

---

## FEATURE: Artwork Image CRUD
### Purpose
Manage artwork images, including adding new URLs, setting primary display photos, and arranging image orders.
### Feature Flow
1. Managed directly inside `saveImages()` or the main artwork update flow.
2. System uses a Safe-Replace strategy: force-deletes existing rows and re-creates them based on the form inputs to maintain proper ordering.
3. Allows changing the primary image selection via radio toggles.
### CREATE (C)
```sql
INSERT INTO art_work_images (art_work_id, image_url, is_primary, display_order, created_at, updated_at) 
VALUES (?, ?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
SELECT * FROM art_work_images WHERE art_work_id = ? ORDER BY display_order ASC;
```
### UPDATE (U)
```sql
-- Toggle primary display selections
UPDATE art_work_images SET is_primary = 0 WHERE art_work_id = ?;
UPDATE art_work_images SET is_primary = 1 WHERE image_id = ?;
```
### DELETE (D)
```sql
-- Safe-replace cleanup
DELETE FROM art_work_images WHERE art_work_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`art_work_images`

---

## FEATURE: Exhibition History CRUD
### Purpose
Add, update, or remove details for exhibitions where an artwork has been featured.
### Feature Flow
1. Managed via `saveChildRecords` in `ArtworkController@store` or `update`.
2. Employs a Safe-Replace strategy: deletes all existing exhibition records for the artwork before inserting the updated list.
### CREATE (C)
```sql
INSERT INTO exhibition_histories (art_work_id, exhibition_title, venue_name, city_name, exhibition_date_display, start_date, end_date, catalogue_reference, exhibition_notes, display_order, created_at, updated_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
SELECT * FROM exhibition_histories WHERE art_work_id = ? ORDER BY display_order ASC;
```
### UPDATE (U)
*None* (handled by Safe-Replace strategy)
### DELETE (D)
```sql
-- Clear old entries before replacing
DELETE FROM exhibition_histories WHERE art_work_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`exhibition_histories`

---

## FEATURE: Artwork Reference CRUD
### Purpose
Manage bibliographies and literature citations for an artwork.
### Feature Flow
1. Managed via `saveChildRecords` in `ArtworkController@store` or `update`.
2. Employs a Safe-Replace strategy: deletes all existing reference records for the artwork before inserting the updated list.
### CREATE (C)
```sql
INSERT INTO art_work_references (art_work_id, reference_text, display_order, created_at, updated_at) 
VALUES (?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
SELECT * FROM art_work_references WHERE art_work_id = ? ORDER BY display_order ASC;
```
### UPDATE (U)
*None* (handled by Safe-Replace strategy)
### DELETE (D)
```sql
-- Clear old entries before replacing
DELETE FROM art_work_references WHERE art_work_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`art_work_references`

---

## FEATURE: User Management CRUD
### Purpose
Allow admins to view and manage registered user accounts.
### Feature Flow
1. Admin opens `/admin/users`
2. `AdminUserController@index` queries and displays users.
### CREATE (C)
*None* (User creation is handled by public registration)
### READ (R)
```sql
SELECT * FROM users ORDER BY created_at DESC;
```
### UPDATE (U)
```sql
-- Set admin permissions or update status
UPDATE users SET is_admin = ?, updated_at = NOW() WHERE user_id = ?;
```
### DELETE (D)
```sql
DELETE FROM users WHERE user_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`users`

---

## FEATURE: Order Management CRUD
### Purpose
Allow admins to view booking orders, validate tickets, or cancel purchases.
### Feature Flow
1. Admin loads `/admin/orders`
2. Admin can search for orders by customer name, ticket code, or payment status.
3. Admin can cancel an order, which transitions the status to `cancelled` and marks all linked tickets as `cancelled`.
### CREATE (C)
*None*
### READ (R)
```sql
-- Load recent orders with user profile or guest info
SELECT orders.*, user_profiles.first_name, user_profiles.last_name, guests.first_name, guests.last_name 
FROM orders 
LEFT JOIN users ON users.user_id = orders.user_id 
LEFT JOIN user_profiles ON user_profiles.user_id = users.user_id 
LEFT JOIN guests ON guests.guest_id = orders.guest_id 
ORDER BY orders.order_date DESC 
LIMIT 20 OFFSET ?;
```
### UPDATE (U)
```sql
-- Cancel order
UPDATE orders SET order_status = 'cancelled', updated_at = NOW() WHERE order_id = ?;

-- Cancel associated tickets
UPDATE tickets SET status = 'cancelled', updated_at = NOW() WHERE order_id = ?;
```
### DELETE (D)
*None* (Uses soft deletes/status changes instead of hard deletes)
### JOIN Query
```sql
-- Fetch order data with customer profiles
SELECT o.*, u.email, up.first_name, up.last_name, g.email as guest_email, g.first_name as guest_first_name 
FROM orders o 
LEFT JOIN users u ON u.user_id = o.user_id 
LEFT JOIN user_profiles up ON up.user_id = u.user_id 
LEFT JOIN guests g ON g.guest_id = o.guest_id;
```
### Transaction Query
```sql
START TRANSACTION;
-- Update order status to cancelled
-- Cancel associated tickets
COMMIT;
```
### Involved Tables
`orders`, `tickets`, `users`, `user_profiles`, `guests`, `payments`

---

## FEATURE: Payment Management CRUD
### Purpose
Allow admins to view all transactions, trace payment methods, and filter by status.
### Feature Flow
1. Admin opens `/admin/payments`
2. `AdminPaymentController@index` fetches transaction history.
### CREATE (C)
*None*
### READ (R)
```sql
SELECT payments.*, orders.order_code 
FROM payments 
INNER JOIN orders ON orders.order_id = payments.order_id 
ORDER BY payments.created_at DESC 
LIMIT 20 OFFSET ?;
```
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
```sql
SELECT p.*, o.order_code, o.order_status 
FROM payments p 
INNER JOIN orders o ON o.order_id = p.order_id;
```
### Transaction Query
*None*
### Involved Tables
`payments`, `orders`

---

## FEATURE: Visit Schedule CRUD
### Purpose
Create and manage visit schedules, locations, and capacity limits.
### Feature Flow
1. Admin opens the ticket management dashboard.
2. Admin adds ticket stock, creating or updating a visit schedule for a location and date.
### CREATE (C)
```sql
INSERT INTO visit_schedules (location_id, visit_date, capacity_limit, created_at, updated_at) 
VALUES (?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Check for existing schedule
SELECT * FROM visit_schedules WHERE location_id = ? AND visit_date = ? LIMIT 1;
```
### UPDATE (U)
```sql
-- Update capacity limit
UPDATE visit_schedules SET capacity_limit = ?, updated_at = NOW() WHERE visit_schedule_id = ?;
```
### DELETE (D)
```sql
DELETE FROM visit_schedules WHERE visit_schedule_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`visit_schedules`, `locations`

---

## FEATURE: Dynamic Ticket Type CRUD
### Purpose
Manage available ticket categories (e.g., General Admission, Member, Disabilities, Companion) and their base prices.
### Feature Flow
1. Admin opens `/admin/tickets/management`.
2. Admin creates, updates, or soft-deletes a ticket type.
### CREATE (C)
```sql
INSERT INTO ticket_types (ticket_type_name, base_price, created_at, updated_at) 
VALUES (?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Read active ticket types
SELECT * FROM ticket_types WHERE deleted_at IS NULL ORDER BY ticket_type_name ASC;
```
### UPDATE (U)
```sql
-- Update ticket type
UPDATE ticket_types SET ticket_type_name = ?, base_price = ?, updated_at = NOW() WHERE ticket_type_id = ?;
```
### DELETE (D)
```sql
-- Soft delete ticket type
UPDATE ticket_types SET deleted_at = NOW() WHERE ticket_type_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`ticket_types`

---

## FEATURE: Dynamic Ticket Stock CRUD
### Purpose
Manage ticket availability and capacity limits for specific visit schedules and ticket types.
### Feature Flow
1. Admin navigates to `/admin/tickets/management`.
2. Admin updates the capacity limit for a ticket availability record.
### CREATE (C)
```sql
-- Add new ticket availability to schedule
INSERT INTO ticket_availability (visit_schedule_id, ticket_type_id, capacity_limit, created_at, updated_at) 
VALUES (?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Check for existing availability
SELECT * FROM ticket_availability WHERE visit_schedule_id = ? AND ticket_type_id = ? LIMIT 1;
```
### UPDATE (U)
```sql
-- Update capacity limit for a ticket availability
UPDATE ticket_availability SET capacity_limit = ?, updated_at = NOW() WHERE ticket_availability_id = ?;
```
### DELETE (D)
```sql
DELETE FROM ticket_availability WHERE ticket_availability_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`ticket_availability`, `visit_schedules`, `ticket_types`

---

## FEATURE: Dashboard & Ticket Analytics
### Purpose
Calculate sales performance, track visitor volumes, and monitor ticket statistics.
### Feature Flow
1. Admin opens `/admin` or `/admin/ticket-analytics`.
2. Queries run to aggregate sales numbers, calculate order totals, and count active members.
### CREATE (C)
*None*
### READ (R)
```sql
-- Count total sales
SELECT SUM(total_amount) AS total_revenue FROM orders WHERE order_status = 'paid';

-- Count sold tickets
SELECT COUNT(*) FROM tickets WHERE status = 'valid' OR status = 'used';

-- Count active members
SELECT COUNT(*) FROM memberships WHERE membership_status = 'active';

-- Aggregate daily revenue for charts
SELECT DATE(order_date) AS date, SUM(total_amount) AS revenue 
FROM orders 
WHERE order_status = 'paid' 
GROUP BY DATE(order_date) 
ORDER BY date ASC;
```
### UPDATE (U)
```sql
*None*
```
### DELETE (D)
```sql
*None*
```
### JOIN Query
```sql
-- Get ticket sales breakdown by type
SELECT tt.ticket_type_name, COUNT(t.ticket_id) AS total_sold, SUM(tt.base_price) AS type_revenue 
FROM tickets t 
INNER JOIN ticket_availability ta ON ta.ticket_availability_id = t.ticket_availability_id 
INNER JOIN ticket_types tt ON tt.ticket_type_id = ta.ticket_type_id 
WHERE t.status != 'cancelled' 
GROUP BY tt.ticket_type_name;
```
### Transaction Query
*None*
### Involved Tables
`orders`, `tickets`, `ticket_availability`, `ticket_types`, `memberships`

---

# SECTION D: SMART & PIVOT FEATURES

---

## FEATURE: Auto Create Classification & Categories
### Purpose
Automatically register classification, category, department, or location records when an artwork is stored or updated with new names.
### Feature Flow
1. Managed via `resolveInlineMasterRecords()` in `ArtworkController`.
2. Checks if a classification name already exists (case-insensitive lookup using `LOWER()`).
3. If not found, it inserts a new classification record.
4. Auto-maps the newly created classification ID to the artwork model.
### CREATE (C)
```sql
INSERT INTO classifications (classification_name, created_at, updated_at) 
VALUES (?, NOW(), NOW());
```
### READ (R)
```sql
SELECT classification_id FROM classifications WHERE LOWER(classification_name) = ? LIMIT 1;
```
### UPDATE (U)
*None*
### DELETE (D)
*None*
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`classifications`, `art_works`

---

## FEATURE: Smart Geography Master Resolver
### Purpose
Automatically resolve hierarchical geography parameters (countries, states, regions, subregions, counties, cities, excavations) when saving an artwork's location.
### Feature Flow
1. Triggers during `preprocessGeographies()` in `ArtworkController`.
2. Inspects newly submitted geography names and does a case-insensitive check in the corresponding master table.
3. If a name is missing, it auto-creates the master record (linking it to its parent, such as placing a new `State` under the correct `Country` ID).
4. Inserts the finalized geography mapping into `art_work_geographies`.
### CREATE (C)
```sql
-- Auto-create missing master records
INSERT INTO countries (country_name, created_at, updated_at) VALUES (?, NOW(), NOW());
INSERT INTO states (state_name, country_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW());
INSERT INTO regions (region_name, country_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW());
INSERT INTO subregions (subregion_name, region_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW());

-- Map geography relation to artwork
INSERT INTO art_work_geographies (art_work_id, geography_type_id, country_id, state_id, county_id, city_id, region_id, subregion_id, locale_id, locus_id, excavation_id, river_id, created_at, updated_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());
```
### READ (R)
```sql
-- Check country existence
SELECT country_id FROM countries WHERE LOWER(country_name) = ? LIMIT 1;

-- Check state existence under country
SELECT state_id FROM states WHERE LOWER(state_name) = ? AND country_id = ? LIMIT 1;
```
### UPDATE (U)
*None*
### DELETE (D)
```sql
-- Clear old geography entries before saving changes (Safe-Replace strategy)
DELETE FROM art_work_geographies WHERE art_work_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`art_work_geographies`, `geography_types`, `countries`, `states`, `counties`, `cities`, `regions`, `subregions`, `locales`, `loci`, `excavations`, `rivers`

---

## FEATURE: Many-to-Many Pivot Synchronization
### Purpose
Sync Many-to-Many relationships for an artwork (e.g. materials, mediums, tags, cultures, periods) using pivot tables.
### Feature Flow
1. Triggers during `syncM2MRelationships()` in `ArtworkController`.
2. System calls Laravel's `$artwork->materials()->sync(...)`, which identifies and removes obsolete pivot rows and inserts new associations.
### CREATE (C)
```sql
-- Insert new pivot associations
INSERT INTO art_work_materials (art_work_id, material_id) VALUES (?, ?);
INSERT INTO art_work_mediums (art_work_id, medium_id) VALUES (?, ?);
```
### READ (R)
```sql
-- Load existing pivot rows for comparison
SELECT material_id FROM art_work_materials WHERE art_work_id = ?;
```
### UPDATE (U)
*None*
### DELETE (D)
```sql
-- Remove old pivot associations
DELETE FROM art_work_materials WHERE art_work_id = ? AND material_id IN (?);
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`art_work_materials`, `art_work_mediums`, `art_work_tags`, `art_work_cultures`, `art_work_periods`

---

## FEATURE: Smart Constituent Pivot Synchronization
### Purpose
Link constituents (artists) to an artwork with detailed role, prefix, suffix, and display order attributes.
### Feature Flow
1. Managed via `saveConstituents()` in `ArtworkController`.
2. Resolves or auto-creates missing `Constituent` and `ConstituentRole` records.
3. Checks if the specific constituent-role mapping already exists on the artwork.
4. Inserts the association with custom pivot fields.
### CREATE (C)
```sql
-- Auto-create constituent if missing
INSERT INTO constituents (display_name, alpha_sort, created_at, updated_at) VALUES (?, ?, NOW(), NOW());

-- Auto-create role if missing
INSERT INTO constituent_roles (role_name, created_at, updated_at) VALUES (?, NOW(), NOW());

-- Map constituent with pivot data
INSERT INTO art_work_constituents (art_work_id, constituent_id, role_id, prefix_id, suffix_id, display_order) 
VALUES (?, ?, ?, ?, ?, ?);
```
### READ (R)
```sql
-- Check for existing constituent association
SELECT 1 FROM art_work_constituents 
WHERE art_work_id = ? 
AND constituent_id = ? 
AND role_id = ? LIMIT 1;
```
### UPDATE (U)
*None*
### DELETE (D)
```sql
-- Clear old constituents (during hard delete)
DELETE FROM art_work_constituents WHERE art_work_id = ?;
```
### JOIN Query
*None*
### Transaction Query
*None*
### Involved Tables
`art_work_constituents`, `constituents`, `constituent_roles`, `constituent_prefixes`, `constituent_suffixes`
