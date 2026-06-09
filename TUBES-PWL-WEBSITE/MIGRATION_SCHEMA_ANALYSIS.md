# Complete Migration Schema Analysis - 57 Files

## EXECUTIVE SUMMARY

**CRITICAL FINDING: Major Type Inconsistency in Database Schema**

- **5 tables use BIGINT primary keys** (via `$table->id()`)
- **45+ tables use INT primary keys** (via `$table->increments()`)
- **Multiple foreign key mismatches** where INT columns reference BIGINT primary keys

---

## PRIMARY KEY DISTRIBUTION

### BIGINT Primary Keys (5 tables) - Using `id('column_name')`

1. **locations** - `location_id` (BIGINT)
2. **departments** - `department_id` (BIGINT)
3. **object_types** - `type_id` (BIGINT)
4. **repositories** - `repository_id` (BIGINT)
5. **classifications** - `classification_id` (BIGINT)

### INT Primary Keys (45+ tables) - Using `increments('column_name')`

- nationalities, constituent_roles, constituent_prefixes, constituent_suffixes
- materials, tags, cultures, periods, dynasties, reigns, portfolios
- geography_types, countries, measurement_elements, measurement_types, measurement_units
- excavations, rivers, ticket_types, constituents, users, guests, postal_codes
- user_profiles, states, regions, counties, cities, subregions, locales, loci
- art_works, visit_schedules, orders, art_work_constituents, art_work_images
- art_work_geographies, art_work_measurements, cart_groups, ticket_availability
- tickets, payments, cart_items, carts

### STRING Primary Keys (3 tables)

- **cache** - `key` (string)
- **cache_locks** - `key` (string)
- **sessions** - `id` (string)

---

## DETAILED MIGRATION BREAKDOWN

### 2026_04_18_142737_create_cache_table.php

**Tables Created:**

- **cache**: PK `key` (string), Method: string()->primary()
- **cache_locks**: PK `key` (string), Method: string()->primary()

---

### 2026_04_18_143629_create_sessions_table.php

**Table:** sessions

- **PK:** `id` (string), Method: string()->primary()
- **Foreign Keys:**
    - `user_id` (unsignedInteger, nullable) → users(user_id) [INT]

---

### 2026_05_07_000001_create_locations_table.php

**Table:** locations

- **PK:** `location_id` (BIGINT), Method: id('location_id')
- **Foreign Keys:** None
- **Note:** Referenced by 5+ tables with INT columns!

---

### 2026_05_07_000002_create_departments_table.php

**Table:** departments

- **PK:** `department_id` (BIGINT), Method: id('department_id')
- **Foreign Keys:** None
- **Note:** Referenced by art_works with INT column

---

### 2026_05_07_000003_create_object_types_table.php

**Table:** object_types

- **PK:** `type_id` (BIGINT), Method: id('type_id')
- **Foreign Keys:** None
- **Note:** Referenced by art_works with INT column

---

### 2026_05_07_000004_create_repositories_table.php

**Table:** repositories

- **PK:** `repository_id` (BIGINT), Method: id('repository_id')
- **Foreign Keys:** None
- **Note:** Referenced by art_works with INT column

---

### 2026_05_07_000005_create_classifications_table.php

**Table:** classifications

- **PK:** `classification_id` (BIGINT), Method: id('classification_id')
- **Foreign Keys:** None
- **Note:** Referenced by art_works with INT column

---

### 2026_05_07_000006_create_nationalities_table.php

**Table:** nationalities

- **PK:** `nationality_id` (INT), Method: increments('nationality_id')
- **Foreign Keys:** None

---

### 2026_05_07_000007_create_constituent_roles_table.php

**Table:** constituent_roles

- **PK:** `role_id` (INT), Method: increments('role_id')
- **Foreign Keys:** None

---

### 2026_05_07_000008_create_constituent_prefixes_table.php

**Table:** constituent_prefixes

- **PK:** `prefix_id` (INT), Method: increments('prefix_id')
- **Foreign Keys:** None

---

### 2026_05_07_000009_create_constituent_suffixes_table.php

**Table:** constituent_suffixes

- **PK:** `suffix_id` (INT), Method: increments('suffix_id')
- **Foreign Keys:** None

---

### 2026_05_07_000010_create_materials_table.php

**Table:** materials

- **PK:** `material_id` (INT), Method: increments('material_id')
- **Foreign Keys:** None

---

### 2026_05_07_000011_create_tags_table.php

**Table:** tags

- **PK:** `tag_id` (INT), Method: increments('tag_id')
- **Foreign Keys:** None

---

### 2026_05_07_000012_create_cultures_table.php

**Table:** cultures

- **PK:** `culture_id` (INT), Method: increments('culture_id')
- **Foreign Keys:** None

---

### 2026_05_07_000013_create_periods_table.php

**Table:** periods

- **PK:** `period_id` (INT), Method: increments('period_id')
- **Foreign Keys:** None

---

### 2026_05_07_000014_create_dynasties_table.php

**Table:** dynasties

- **PK:** `dynasty_id` (INT), Method: increments('dynasty_id')
- **Foreign Keys:** None

---

### 2026_05_07_000015_create_reigns_table.php

**Table:** reigns

- **PK:** `reign_id` (INT), Method: increments('reign_id')
- **Foreign Keys:** None

---

### 2026_05_07_000016_create_portfolios_table.php

**Table:** portfolios

- **PK:** `portfolio_id` (INT), Method: increments('portfolio_id')
- **Foreign Keys:** None

---

### 2026_05_07_000017_create_geography_types_table.php

**Table:** geography_types

- **PK:** `geography_type_id` (INT), Method: increments('geography_type_id')
- **Foreign Keys:** None

---

### 2026_05_07_000018_create_countries_table.php

**Table:** countries

- **PK:** `country_id` (INT), Method: increments('country_id')
- **Foreign Keys:** None

---

### 2026_05_07_000019_create_measurement_elements_table.php

**Table:** measurement_elements

- **PK:** `measurement_element_id` (INT), Method: increments('measurement_element_id')
- **Foreign Keys:** None

---

### 2026_05_07_000020_create_measurement_types_table.php

**Table:** measurement_types

- **PK:** `measurement_type_id` (INT), Method: increments('measurement_type_id')
- **Foreign Keys:** None

---

### 2026_05_07_000021_create_measurement_units_table.php

**Table:** measurement_units

- **PK:** `measurement_unit_id` (INT), Method: increments('measurement_unit_id')
- **Foreign Keys:** None

---

### 2026_05_07_000022_create_excavations_table.php

**Table:** excavations

- **PK:** `excavation_id` (INT), Method: increments('excavation_id')
- **Foreign Keys:** None

---

### 2026_05_07_000023_create_rivers_table.php

**Table:** rivers

- **PK:** `river_id` (INT), Method: increments('river_id')
- **Foreign Keys:** None

---

### 2026_05_07_000024_create_ticket_types_table.php

**Table:** ticket_types

- **PK:** `ticket_type_id` (INT), Method: increments('ticket_type_id')
- **Foreign Keys:** None

---

### 2026_05_07_000025_create_constituents_table.php

**Table:** constituents

- **PK:** `constituent_id` (INT), Method: increments('constituent_id')
- **Foreign Keys:** None

---

### 2026_05_07_000026_create_users_table.php

**Table:** users

- **PK:** `user_id` (INT), Method: increments('user_id')
- **Foreign Keys:** None

---

### 2026_05_07_000027_create_guests_table.php

**Table:** guests

- **PK:** `guest_id` (INT), Method: increments('guest_id')
- **Foreign Keys:** None

---

### 2026_05_07_000028_create_postal_codes_table.php

**Table:** postal_codes

- **PK:** `postal_code_id` (INT), Method: increments('postal_code_id')
- **Foreign Keys:** None

---

### 2026_05_07_000029_create_user_profiles_table.php

**Table:** user_profiles

- **PK:** `user_profile_id` (INT), Method: increments('user_profile_id')
- **Foreign Keys:**
    - `user_id` (unsignedInteger, unique) → users(user_id) [INT → INT] ✓
    - `postal_code_id` (unsignedInteger) → postal_codes(postal_code_id) [INT → INT] ✓

---

### 2026_05_07_000030_create_states_table.php

**Table:** states

- **PK:** `state_id` (INT), Method: increments('state_id')
- **Foreign Keys:**
    - `country_id` (unsignedInteger) → countries(country_id) [INT → INT] ✓

---

### 2026_05_07_000031_create_regions_table.php

**Table:** regions

- **PK:** `region_id` (INT), Method: increments('region_id')
- **Foreign Keys:**
    - `country_id` (unsignedInteger) → countries(country_id) [INT → INT] ✓

---

### 2026_05_07_000032_create_counties_table.php

**Table:** counties

- **PK:** `county_id` (INT), Method: increments('county_id')
- **Foreign Keys:**
    - `state_id` (unsignedInteger) → states(state_id) [INT → INT] ✓

---

### 2026_05_07_000033_create_cities_table.php

**Table:** cities

- **PK:** `city_id` (INT), Method: increments('city_id')
- **Foreign Keys:**
    - `state_id` (unsignedInteger) → states(state_id) [INT → INT] ✓

---

### 2026_05_07_000034_create_subregions_table.php

**Table:** subregions

- **PK:** `subregion_id` (INT), Method: increments('subregion_id')
- **Foreign Keys:**
    - `region_id` (unsignedInteger) → regions(region_id) [INT → INT] ✓

---

### 2026_05_07_000035_create_locales_table.php

**Table:** locales

- **PK:** `locale_id` (INT), Method: increments('locale_id')
- **Foreign Keys:**
    - `subregion_id` (unsignedInteger) → subregions(subregion_id) [INT → INT] ✓

---

### 2026_05_07_000036_create_loci_table.php

**Table:** loci

- **PK:** `locus_id` (INT), Method: increments('locus_id')
- **Foreign Keys:**
    - `locale_id` (unsignedInteger) → locales(locale_id) [INT → INT] ✓

---

## 🚨 CRITICAL MISMATCH: art_works table

### 2026_05_07_000037_create_art_works_table.php

**Table:** art_works

- **PK:** `art_work_id` (INT), Method: increments('art_work_id')
- **Foreign Keys - TYPE MISMATCHES DETECTED:**
    - `repository_id` (unsignedInteger) → repositories(repository_id) [INT → BIGINT] ❌ **MISMATCH**
    - `department_id` (unsignedInteger) → departments(department_id) [INT → BIGINT] ❌ **MISMATCH**
    - `type_id` (unsignedInteger) → object_types(type_id) [INT → BIGINT] ❌ **MISMATCH**
    - `classification_id` (unsignedInteger) → classifications(classification_id) [INT → BIGINT] ❌ **MISMATCH**
    - `location_id` (unsignedInteger) → locations(location_id) [INT → BIGINT] ❌ **MISMATCH**

---

### 2026_05_07_000038_create_carts_table.php

**Table:** carts

- **PK:** `cart_id` (INT), Method: increments('cart_id')
- **Foreign Keys:**
    - `user_id` (unsignedInteger, nullable) → users(user_id) [INT → INT] ✓
    - `guest_id` (unsignedInteger, nullable) → guests(guest_id) [INT → INT] ✓
- **Constraint:** XOR check on (user_id, guest_id)

---

### 2026_05_07_000039_create_visit_schedules_table.php

**Table:** visit_schedules

- **PK:** `visit_schedule_id` (INT), Method: increments('visit_schedule_id')
- **Foreign Keys:**
    - `location_id` (unsignedInteger) → locations(location_id) [INT → BIGINT] ❌ **MISMATCH**

---

### 2026_05_07_000040_create_orders_table.php

**Table:** orders

- **PK:** `order_id` (INT), Method: increments('order_id')
- **Foreign Keys:**
    - `user_id` (unsignedInteger, nullable) → users(user_id) [INT → INT] ✓
    - `guest_id` (unsignedInteger, nullable) → guests(guest_id) [INT → INT] ✓
- **Constraint:** XOR check on (user_id, guest_id)

---

### 2026_05_07_000041_create_constituent_nationalities_table.php

**Table:** constituent_nationalities (Many-to-Many)

- **PK:** Composite (constituent_id, nationality_id) - No autoincrement
- **Foreign Keys:**
    - `constituent_id` (unsignedInteger) → constituents(constituent_id) [INT → INT] ✓
    - `nationality_id` (unsignedInteger) → nationalities(nationality_id) [INT → INT] ✓

---

### 2026_05_07_000042_create_art_work_materials_table.php

**Table:** art_work_materials (Many-to-Many)

- **PK:** Composite (art_work_id, material_id) - No autoincrement
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `material_id` (unsignedInteger) → materials(material_id) [INT → INT] ✓

---

### 2026_05_07_000043_create_art_work_constituents_table.php

**Table:** art_work_constituents

- **PK:** `art_work_constituent_id` (INT), Method: increments('art_work_constituent_id')
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `constituent_id` (unsignedInteger) → constituents(constituent_id) [INT → INT] ✓
    - `role_id` (unsignedInteger) → constituent_roles(role_id) [INT → INT] ✓
    - `prefix_id` (unsignedInteger, nullable) → constituent_prefixes(prefix_id) [INT → INT] ✓
    - `suffix_id` (unsignedInteger, nullable) → constituent_suffixes(suffix_id) [INT → INT] ✓

---

### 2026_05_07_000044_create_art_work_images_table.php

**Table:** art_work_images

- **PK:** `image_id` (INT), Method: increments('image_id')
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
- **Constraint:** Unique index on (CASE WHEN is_primary = 1 THEN art_work_id ELSE NULL END)

---

### 2026_05_07_000045_create_art_work_tags_table.php

**Table:** art_work_tags (Many-to-Many)

- **PK:** Composite (art_work_id, tag_id) - No autoincrement
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `tag_id` (unsignedInteger) → tags(tag_id) [INT → INT] ✓

---

### 2026_05_07_000046_create_art_work_cultures_table.php

**Table:** art_work_cultures (Many-to-Many)

- **PK:** Composite (art_work_id, culture_id) - No autoincrement
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `culture_id` (unsignedInteger) → cultures(culture_id) [INT → INT] ✓

---

### 2026_05_07_000047_create_art_work_periods_table.php

**Table:** art_work_periods (Many-to-Many)

- **PK:** Composite (art_work_id, period_id) - No autoincrement
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `period_id` (unsignedInteger) → periods(period_id) [INT → INT] ✓

---

### 2026_05_07_000048_create_art_work_dynasties_table.php

**Table:** art_work_dynasties (Many-to-Many)

- **PK:** Composite (art_work_id, dynasty_id) - No autoincrement
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `dynasty_id` (unsignedInteger) → dynasties(dynasty_id) [INT → INT] ✓

---

### 2026_05_07_000049_create_art_work_reigns_table.php

**Table:** art_work_reigns (Many-to-Many)

- **PK:** Composite (art_work_id, reign_id) - No autoincrement
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `reign_id` (unsignedInteger) → reigns(reign_id) [INT → INT] ✓

---

### 2026_05_07_000050_create_art_work_portfolios_table.php

**Table:** art_work_portfolios (Many-to-Many)

- **PK:** Composite (art_work_id, portfolio_id) - No autoincrement
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `portfolio_id` (unsignedInteger) → portfolios(portfolio_id) [INT → INT] ✓

---

### 2026_05_07_000051_create_art_work_geographies_table.php

**Table:** art_work_geographies

- **PK:** `art_work_geography_id` (INT), Method: increments('art_work_geography_id')
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `geography_type_id` (unsignedInteger, nullable) → geography_types(geography_type_id) [INT → INT] ✓
    - `country_id` (unsignedInteger, nullable) → countries(country_id) [INT → INT] ✓
    - `state_id` (unsignedInteger, nullable) → states(state_id) [INT → INT] ✓
    - `county_id` (unsignedInteger, nullable) → counties(county_id) [INT → INT] ✓
    - `city_id` (unsignedInteger, nullable) → cities(city_id) [INT → INT] ✓
    - `region_id` (unsignedInteger, nullable) → regions(region_id) [INT → INT] ✓
    - `subregion_id` (unsignedInteger, nullable) → subregions(subregion_id) [INT → INT] ✓
    - `locale_id` (unsignedInteger, nullable) → locales(locale_id) [INT → INT] ✓
    - `locus_id` (unsignedInteger, nullable) → loci(locus_id) [INT → INT] ✓
    - `excavation_id` (unsignedInteger, nullable) → excavations(excavation_id) [INT → INT] ✓
    - `river_id` (unsignedInteger, nullable) → rivers(river_id) [INT → INT] ✓

---

### 2026_05_07_000052_create_art_work_measurements_table.php

**Table:** art_work_measurements

- **PK:** `art_work_measurement_id` (INT), Method: increments('art_work_measurement_id')
- **Foreign Keys:**
    - `art_work_id` (unsignedInteger) → art_works(art_work_id) [INT → INT] ✓
    - `measurement_element_id` (unsignedInteger, nullable) → measurement_elements(measurement_element_id) [INT → INT] ✓
    - `measurement_type_id` (unsignedInteger, nullable) → measurement_types(measurement_type_id) [INT → INT] ✓
    - `measurement_unit_id` (unsignedInteger, nullable) → measurement_units(measurement_unit_id) [INT → INT] ✓

---

### 2026_05_07_000053_create_cart_groups_table.php

**Table:** cart_groups

- **PK:** `cart_group_id` (INT), Method: increments('cart_group_id')
- **Foreign Keys:**
    - `cart_id` (unsignedInteger) → carts(cart_id) [INT → INT] ✓ (onDelete: cascade)

---

### 2026_05_07_000054_create_ticket_availability_table.php

**Table:** ticket_availability

- **PK:** `ticket_availability_id` (INT), Method: increments('ticket_availability_id')
- **Foreign Keys:**
    - `ticket_type_id` (unsignedInteger) → ticket_types(ticket_type_id) [INT → INT] ✓
    - `visit_schedule_id` (unsignedInteger) → visit_schedules(visit_schedule_id) [INT → INT] ✓

---

### 2026_05_07_000055_create_tickets_table.php

**Table:** tickets

- **PK:** `ticket_id` (INT), Method: increments('ticket_id')
- **Foreign Keys:**
    - `order_id` (unsignedInteger) → orders(order_id) [INT → INT] ✓
    - `ticket_availability_id` (unsignedInteger) → ticket_availability(ticket_availability_id) [INT → INT] ✓

---

### 2026_05_07_000056_create_payments_table.php

**Table:** payments

- **PK:** `payment_id` (INT), Method: increments('payment_id')
- **Foreign Keys:**
    - `order_id` (unsignedInteger) → orders(order_id) [INT → INT] ✓

---

### 2026_05_07_000057_create_cart_items_table.php

**Table:** cart_items

- **PK:** `cart_item_id` (INT), Method: increments('cart_item_id')
- **Foreign Keys:**
    - `cart_group_id` (unsignedInteger) → cart_groups(cart_group_id) [INT → INT] ✓ (onDelete: cascade)
    - `ticket_availability_id` (unsignedInteger) → ticket_availability(ticket_availability_id) [INT → INT] ✓
- **Constraint:** quantity > 0

---

## 🚨 CRITICAL FINDINGS: TYPE MISMATCHES

### All Type Mismatches (INT Foreign Key → BIGINT Primary Key):

1. **art_works table (5 mismatches):**
    - `repository_id` (INT) → repositories(repository_id) [BIGINT]
    - `department_id` (INT) → departments(department_id) [BIGINT]
    - `type_id` (INT) → object_types(type_id) [BIGINT]
    - `classification_id` (INT) → classifications(classification_id) [BIGINT]
    - `location_id` (INT) → locations(location_id) [BIGINT]

2. **visit_schedules table (1 mismatch):**
    - `location_id` (INT) → locations(location_id) [BIGINT]

### Total Type Mismatches: 6 Foreign Keys

---

## RECOMMENDATIONS

### Immediate Actions Required:

1. **Option A: Standardize on INT (Recommended if data fits)**
    - Convert 5 BIGINT tables to INT using increments()
    - All foreign keys are already INT, so they'll match
    - Change files:
        - 2026_05_07_000001 (locations)
        - 2026_05_07_000002 (departments)
        - 2026_05_07_000003 (object_types)
        - 2026_05_07_000004 (repositories)
        - 2026_05_07_000005 (classifications)

2. **Option B: Standardize on BIGINT**
    - Convert all INT primary keys to BIGINT using id()
    - Update 45+ tables to use id() method
    - Update all foreign key columns to unsignedBigInteger

3. **Option C: Fix Only Affected Tables**
    - Keep BIGINT for base lookup tables (locations, departments, etc.)
    - Update foreign key columns in art_works and visit_schedules to unsignedBigInteger

### Method Usage Notes:

- `$table->id('name')` → Creates BIGINT auto-increment (unsigned)
- `$table->increments('name')` → Creates INT auto-increment (unsigned)
- `$table->bigIncrements('name')` → Creates BIGINT auto-increment (unsigned)
- `$table->foreignId('name')` → Creates BIGINT by default (if using id())
- `$table->unsignedBigInteger('name')` → Creates BIGINT (no auto-increment)
- `$table->unsignedInteger('name')` → Creates INT (no auto-increment)

---

## STATISTICS

| Category                     | Count |
| ---------------------------- | ----- |
| Total Migration Files        | 57    |
| Total Tables Created         | 59    |
| BIGINT Primary Keys          | 5     |
| INT Primary Keys             | 45+   |
| STRING Primary Keys          | 3     |
| Composite Primary Keys (M2M) | 10    |
| Tables with Foreign Keys     | 28    |
| Type Mismatches Found        | 6     |
| Mismatched FK Columns        | 6     |
| XOR Check Constraints        | 2     |
| Cascade Delete Constraints   | 2     |

---

## Files Affected by Type Mismatches

- 2026_05_07_000037_create_art_works_table.php (5 mismatches)
- 2026_05_07_000039_create_visit_schedules_table.php (1 mismatch)
