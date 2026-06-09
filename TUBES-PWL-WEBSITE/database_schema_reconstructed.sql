-- database_schema_reconstructed.sql

-- 1. Create database and set context
CREATE DATABASE IF NOT EXISTS `tubes_sbd_website` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tubes_sbd_website`;

-- Disable foreign key checks during creation
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------
-- 2. Lookup tables (master/reference data)
-- ---------------------------------------------------
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

-- ---------------------------------------------------
-- 3. Core entity tables
-- ---------------------------------------------------
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

CREATE TABLE art_work_measurements (
  art_work_measurement_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  art_work_id INT UNSIGNED NOT NULL,
  measurement_type VARCHAR(50) NOT NULL,
  measurement_name VARCHAR(100) NOT NULL,
  measurement_value DECIMAL(10,4) NOT NULL,
  measurement_unit VARCHAR(20) NOT NULL,
  display_order INT UNSIGNED DEFAULT 1,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_awm_art_work FOREIGN KEY (art_work_id) REFERENCES art_works(art_work_id) ON DELETE CASCADE,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE art_work_references (
  art_work_reference_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  art_work_id INT UNSIGNED NOT NULL,
  reference_text TEXT NOT NULL,
  display_order INT UNSIGNED DEFAULT 1,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_awref_art_work FOREIGN KEY (art_work_id) REFERENCES art_works(art_work_id) ON DELETE CASCADE,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

CREATE TABLE art_work_exhibition_histories (
  art_work_exhibition_history_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  art_work_id INT UNSIGNED NOT NULL,
  exhibition_title VARCHAR(255) NOT NULL,
  venue_name VARCHAR(255) NULL,
  city_name VARCHAR(255) NULL,
  exhibition_date_display VARCHAR(100) NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  catalogue_reference VARCHAR(255) NULL,
  exhibition_notes TEXT NULL,
  display_order INT UNSIGNED DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_awexh_art_work FOREIGN KEY (art_work_id) REFERENCES art_works(art_work_id) ON DELETE CASCADE,
  ENGINE=InnoDB,
  CHARSET=utf8mb4,
  COLLATE=utf8mb4_unicode_ci
);

-- ---------------------------------------------------
-- 4. Enable foreign key checks after creation
-- ---------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;

-- End of script
