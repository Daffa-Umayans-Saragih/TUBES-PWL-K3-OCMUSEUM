# Migration Quick Reference Table

| #   | Migration File                                               | Table Name                | PK Column               | PK Type | PK Method               | FK Count | Type Issues                             |
| --- | ------------------------------------------------------------ | ------------------------- | ----------------------- | ------- | ----------------------- | -------- | --------------------------------------- |
| 1   | 2026_04_18_142737_create_cache_table.php                     | cache                     | key                     | string  | string()->primary()     | 0        | None                                    |
| 2   | 2026_04_18_142737_create_cache_table.php                     | cache_locks               | key                     | string  | string()->primary()     | 0        | None                                    |
| 3   | 2026_04_18_143629_create_sessions_table.php                  | sessions                  | id                      | string  | string()->primary()     | 1        | None                                    |
| 4   | 2026_05_07_000001_create_locations_table.php                 | locations                 | location_id             | BIGINT  | id('location_id')       | 0        | Referenced by INT cols                  |
| 5   | 2026_05_07_000002_create_departments_table.php               | departments               | department_id           | BIGINT  | id('department_id')     | 0        | Referenced by INT cols                  |
| 6   | 2026_05_07_000003_create_object_types_table.php              | object_types              | type_id                 | BIGINT  | id('type_id')           | 0        | Referenced by INT cols                  |
| 7   | 2026_05_07_000004_create_repositories_table.php              | repositories              | repository_id           | BIGINT  | id('repository_id')     | 0        | Referenced by INT cols                  |
| 8   | 2026_05_07_000005_create_classifications_table.php           | classifications           | classification_id       | BIGINT  | id('classification_id') | 0        | Referenced by INT cols                  |
| 9   | 2026_05_07_000006_create_nationalities_table.php             | nationalities             | nationality_id          | INT     | increments()            | 0        | None                                    |
| 10  | 2026_05_07_000007_create_constituent_roles_table.php         | constituent_roles         | role_id                 | INT     | increments()            | 0        | None                                    |
| 11  | 2026_05_07_000008_create_constituent_prefixes_table.php      | constituent_prefixes      | prefix_id               | INT     | increments()            | 0        | None                                    |
| 12  | 2026_05_07_000009_create_constituent_suffixes_table.php      | constituent_suffixes      | suffix_id               | INT     | increments()            | 0        | None                                    |
| 13  | 2026_05_07_000010_create_materials_table.php                 | materials                 | material_id             | INT     | increments()            | 0        | None                                    |
| 14  | 2026_05_07_000011_create_tags_table.php                      | tags                      | tag_id                  | INT     | increments()            | 0        | None                                    |
| 15  | 2026_05_07_000012_create_cultures_table.php                  | cultures                  | culture_id              | INT     | increments()            | 0        | None                                    |
| 16  | 2026_05_07_000013_create_periods_table.php                   | periods                   | period_id               | INT     | increments()            | 0        | None                                    |
| 17  | 2026_05_07_000014_create_dynasties_table.php                 | dynasties                 | dynasty_id              | INT     | increments()            | 0        | None                                    |
| 18  | 2026_05_07_000015_create_reigns_table.php                    | reigns                    | reign_id                | INT     | increments()            | 0        | None                                    |
| 19  | 2026_05_07_000016_create_portfolios_table.php                | portfolios                | portfolio_id            | INT     | increments()            | 0        | None                                    |
| 20  | 2026_05_07_000017_create_geography_types_table.php           | geography_types           | geography_type_id       | INT     | increments()            | 0        | None                                    |
| 21  | 2026_05_07_000018_create_countries_table.php                 | countries                 | country_id              | INT     | increments()            | 0        | None                                    |
| 22  | 2026_05_07_000019_create_measurement_elements_table.php      | measurement_elements      | measurement_element_id  | INT     | increments()            | 0        | None                                    |
| 23  | 2026_05_07_000020_create_measurement_types_table.php         | measurement_types         | measurement_type_id     | INT     | increments()            | 0        | None                                    |
| 24  | 2026_05_07_000021_create_measurement_units_table.php         | measurement_units         | measurement_unit_id     | INT     | increments()            | 0        | None                                    |
| 25  | 2026_05_07_000022_create_excavations_table.php               | excavations               | excavation_id           | INT     | increments()            | 0        | None                                    |
| 26  | 2026_05_07_000023_create_rivers_table.php                    | rivers                    | river_id                | INT     | increments()            | 0        | None                                    |
| 27  | 2026_05_07_000024_create_ticket_types_table.php              | ticket_types              | ticket_type_id          | INT     | increments()            | 0        | None                                    |
| 28  | 2026_05_07_000025_create_constituents_table.php              | constituents              | constituent_id          | INT     | increments()            | 0        | None                                    |
| 29  | 2026_05_07_000026_create_users_table.php                     | users                     | user_id                 | INT     | increments()            | 0        | None                                    |
| 30  | 2026_05_07_000027_create_guests_table.php                    | guests                    | guest_id                | INT     | increments()            | 0        | None                                    |
| 31  | 2026_05_07_000028_create_postal_codes_table.php              | postal_codes              | postal_code_id          | INT     | increments()            | 0        | None                                    |
| 32  | 2026_05_07_000029_create_user_profiles_table.php             | user_profiles             | user_profile_id         | INT     | increments()            | 2        | None (INT→INT)                          |
| 33  | 2026_05_07_000030_create_states_table.php                    | states                    | state_id                | INT     | increments()            | 1        | None (INT→INT)                          |
| 34  | 2026_05_07_000031_create_regions_table.php                   | regions                   | region_id               | INT     | increments()            | 1        | None (INT→INT)                          |
| 35  | 2026_05_07_000032_create_counties_table.php                  | counties                  | county_id               | INT     | increments()            | 1        | None (INT→INT)                          |
| 36  | 2026_05_07_000033_create_cities_table.php                    | cities                    | city_id                 | INT     | increments()            | 1        | None (INT→INT)                          |
| 37  | 2026_05_07_000034_create_subregions_table.php                | subregions                | subregion_id            | INT     | increments()            | 1        | None (INT→INT)                          |
| 38  | 2026_05_07_000035_create_locales_table.php                   | locales                   | locale_id               | INT     | increments()            | 1        | None (INT→INT)                          |
| 39  | 2026_05_07_000036_create_loci_table.php                      | loci                      | locus_id                | INT     | increments()            | 1        | None (INT→INT)                          |
| 40  | 2026_05_07_000037_create_art_works_table.php                 | art_works                 | art_work_id             | INT     | increments()            | 5        | **5 MISMATCHES: INT→BIGINT** ⚠️         |
| 41  | 2026_05_07_000038_create_carts_table.php                     | carts                     | cart_id                 | INT     | increments()            | 2        | None (INT→INT) + XOR check              |
| 42  | 2026_05_07_000039_create_visit_schedules_table.php           | visit_schedules           | visit_schedule_id       | INT     | increments()            | 1        | **1 MISMATCH: INT→BIGINT** ⚠️           |
| 43  | 2026_05_07_000040_create_orders_table.php                    | orders                    | order_id                | INT     | increments()            | 2        | None (INT→INT) + XOR check              |
| 44  | 2026_05_07_000041_create_constituent_nationalities_table.php | constituent_nationalities | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 45  | 2026_05_07_000042_create_art_work_materials_table.php        | art_work_materials        | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 46  | 2026_05_07_000043_create_art_work_constituents_table.php     | art_work_constituents     | art_work_constituent_id | INT     | increments()            | 5        | None (INT→INT)                          |
| 47  | 2026_05_07_000044_create_art_work_images_table.php           | art_work_images           | image_id                | INT     | increments()            | 1        | None (INT→INT) + Partial unique index   |
| 48  | 2026_05_07_000045_create_art_work_tags_table.php             | art_work_tags             | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 49  | 2026_05_07_000046_create_art_work_cultures_table.php         | art_work_cultures         | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 50  | 2026_05_07_000047_create_art_work_periods_table.php          | art_work_periods          | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 51  | 2026_05_07_000048_create_art_work_dynasties_table.php        | art_work_dynasties        | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 52  | 2026_05_07_000049_create_art_work_reigns_table.php           | art_work_reigns           | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 53  | 2026_05_07_000050_create_art_work_portfolios_table.php       | art_work_portfolios       | (composite)             | —       | —                       | 2        | None (M2M, INT→INT)                     |
| 54  | 2026_05_07_000051_create_art_work_geographies_table.php      | art_work_geographies      | art_work_geography_id   | INT     | increments()            | 12       | None (INT→INT, many nullable)           |
| 55  | 2026_05_07_000052_create_art_work_measurements_table.php     | art_work_measurements     | art_work_measurement_id | INT     | increments()            | 4        | None (INT→INT, some nullable)           |
| 56  | 2026_05_07_000053_create_cart_groups_table.php               | cart_groups               | cart_group_id           | INT     | increments()            | 1        | None (INT→INT) + cascade delete         |
| 57  | 2026_05_07_000054_create_ticket_availability_table.php       | ticket_availability       | ticket_availability_id  | INT     | increments()            | 2        | None (INT→INT)                          |
| 58  | 2026_05_07_000055_create_tickets_table.php                   | tickets                   | ticket_id               | INT     | increments()            | 2        | None (INT→INT)                          |
| 59  | 2026_05_07_000056_create_payments_table.php                  | payments                  | payment_id              | INT     | increments()            | 1        | None (INT→INT)                          |
| 60  | 2026_05_07_000057_create_cart_items_table.php                | cart_items                | cart_item_id            | INT     | increments()            | 2        | None (INT→INT) + cascade delete + check |

## Summary Statistics

| Metric                        | Count |
| ----------------------------- | ----- |
| **Migration Files**           | 57    |
| **Total Tables**              | 60    |
| **BIGINT PKs**                | 5     |
| **INT PKs**                   | 45+   |
| **STRING PKs**                | 3     |
| **Composite/M2M PKs**         | 10    |
| **Tables with Foreign Keys**  | 30+   |
| **Foreign Key Relationships** | 100+  |
| **Type Mismatch Issues**      | 6     |
| **Files with Mismatches**     | 2     |

## Critical Issues

### 🚨 Affected Files (6 Type Mismatches):

1. **[2026_05_07_000037_create_art_works_table.php](2026_05_07_000037_create_art_works_table.php) - 5 mismatches**
    - `repository_id` (INT) → repositories (BIGINT)
    - `department_id` (INT) → departments (BIGINT)
    - `type_id` (INT) → object_types (BIGINT)
    - `classification_id` (INT) → classifications (BIGINT)
    - `location_id` (INT) → locations (BIGINT)

2. **[2026_05_07_000039_create_visit_schedules_table.php](2026_05_07_000039_create_visit_schedules_table.php) - 1 mismatch**
    - `location_id` (INT) → locations (BIGINT)

## Base Lookup Tables (BIGINT - Should Match References)

| Table           | PK Column         | Type   | Referenced By                          |
| --------------- | ----------------- | ------ | -------------------------------------- |
| locations       | location_id       | BIGINT | art_works (INT), visit_schedules (INT) |
| departments     | department_id     | BIGINT | art_works (INT)                        |
| object_types    | type_id           | BIGINT | art_works (INT)                        |
| repositories    | repository_id     | BIGINT | art_works (INT)                        |
| classifications | classification_id | BIGINT | art_works (INT)                        |
