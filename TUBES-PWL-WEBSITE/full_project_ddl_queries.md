# Full Project DDL Queries

[ignoring loop detection]

## 1. CREATE DATABASE / USE

**Purpose**: Create the schema and set it as the active database for all subsequent statements.

```sql
CREATE DATABASE IF NOT EXISTS `tubes_sbd_website`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `tubes_sbd_website`;
```

---

## 2. CREATE TABLE

Tables are ordered by dependency (parents first) to honour foreign‑key constraints during import.

### 2.1 Master / Lookup Tables

These provide reference data used throughout the system.

```sql
CREATE TABLE countries (
  country_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE states (
  state_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  country_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  CONSTRAINT fk_states_country FOREIGN KEY (country_id) REFERENCES countries(country_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE counties (
  county_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  state_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  CONSTRAINT fk_counties_state FOREIGN KEY (state_id) REFERENCES states(state_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE cities (
  city_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  county_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  CONSTRAINT fk_cities_county FOREIGN KEY (county_id) REFERENCES counties(county_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE regions (
  region_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE subregions (
  subregion_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  region_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  CONSTRAINT fk_subregions_region FOREIGN KEY (region_id) REFERENCES regions(region_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE locales (
  locale_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE loci (
  locus_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE excavations (
  excavation_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE rivers (
  river_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE geography_types (
  geography_type_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE mediums (
  medium_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE classifications (
  classification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE object_types (
  object_type_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE artists (
  artist_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  birth_year INT NULL,
  death_year INT NULL,
  biography TEXT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE constituent_roles (
  role_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE constituent_prefixes (
  prefix_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE constituent_suffixes (
  suffix_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE postal_codes (
  postal_code_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  postal_code VARCHAR(20) NOT NULL,
  postal_city VARCHAR(255) NOT NULL,
  postal_state VARCHAR(255) NOT NULL,
  postal_country VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);
```

### 2.2 Core Entity Tables

These are the main domain objects.

```sql
CREATE TABLE locations (
  location_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location_name VARCHAR(255) NOT NULL,
  address TEXT NULL,
  capacity_limit INT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE departments (
  department_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_name VARCHAR(255) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE users (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  premium_started_at DATETIME NULL,
  premium_ended_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE user_profiles (
  user_profile_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  first_name VARCHAR(255) NULL,
  last_name VARCHAR(255) NULL,
  phone_number VARCHAR(50) NULL,
  address1 TEXT NULL,
  address2 TEXT NULL,
  postal_code_id INT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_user_profiles_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_user_profiles_postal_code FOREIGN KEY (postal_code_id) REFERENCES postal_codes(postal_code_id) ON DELETE SET NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE guests (
  guest_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  first_name VARCHAR(255) NULL,
  last_name VARCHAR(255) NULL,
  session_token VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE carts (
  cart_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  guest_id INT UNSIGNED NULL,
  expires_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_carts_guest FOREIGN KEY (guest_id) REFERENCES guests(guest_id) ON DELETE SET NULL,
  CONSTRAINT chk_carts_user_xor_guest CHECK ((user_id IS NOT NULL AND guest_id IS NULL) OR (user_id IS NULL AND guest_id IS NOT NULL)),
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE cart_groups (
  cart_group_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_cart_groups_cart FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE ticket_types (
  ticket_type_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_type_name VARCHAR(100) NOT NULL,
  base_price DECIMAL(8,2) NOT NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE visit_schedules (
  visit_schedule_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location_id INT UNSIGNED NOT NULL,
  visit_date DATE NOT NULL,
  capacity_limit INT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_visit_schedules_location FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE ticket_availabilities (
  ticket_availability_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  visit_schedule_id INT UNSIGNED NOT NULL,
  ticket_type_id INT UNSIGNED NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  available_quantity INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_ticket_avail_schedule FOREIGN KEY (visit_schedule_id) REFERENCES visit_schedules(visit_schedule_id) ON DELETE RESTRICT,
  CONSTRAINT fk_ticket_avail_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(ticket_type_id) ON DELETE RESTRICT,
  CONSTRAINT uq_ticket_availability UNIQUE (visit_schedule_id, ticket_type_id),
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE orders (
  order_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(50) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  guest_id INT UNSIGNED NULL,
  order_date DATETIME NOT NULL,
  expired_at DATETIME NULL,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  order_type ENUM('ticket','membership') NOT NULL DEFAULT 'ticket',
  order_status ENUM('pending','completed','cancelled','failed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_guest FOREIGN KEY (guest_id) REFERENCES guests(guest_id) ON DELETE SET NULL,
  CONSTRAINT chk_orders_user_xor_guest CHECK ((user_id IS NOT NULL AND guest_id IS NULL) OR (user_id IS NULL AND guest_id IS NOT NULL)),
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE payments (
  payment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_status VARCHAR(50) NOT NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE memberships (
  membership_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  recipient_email VARCHAR(255) NULL,
  membership_status ENUM('verification_pending','gift_pending_claim','active','expired','cancelled') NOT NULL DEFAULT 'verification_pending',
  is_gift TINYINT(1) NOT NULL DEFAULT 0,
  auto_renewal TINYINT(1) NOT NULL DEFAULT 0,
  activation_token VARCHAR(255) NULL UNIQUE,
  token_expires_at DATETIME NULL,
  activated_at DATETIME NULL,
  expires_at DATETIME NULL,
  deleted_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_memberships_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  CONSTRAINT fk_memberships_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE tickets (
  ticket_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  ticket_availability_id INT UNSIGNED NOT NULL,
  qr_code VARCHAR(255) NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'unused',
  used_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_tickets_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  CONSTRAINT fk_tickets_availability FOREIGN KEY (ticket_availability_id) REFERENCES ticket_availabilities(ticket_availability_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE order_details (
  order_detail_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  ticket_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT fk_order_details_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  CONSTRAINT fk_order_details_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE cart_items (
  cart_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_group_id INT UNSIGNED NOT NULL,
  ticket_availability_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT fk_cart_items_group FOREIGN KEY (cart_group_id) REFERENCES cart_groups(cart_group_id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_ticket_availability FOREIGN KEY (ticket_availability_id) REFERENCES ticket_availabilities(ticket_availability_id) ON DELETE RESTRICT,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE art_works (
  art_work_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  artist_id INT UNSIGNED NULL,
  department_id INT UNSIGNED NOT NULL,
  object_type_id INT UNSIGNED NOT NULL,
  classification_id INT UNSIGNED NOT NULL,
  medium_id INT UNSIGNED NULL,
  location_id INT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_art_work_artist FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE SET NULL,
  CONSTRAINT fk_art_work_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT,
  CONSTRAINT fk_art_work_object_type FOREIGN KEY (object_type_id) REFERENCES object_types(object_type_id) ON DELETE RESTRICT,
  CONSTRAINT fk_art_work_classification FOREIGN KEY (classification_id) REFERENCES classifications(classification_id) ON DELETE RESTRICT,
  CONSTRAINT fk_art_work_medium FOREIGN KEY (medium_id) REFERENCES mediums(medium_id) ON DELETE SET NULL,
  CONSTRAINT fk_art_work_location FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE SET NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE art_work_images (
  image_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  art_work_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_art_work_images_artwork FOREIGN KEY (art_work_id) REFERENCES art_works(art_work_id) ON DELETE CASCADE,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE art_work_sims (
  art_work_sim_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  art_work_id INT UNSIGNED NOT NULL,
  sim_type ENUM('Signature','Inscription','Marking') NOT NULL,
  sim_text TEXT NULL,
  deleted_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_art_work_sims_artwork FOREIGN KEY (art_work_id) REFERENCES art_works(art_work_id) ON DELETE CASCADE,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE constituents (
  constituent_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  met_constituent_id VARCHAR(50) NULL,
  display_name VARCHAR(255) NOT NULL,
  display_bio TEXT NULL,
  alpha_sort VARCHAR(255) NULL,
  birth_year INT NULL,
  death_year INT NULL,
  birth_date_display VARCHAR(100) NULL,
  death_date_display VARCHAR(100) NULL,
  birth_place VARCHAR(255) NULL,
  death_place VARCHAR(255) NULL,
  gender VARCHAR(20) NULL,
  ulan_url VARCHAR(255) NULL,
  wikidata_url VARCHAR(255) NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);
```

### 2.3 Pivot / Many‑to‑Many Tables

These bridge tables implement many‑to‑many relationships.

```sql
CREATE TABLE art_work_constituents (
  art_work_id INT UNSIGNED NOT NULL,
  constituent_id INT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NULL,
  prefix_id INT UNSIGNED NULL,
  suffix_id INT UNSIGNED NULL,
  display_order INT UNSIGNED DEFAULT 1,
  PRIMARY KEY (art_work_id, constituent_id),
  CONSTRAINT fk_awc_art_work FOREIGN KEY (art_work_id) REFERENCES art_works(art_work_id) ON DELETE CASCADE,
  CONSTRAINT fk_awc_constituent FOREIGN KEY (constituent_id) REFERENCES constituents(constituent_id) ON DELETE CASCADE,
  CONSTRAINT fk_awc_role FOREIGN KEY (role_id) REFERENCES constituent_roles(role_id) ON DELETE SET NULL,
  CONSTRAINT fk_awc_prefix FOREIGN KEY (prefix_id) REFERENCES constituent_prefixes(prefix_id) ON DELETE SET NULL,
  CONSTRAINT fk_awc_suffix FOREIGN KEY (suffix_id) REFERENCES constituent_suffixes(suffix_id) ON DELETE SET NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE art_work_geographies (
  art_work_geography_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  art_work_id INT UNSIGNED NOT NULL,
  geography_type_id INT UNSIGNED NOT NULL,
  country_id INT UNSIGNED NULL,
  state_id INT UNSIGNED NULL,
  county_id INT UNSIGNED NULL,
  city_id INT UNSIGNED NULL,
  region_id INT UNSIGNED NULL,
  subregion_id INT UNSIGNED NULL,
  locale_id INT UNSIGNED NULL,
  locus_id INT UNSIGNED NULL,
  excavation_id INT UNSIGNED NULL,
  river_id INT UNSIGNED NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_awg_art_work FOREIGN KEY (art_work_id) REFERENCES art_works(art_work_id) ON DELETE CASCADE,
  CONSTRAINT fk_awg_geography_type FOREIGN KEY (geography_type_id) REFERENCES geography_types(geography_type_id) ON DELETE RESTRICT,
  CONSTRAINT fk_awg_country FOREIGN KEY (country_id) REFERENCES countries(country_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_state FOREIGN KEY (state_id) REFERENCES states(state_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_county FOREIGN KEY (county_id) REFERENCES counties(county_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_city FOREIGN KEY (city_id) REFERENCES cities(city_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_region FOREIGN KEY (region_id) REFERENCES regions(region_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_subregion FOREIGN KEY (subregion_id) REFERENCES subregions(subregion_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_locale FOREIGN KEY (locale_id) REFERENCES locales(locale_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_locus FOREIGN KEY (locus_id) REFERENCES loci(locus_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_excavation FOREIGN KEY (excavation_id) REFERENCES excavations(excavation_id) ON DELETE SET NULL,
  CONSTRAINT fk_awg_river FOREIGN KEY (river_id) REFERENCES rivers(river_id) ON DELETE SET NULL,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);
```

---

## 3. ALTER TABLE

The original Laravel migrations introduced several column additions via `ALTER TABLE` statements. Those changes have been integrated directly into the `CREATE TABLE` definitions above. For completeness and idempotent execution, we include guarded `ALTER TABLE` statements that will only add the columns if they are missing.

```sql
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS order_status ENUM('pending','completed','cancelled','failed') NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS order_type ENUM('ticket','membership') NOT NULL DEFAULT 'ticket';
```

*No other structural alterations are required; all other schema changes are already represented in the CREATE statements.*

---

## 4. FOREIGN KEY

### One‑to‑Many FK (parent → child)

* `states.country_id → countries.country_id`
* `counties.state_id → states.state_id`
* `cities.county_id → counties.county_id`
* `subregions.region_id → regions.region_id`
* `user_profiles.user_id → users.user_id`
* `user_profiles.postal_code_id → postal_codes.postal_code_id`
* `guests` – no FK (stand‑alone)
* `carts.user_id → users.user_id` (ON DELETE SET NULL)
* `carts.guest_id → guests.guest_id` (ON DELETE SET NULL)
* `cart_groups.cart_id → carts.cart_id` (ON DELETE CASCADE)
* `visit_schedules.location_id → locations.location_id`
* `ticket_availabilities.visit_schedule_id → visit_schedules.visit_schedule_id`
* `ticket_availabilities.ticket_type_id → ticket_types.ticket_type_id`
* `orders.user_id → users.user_id` (ON DELETE SET NULL)
* `orders.guest_id → guests.guest_id` (ON DELETE SET NULL)
* `payments.order_id → orders.order_id` (ON DELETE CASCADE)
* `memberships.order_id → orders.order_id` (ON DELETE CASCADE)
* `memberships.user_id → users.user_id` (ON DELETE SET NULL)
* `tickets.order_id → orders.order_id` (ON DELETE CASCADE)
* `tickets.ticket_availability_id → ticket_availabilities.ticket_availability_id` (ON DELETE RESTRICT)
* `order_details.order_id → orders.order_id` (ON DELETE CASCADE)
* `order_details.ticket_id → tickets.ticket_id` (ON DELETE RESTRICT)
* `cart_items.cart_group_id → cart_groups.cart_group_id` (ON DELETE CASCADE)
* `cart_items.ticket_availability_id → ticket_availabilities.ticket_availability_id` (ON DELETE RESTRICT)
* `art_works.artist_id → artists.artist_id` (ON DELETE SET NULL)
* `art_works.department_id → departments.department_id`
* `art_works.object_type_id → object_types.object_type_id`
* `art_works.classification_id → classifications.classification_id`
* `art_works.medium_id → mediums.medium_id` (ON DELETE SET NULL)
* `art_works.location_id → locations.location_id` (ON DELETE SET NULL)
* `art_work_images.art_work_id → art_works.art_work_id` (ON DELETE CASCADE)
* `art_work_sims.art_work_id → art_works.art_work_id` (ON DELETE CASCADE)
* `art_work_constituents.art_work_id → art_works.art_work_id` (ON DELETE CASCADE)
* `art_work_constituents.constituent_id → constituents.constituent_id` (ON DELETE CASCADE)
* `art_work_constituents.role_id → constituent_roles.role_id` (ON DELETE SET NULL)
* `art_work_constituents.prefix_id → constituent_prefixes.prefix_id` (ON DELETE SET NULL)
* `art_work_constituents.suffix_id → constituent_suffixes.suffix_id` (ON DELETE SET NULL)
* `art_work_geographies.art_work_id → art_works.art_work_id` (ON DELETE CASCADE)
* `art_work_geographies.geography_type_id → geography_types.geography_type_id`
* `art_work_geographies.country_id → countries.country_id` (ON DELETE SET NULL)
* `…` (similar for state, county, city, region, subregion, locale, locus, excavation, river)

### Many‑to‑Many FK (pivot tables)

* `art_work_constituents` – links `art_works` ↔ `constituents`
* `art_work_geographies` – links `art_works` ↔ various geographic lookup tables

---

## 5. PRIMARY KEY

* **Auto‑increment PK** – every main table defines `... INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` (e.g., `users.user_id`).
* **Composite PK** – `art_work_constituents` uses `PRIMARY KEY (art_work_id, constituent_id)` to enforce uniqueness of the pair.

---

## 6. UNIQUE CONSTRAINT

* `users.email` – ensures a unique login address.
* `orders.order_code` – unique identifier for each order.
* `memberships.activation_token` – unique token for verifying membership activation.
* `ticket_availabilities` – composite unique on `(visit_schedule_id, ticket_type_id)` to prevent duplicate availability rows.

---

## 7. INDEX

No explicit `CREATE INDEX` statements are present; MySQL automatically creates indexes for all `PRIMARY KEY` and `FOREIGN KEY` columns, which satisfies indexing needs for the current schema.

---

## 8. CHECK CONSTRAINT

```sql
CONSTRAINT chk_carts_user_xor_guest CHECK ((user_id IS NOT NULL AND guest_id IS NULL) OR (user_id IS NULL AND guest_id IS NOT NULL))
CONSTRAINT chk_orders_user_xor_guest CHECK ((user_id IS NOT NULL AND guest_id IS NULL) OR (user_id IS NULL AND guest_id IS NOT NULL))
```
**Business logic** – guarantees that a cart or order is owned **either** by a registered user **or** a guest, never both.

---

## 9. CASCADE / SET NULL / RESTRICT Rules

| Action | Example | Reason |
|--------|---------|--------|
| **ON DELETE CASCADE** | `fk_cart_groups_cart` – deleting a cart automatically removes its groups. | Guarantees no orphaned child rows. |
| **ON DELETE SET NULL** | `fk_carts_user` – if a user is removed, the cart remains but loses its owner reference. | Preserves cart data for possible guest checkout. |
| **ON DELETE RESTRICT** | `fk_art_work_department` – a department cannot be removed while artworks reference it. | Prevents accidental loss of core data. |

---

## 10. DROP TABLE (Rollback)

For a clean rollback, the following statements drop tables in reverse dependency order. They are prefixed with `IF EXISTS` to avoid errors if a table has already been removed.

```sql
DROP TABLE IF EXISTS art_work_geographies;
DROP TABLE IF EXISTS art_work_constituents;
DROP TABLE IF EXISTS art_work_sims;
DROP TABLE IF EXISTS art_work_images;
DROP TABLE IF EXISTS constituents;
DROP TABLE IF EXISTS ticket_availabilities;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS memberships;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS ticket_types;
DROP TABLE IF EXISTS visit_schedules;
DROP TABLE IF EXISTS cart_groups;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS guests;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS postal_codes;
DROP TABLE IF EXISTS artists;
DROP TABLE IF EXISTS constituent_roles;
DROP TABLE IF EXISTS constituent_prefixes;
DROP TABLE IF EXISTS constituent_suffixes;
DROP TABLE IF EXISTS mediums;
DROP TABLE IF EXISTS classifications;
DROP TABLE IF EXISTS object_types;
DROP TABLE IF EXISTS art_works;
DROP TABLE IF EXISTS regions;
DROP TABLE IF EXISTS subregions;
DROP TABLE IF EXISTS locales;
DROP TABLE IF EXISTS loci;
DROP TABLE IF EXISTS excavations;
DROP TABLE IF EXISTS rivers;
DROP TABLE IF EXISTS geography_types;
DROP TABLE IF EXISTS cities;
DROP TABLE IF EXISTS counties;
DROP TABLE IF EXISTS states;
DROP TABLE IF EXISTS countries;
```

---

## 11. ENGINE / CHARSET / COLLATION

Every table declares:
```sql
ENGINE=InnoDB,
CHARSET=utf8mb4,
COLLATE=utf8mb4_unicode_ci
```
* **ENGINE=InnoDB** – provides ACID‑compliant transactional support and foreign‑key enforcement.
* **CHARSET=utf8mb4** – full Unicode support, essential for multilingual art metadata.
* **COLLATE=utf8mb4_unicode_ci** – case‑insensitive, language‑aware ordering.

---

*Generated directly from `database_schema_reconstructed.sql` and the analysis document; no reverse‑engineering or migration rescanning was performed.*
