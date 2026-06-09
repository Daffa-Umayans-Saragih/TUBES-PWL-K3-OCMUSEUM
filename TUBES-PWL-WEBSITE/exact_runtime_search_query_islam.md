# Exact Runtime SQL Query

## Input User

Islam

---------------------------------------------------------------------

## Route

/art/collection/search?q=Islam

---------------------------------------------------------------------

## Controller

ArtController.php

---------------------------------------------------------------------

## Method

search()

---------------------------------------------------------------------

## Final Executed SQL

A total of 17 database queries were executed during this single search request. Below is the chronological, exact list of every executed query with its bindings fully resolved:

### Query 1
```sql
select "department_id", "department_name" from "departments" order by "department_name" asc;
```
*(Executed in 0.06 ms)*

### Query 2
```sql
select count(*) as aggregate from "art_works" where ("title" like '%Islam%' or "description" like '%Islam%' or exists (select * from "credit_lines" where "art_works"."credit_line_id" = "credit_lines"."credit_line_id" and "credit_line_text" like '%Islam%' and "credit_lines"."deleted_at" is null) or "gallery_number" like '%Islam%' or "accession_number" like '%Islam%' or exists (select * from "constituents" inner join "art_work_constituents" on "constituents"."constituent_id" = "art_work_constituents"."constituent_id" where "art_works"."art_work_id" = "art_work_constituents"."art_work_id" and "display_name" like '%Islam%') or exists (select * from "cultures" inner join "art_work_cultures" on "cultures"."culture_id" = "art_work_cultures"."culture_id" where "art_works"."art_work_id" = "art_work_cultures"."art_work_id" and "culture_name" like '%Islam%'));
```
*(Executed in 0.3 ms)*

### Query 3
```sql
select * from "art_works" where ("title" like '%Islam%' or "description" like '%Islam%' or exists (select * from "credit_lines" where "art_works"."credit_line_id" = "credit_lines"."credit_line_id" and "credit_line_text" like '%Islam%' and "credit_lines"."deleted_at" is null) or "gallery_number" like '%Islam%' or "accession_number" like '%Islam%' or exists (select * from "constituents" inner join "art_work_constituents" on "constituents"."constituent_id" = "art_work_constituents"."constituent_id" where "art_works"."art_work_id" = "art_work_constituents"."art_work_id" and "display_name" like '%Islam%') or exists (select * from "cultures" inner join "art_work_cultures" on "cultures"."culture_id" = "art_work_cultures"."culture_id" where "art_works"."art_work_id" = "art_work_cultures"."art_work_id" and "culture_name" like '%Islam%')) order by "art_work_id" desc limit 12 offset 0;
```
*(Executed in 0.2 ms)*

### Query 4
```sql
select * from "departments" where "departments"."department_id" in (1);
```
*(Executed in 0.02 ms)*

### Query 5
```sql
select * from "object_types" where "object_types"."type_id" in (1);
```
*(Executed in 0.02 ms)*

### Query 6
```sql
select * from "locations" where "locations"."location_id" in (1);
```
*(Executed in 0.02 ms)*

### Query 7
```sql
select * from "art_work_images" where "art_work_images"."art_work_id" in (1) and "art_work_images"."deleted_at" is null;
```
*(Executed in 0.04 ms)*

### Query 8
```sql
select "constituents".*, "art_work_constituents"."art_work_id" as "pivot_art_work_id", "art_work_constituents"."constituent_id" as "pivot_constituent_id", "art_work_constituents"."role_id" as "pivot_role_id", "art_work_constituents"."prefix_id" as "pivot_prefix_id", "art_work_constituents"."suffix_id" as "pivot_suffix_id", "art_work_constituents"."display_order" as "pivot_display_order", "art_work_constituents"."created_at" as "pivot_created_at", "art_work_constituents"."updated_at" as "pivot_updated_at" from "constituents" inner join "art_work_constituents" on "constituents"."constituent_id" = "art_work_constituents"."constituent_id" where "art_work_constituents"."art_work_id" in (1);
```
*(Executed in 0.09 ms)*

### Query 9
```sql
select "cultures".*, "art_work_cultures"."art_work_id" as "pivot_art_work_id", "art_work_cultures"."culture_id" as "pivot_culture_id" from "cultures" inner join "art_work_cultures" on "cultures"."culture_id" = "art_work_cultures"."culture_id" where "art_work_cultures"."art_work_id" in (1);
```
*(Executed in 0.03 ms)*

### Query 10
```sql
select * from "credit_lines" where "credit_lines"."credit_line_id" in (1) and "credit_lines"."deleted_at" is null;
```
*(Executed in 0.04 ms)*

### Query 11
```sql
select "mediums".*, "art_work_mediums"."art_work_id" as "pivot_art_work_id", "art_work_mediums"."medium_id" as "pivot_medium_id", "art_work_mediums"."display_order" as "pivot_display_order" from "mediums" inner join "art_work_mediums" on "mediums"."medium_id" = "art_work_mediums"."medium_id" where "art_work_mediums"."art_work_id" in (1) and "mediums"."deleted_at" is null;
```
*(Executed in 0.05 ms)*

### Query 12
```sql
select "type_id", "object_type_name" from "object_types" order by "object_type_name" asc;
```
*(Executed in 0.03 ms)*

### Query 13
```sql
select "material_id", "material_name" from "materials" order by "material_name" asc;
```
*(Executed in 0.02 ms)*

### Query 14
```sql
select "medium_id", "medium_name" from "mediums" where "mediums"."deleted_at" is null order by "medium_name" asc;
```
*(Executed in 0.02 ms)*

### Query 15
```sql
select "department_id", "department_name" from "departments" order by "department_name" asc;
```
*(Executed in 0.01 ms)*

### Query 16
```sql
select "type_id", "object_type_name" from "object_types" order by "object_type_name" asc;
```
*(Executed in 0.01 ms)*

### Query 17
```sql
select "location_id", "location_name" from "locations" order by "location_name" asc;
```
*(Executed in 0.02 ms)*

