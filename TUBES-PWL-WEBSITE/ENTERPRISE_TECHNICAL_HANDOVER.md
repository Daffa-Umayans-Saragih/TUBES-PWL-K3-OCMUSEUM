# Enterprise Architecture Handover Document

Project: MET Museum Website and Admin
Date: 2026-05-12
Scope: Full application architecture, admin ecosystem, routing, transaction subsystem, schema alignment, and continuation strategy.

## 1. Executive Summary

This handover captures the current production-grade state of the MET Museum website and admin dashboard, including the enterprise UI system, stabilized routing, transaction flow, and schema-compatibility work. The system is a Laravel-based MVC monolith with Blade views, Vite-based assets, and a legacy database that requires per-model timestamp alignment. Core checkout flow is implemented with idempotency and strict XOR constraints between user and guest identities.

## 2. Full Architecture Overview

### 2.1 Application Layers

- Presentation: Blade templates for public pages and admin dashboard.
- Controller layer: Public controllers and admin controllers separated by namespace.
- Domain layer: Eloquent models with explicit primary keys and relationships.
- Persistence: Legacy schema defined by migrations, including constraints and soft deletes.
- Asset pipeline: Vite builds CSS/JS from resources into public/build.
- Tests: Pest-based feature and unit tests.

### 2.2 Key Directories

- app/Http/Controllers: public-facing controllers and checkout flow.
- app/Http/Controllers/Admin: admin dashboard controllers.
- app/Models: transaction models and reference entities.
- resources/views: public and admin views with modular components.
- resources/css: admin CSS system with componentized files.
- routes/web.php: all public, legacy, and admin routes.
- database/migrations: authoritative schema definitions.

### 2.3 Core Architectural Principles

- MVC separation is enforced.
- Legacy compatibility is preserved.
- Schema is treated as source of truth; models conform to migrations.
- Admin UI uses a mirror CSS system tied to view structure.

## 3. Admin Ecosystem Architecture

### 3.1 Admin Route Group

- Prefix: /admin
- Middleware: auth, admin
- Route names: admin.\*
- Sections: dashboard, tickets, orders, payments, users, artworks, exhibitions, analytics, reports, settings.

### 3.2 Admin Views and Components

- Base layout organizes navigation, toolbar, cards, tables, and analytics panels.
- Shared components include toolbar, breadcrumbs, quick-actions, skeleton cards, and empty-state components.
- Each admin page uses consistent UI primitives (badges, status chips, button variants).

### 3.3 Admin CSS Architecture

- Layout styles in resources/css/admin/layouts (admin layout, grid, responsive behavior).
- Component styles in resources/css/admin/components (toolbar, breadcrumbs, quick-actions, tables, cards, empty-state).
- Page styles in resources/css/admin mapped to views.

## 4. Mirror CSS System

### 4.1 Core Rule

Every admin view under resources/views/admin has a mirrored CSS file under resources/css/admin with the same relative path. This guarantees traceability and prevents global CSS sprawl.

### 4.2 Component Convention

Reusable UI parts live in resources/css/admin/components and are referenced by all admin pages. This enforces a single source of truth for UI variants (buttons, badges, chips, skeletons).

### 4.3 Why It Matters

- Rapid debugging: locate styles by view path.
- Safer refactors: no implicit CSS dependencies.
- Scalability: new pages adopt existing component styles.

## 5. Route Stabilization Findings

### 5.1 Findings and Actions

- Imports consolidated at the top of routes/web.php to avoid duplicate or misplaced use statements.
- Routes grouped by domain: home, art collection, visit, auth, tickets, membership, legacy compatibility, admin.
- Canonical /login route provided to satisfy default auth middleware redirection.
- Fallback GET /checkout route added to prevent RouteNotFoundException when GET is accessed without POST.

### 5.2 Current Route Structure Notes

- Legacy compatibility routes remain for older URLs and controllers.
- Admin routes are isolated under the admin middleware group.

## 6. Transaction Ecosystem Design

### 6.1 Data Model Flow

- Cart (user or guest scoped)
  -> CartGroup
  -> CartItem (links TicketAvailability)
  -> TicketType and VisitSchedule
- Checkout creates Order + Payment.
- Payment confirmation generates Tickets.
- Cart is deleted only after ticket generation.

### 6.2 Checkout Flow (Core Behavior)

- Identity resolution: user_id OR guest_id must be set.
- Idempotency: existing non-expired pending order is reused.
- Total amount computed from ticket availability and quantity.
- XOR enforcement: if user and guest both set, guest is cleared.
- Payment status starts as Pending.
- Tickets are created only after payment confirmation.

### 6.3 Ownership and Security

- Order access is validated against current user or guest session.
- Payment confirmation refuses to reprocess paid orders.
- Email is sent after successful payment (non-blocking).

## 7. Database Constraint Findings

### 7.1 XOR Constraints

- carts: carts_user_guest_xor_check enforces user_id XOR guest_id.
- orders: orders_user_guest_xor_check enforces user_id XOR guest_id.

### 7.2 Data Integrity Constraints

- cart_items: quantity must be > 0.
- cart_items: unique cart_group_id + ticket_availability_id.
- ticket_availability: unique ticket_type_id + visit_schedule_id.
- orders: unique order_code.
- ticket_types: unique ticket_type_name.

### 7.3 Soft Deletes

- orders, tickets, ticket_types include deleted_at via softDeletes.

## 8. Schema Compatibility Findings

### 8.1 Timestamp Columns vs Model Settings

- carts table has created_at only; Cart model uses timestamps = false.
- cart_groups table has created_at only; CartGroup model uses timestamps = true with UPDATED_AT = null (aligned).
- cart_items table has no timestamps; CartItem uses timestamps = false (aligned).
- orders table has no created_at/updated_at, but has deleted_at; Order uses timestamps = false (aligned for timestamps).
- payments table has no timestamps; Payment uses timestamps = false (aligned).
- tickets table has no created_at/updated_at, but has deleted_at; Ticket uses timestamps = false (aligned for timestamps).
- ticket_availability table has no timestamps; TicketAvailability uses timestamps = false (aligned).
- ticket_types table has no created_at/updated_at, but has deleted_at; TicketType uses timestamps = false (aligned for timestamps).
- order_details table has no timestamps; OrderDetail uses timestamps = false (aligned).

### 8.2 Soft Delete Mismatch

- Orders, Tickets, TicketTypes have softDeletes in schema but models do not use SoftDeletes trait. This is a behavioral mismatch (deleted records are not filtered by default, and deletes are hard deletes).

## 9. Controller/Model Compatibility Findings

### 9.1 Primary Key and Relationship Mismatches

- OrderDetail model does not declare primary key order_detail_id.
- OrderDetail relations use owner keys id, but schema uses order_id and ticket_id.

### 9.2 Behavioral Gaps

- OrderDetail records are not created during checkout or payment. If order_details is intended for reporting, it is currently unused.
- Cart::lockForUpdate() is called on a model instance; it does not guarantee row-level locking as written.

### 9.3 Soft Deletes Not Modeled

- Order, Ticket, TicketType omit SoftDeletes, which conflicts with schema intent.

## 10. Legacy Schema Alignment Findings

### 10.1 Confirmed Alignments

- cart_groups: created_at only, model aligned with UPDATED_AT = null.
- orders: status column exists via a later migration and is present in the Order model fillable array.

### 10.2 Pending Alignments

- carts: created_at exists but is not auto-managed by model.
- orders, tickets, ticket_types: soft delete columns exist without model-level SoftDeletes.
- order_details: primary key and relationship keys do not match schema.

## 11. Current Project Status

- Admin UI system: enterprise polish complete with toolbars, breadcrumbs, quick actions, skeletons, and empty states.
- Routes: normalized imports and grouped routes; canonical /login and /checkout fallback added.
- Transaction flow: checkout and payment are implemented with idempotency and XOR handling.
- Schema alignment: cart_groups fixed; remaining model alignment still pending.

## 12. Current Unfinished Roadmap

1. Complete per-model alignment against migrations for all transaction tables.
2. Add SoftDeletes to models with deleted_at columns (orders, tickets, ticket_types).
3. Fix OrderDetail primary key and relationship keys.
4. Decide whether to populate order_details on payment, or deprecate it.
5. Review cart created_at handling (enable timestamps with UPDATED_AT = null if desired).
6. Run route:list and transaction flow smoke tests.
7. Finalize empty-state coverage across admin pages.

## 13. Current Risks

- Timestamp mismatches can cause missing created_at data or runtime errors.
- Soft delete mismatch can cause data to appear deleted incorrectly or be permanently removed.
- OrderDetail relationship keys can break reporting or eager loading.
- XOR constraints can throw DB errors if user and guest are both set in edge cases.
- Lack of effective cart row locking may allow race conditions during checkout.

## 14. Debugging Discoveries

- Checkout route required explicit GET fallback to avoid RouteNotFoundException.
- Duplicate or scattered route imports increased risk of binding errors; import block is now centralized.
- Timestamp misalignment caused created_at or updated_at errors; per-model alignment was required.
- XOR constraints on orders and carts required defensive normalization in checkout logic.

## 15. Important Implementation Philosophy

- Treat migrations as the source of truth for schema compatibility.
- Prefer minimal, additive changes that preserve legacy behavior.
- Keep admin UI changes strictly within the mirror CSS and component system.
- Avoid cross-layer coupling; use explicit relationships and fillable fields.

## 16. Recommended Continuation Strategy

1. Build a migration-to-model alignment matrix and update models accordingly.
2. Add SoftDeletes trait where deleted_at exists.
3. Fix OrderDetail primary key and relationship owner keys.
4. Re-run checkout flow and payment flow tests to validate XOR logic and ticket generation.
5. Verify route:list output for duplicates and conflicts.
6. Validate admin UI build output with Vite and check for missing styles.

## 17. Important Do and Dont

### Do

- Keep imports at top of routes/web.php.
- Use array-based controller routes consistently.
- Align every model to its migration (timestamps, keys, deletes).
- Preserve XOR constraints and validate identity flow.
- Use admin UI components and mirror CSS for new pages.

### Dont

- Do not add migrations or change schema without explicit approval.
- Do not disable timestamps globally to solve legacy issues.
- Do not remove legacy compatibility routes.
- Do not create tickets during checkout (only after payment).
- Do not delete carts before tickets are generated.

## 18. Enterprise Implementation Inventory Document

This section is the physical project map and implementation inventory required for developer onboarding and continuation. It is a concrete path map of the admin system and its mirrored CSS files.

### 18.1 Full Admin Folder Structure (Views)

resources/views/admin/

- admin.blade.php
- layouts/
    - admin-layout.blade.php
- layouts-admin/
    - layout-admin.blade.php
- components/
    - admin-sidebar.blade.php
    - navbar-admin.blade.php
    - navbar/
        - navbar.blade.php
    - sidebar/
        - sidebar.blade.php
    - footer/
        - footer.blade.php
    - toolbar/
        - breadcrumbs.blade.php
        - page-toolbar.blade.php
        - quick-actions.blade.php
    - cards/
        - skeleton-card.blade.php
        - stat-card.blade.php
    - charts/
        - capacity-chart.blade.php
        - exhibition-chart.blade.php
        - payment-chart.blade.php
        - revenue-chart.blade.php
        - ticket-sales-chart.blade.php
        - visitor-chart.blade.php
    - tables/
        - data-table.blade.php
    - filters/
        - filter-bar.blade.php
    - empty-state/
        - empty-state.blade.php
    - modals/
        - base-modal.blade.php
- analytics/
    - index.blade.php
- artworks/
    - index.blade.php
- dashboard/
    - index.blade.php
    - dashboard.blade.php
    - transactions.blade.php
    - artworks.blade.php
- exhibitions/
    - index.blade.php
- orders/
    - index.blade.php
- payments/
    - index.blade.php
- reports/
    - index.blade.php
- settings/
    - index.blade.php
- tickets/
    - index.blade.php
- users/
    - index.blade.php
- art/
    - art.blade.php
    - create/
        - create.blade.php
    - edit/
        - edit.blade.php
    - show/
        - show.blade.php

### 18.2 Full Admin View Path Map

Modern admin pages

- resources/views/admin/dashboard/index.blade.php
- resources/views/admin/analytics/index.blade.php
- resources/views/admin/artworks/index.blade.php
- resources/views/admin/exhibitions/index.blade.php
- resources/views/admin/orders/index.blade.php
- resources/views/admin/payments/index.blade.php
- resources/views/admin/settings/index.blade.php
- resources/views/admin/tickets/index.blade.php
- resources/views/admin/users/index.blade.php

Legacy admin pages

- resources/views/admin/dashboard/dashboard.blade.php
- resources/views/admin/dashboard/transactions.blade.php
- resources/views/admin/dashboard/artworks.blade.php
- resources/views/admin/art/art.blade.php
- resources/views/admin/art/create/create.blade.php
- resources/views/admin/art/edit/edit.blade.php
- resources/views/admin/art/show/show.blade.php

Layouts

- resources/views/admin/layouts/admin-layout.blade.php
- resources/views/admin/layouts-admin/layout-admin.blade.php
- resources/views/admin/admin.blade.php

Components

- resources/views/admin/components/navbar/navbar.blade.php
- resources/views/admin/components/sidebar/sidebar.blade.php
- resources/views/admin/components/footer/footer.blade.php
- resources/views/admin/components/toolbar/page-toolbar.blade.php
- resources/views/admin/components/toolbar/breadcrumbs.blade.php
- resources/views/admin/components/toolbar/quick-actions.blade.php
- resources/views/admin/components/cards/stat-card.blade.php
- resources/views/admin/components/cards/skeleton-card.blade.php
- resources/views/admin/components/charts/capacity-chart.blade.php
- resources/views/admin/components/charts/exhibition-chart.blade.php
- resources/views/admin/components/charts/payment-chart.blade.php
- resources/views/admin/components/charts/revenue-chart.blade.php
- resources/views/admin/components/charts/ticket-sales-chart.blade.php
- resources/views/admin/components/charts/visitor-chart.blade.php
- resources/views/admin/components/tables/data-table.blade.php
- resources/views/admin/components/filters/filter-bar.blade.php
- resources/views/admin/components/empty-state/empty-state.blade.php
- resources/views/admin/components/modals/base-modal.blade.php
- resources/views/admin/components/admin-sidebar.blade.php
- resources/views/admin/components/navbar-admin.blade.php

### 18.3 Full Admin CSS Mirror Path Map

resources/css/admin/

- layouts/
    - admin-layout.css
- layout.css
- analytics/
    - index.css
- artworks/
    - index.css
- dashboard/
    - index.css
    - dashboard.css
    - modern.css
- exhibitions/
    - index.css
- orders/
    - index.css
- payments/
    - index.css
- reports/
    - index.css
- settings/
    - index.css
- tickets/
    - index.css
- users/
    - index.css
- art/
    - art.css
    - index.css
    - create.css
    - create/create.css
    - edit.css
    - edit/edit.css
    - show.css
    - show/show.css
- components/
    - admin-sidebar.css
    - navbar-admin.css
    - icon-placeholder.css
    - footer.css
    - footer/
        - footer.css
    - navbar/
        - navbar.css
        - notification-shell.css
    - sidebar/
        - sidebar.css
    - toolbar/
        - breadcrumbs.css
        - page-toolbar.css
        - quick-actions.css
    - cards/
        - stat-card.css
        - skeleton-card.css
    - charts/
        - capacity-chart.css
        - exhibition-chart.css
        - payment-chart.css
        - revenue-chart.css
        - ticket-sales-chart.css
        - visitor-chart.css
    - tables/
        - data-table.css
    - filters/
        - filter-bar.css
    - modals/
        - base-modal.css
    - empty-state/
        - empty-state.css

### 18.4 Mirror CSS Relation (View -> CSS)

Modern admin views

- resources/views/admin/dashboard/index.blade.php -> resources/css/admin/dashboard/index.css
- resources/views/admin/analytics/index.blade.php -> resources/css/admin/analytics/index.css
- resources/views/admin/artworks/index.blade.php -> resources/css/admin/artworks/index.css
- resources/views/admin/exhibitions/index.blade.php -> resources/css/admin/exhibitions/index.css
- resources/views/admin/orders/index.blade.php -> resources/css/admin/orders/index.css
- resources/views/admin/payments/index.blade.php -> resources/css/admin/payments/index.css
- resources/views/admin/settings/index.blade.php -> resources/css/admin/settings/index.css
- resources/views/admin/tickets/index.blade.php -> resources/css/admin/tickets/index.css
- resources/views/admin/users/index.blade.php -> resources/css/admin/users/index.css

Legacy admin views

- resources/views/admin/dashboard/dashboard.blade.php -> resources/css/admin/dashboard/dashboard.css
- resources/views/admin/dashboard/transactions.blade.php -> resources/css/admin/dashboard/modern.css
- resources/views/admin/dashboard/artworks.blade.php -> resources/css/admin/dashboard/modern.css
- resources/views/admin/art/art.blade.php -> resources/css/admin/art/art.css
- resources/views/admin/art/create/create.blade.php -> resources/css/admin/art/create/create.css
- resources/views/admin/art/edit/edit.blade.php -> resources/css/admin/art/edit/edit.css
- resources/views/admin/art/show/show.blade.php -> resources/css/admin/art/show/show.css

Layouts and component bundles

- resources/views/admin/layouts/admin-layout.blade.php -> resources/css/admin/layouts/admin-layout.css
- resources/views/admin/components/navbar/navbar.blade.php -> resources/css/admin/components/navbar/navbar.css
- resources/views/admin/components/sidebar/sidebar.blade.php -> resources/css/admin/components/sidebar/sidebar.css
- resources/views/admin/components/footer/footer.blade.php -> resources/css/admin/components/footer/footer.css
- resources/views/admin/components/toolbar/page-toolbar.blade.php -> resources/css/admin/components/toolbar/page-toolbar.css
- resources/views/admin/components/toolbar/breadcrumbs.blade.php -> resources/css/admin/components/toolbar/breadcrumbs.css
- resources/views/admin/components/toolbar/quick-actions.blade.php -> resources/css/admin/components/toolbar/quick-actions.css
- resources/views/admin/components/cards/stat-card.blade.php -> resources/css/admin/components/cards/stat-card.css
- resources/views/admin/components/cards/skeleton-card.blade.php -> resources/css/admin/components/cards/skeleton-card.css
- resources/views/admin/components/charts/capacity-chart.blade.php -> resources/css/admin/components/charts/capacity-chart.css
- resources/views/admin/components/charts/exhibition-chart.blade.php -> resources/css/admin/components/charts/exhibition-chart.css
- resources/views/admin/components/charts/payment-chart.blade.php -> resources/css/admin/components/charts/payment-chart.css
- resources/views/admin/components/charts/revenue-chart.blade.php -> resources/css/admin/components/charts/revenue-chart.css
- resources/views/admin/components/charts/ticket-sales-chart.blade.php -> resources/css/admin/components/charts/ticket-sales-chart.css
- resources/views/admin/components/charts/visitor-chart.blade.php -> resources/css/admin/components/charts/visitor-chart.css
- resources/views/admin/components/tables/data-table.blade.php -> resources/css/admin/components/tables/data-table.css
- resources/views/admin/components/filters/filter-bar.blade.php -> resources/css/admin/components/filters/filter-bar.css
- resources/views/admin/components/empty-state/empty-state.blade.php -> resources/css/admin/components/empty-state/empty-state.css
- resources/views/admin/components/modals/base-modal.blade.php -> resources/css/admin/components/modals/base-modal.css

Legacy and parallel components (non-mirror or duplicated)

- resources/views/admin/components/admin-sidebar.blade.php -> resources/css/admin/components/admin-sidebar.css
- resources/views/admin/components/navbar-admin.blade.php -> resources/css/admin/components/navbar-admin.css
- resources/views/admin/layouts-admin/layout-admin.blade.php -> resources/css/admin/layout.css (legacy layout) and resources/css/admin/layout/layout.css (referenced in view, verify path)

### 18.5 Component Inventory (Reusable)

Navigation and layout

- Sidebar: resources/views/admin/components/sidebar/sidebar.blade.php with resources/css/admin/components/sidebar/sidebar.css
- Navbar: resources/views/admin/components/navbar/navbar.blade.php with resources/css/admin/components/navbar/navbar.css
- Footer: resources/views/admin/components/footer/footer.blade.php with resources/css/admin/components/footer/footer.css
- Icon placeholder system: resources/css/admin/components/icon-placeholder.css
- Notification shell: resources/css/admin/components/navbar/notification-shell.css (structure in navbar.blade.php)

Toolbar and utility

- Toolbar: resources/views/admin/components/toolbar/page-toolbar.blade.php with resources/css/admin/components/toolbar/page-toolbar.css
- Breadcrumbs: resources/views/admin/components/toolbar/breadcrumbs.blade.php with resources/css/admin/components/toolbar/breadcrumbs.css
- Quick actions: resources/views/admin/components/toolbar/quick-actions.blade.php with resources/css/admin/components/toolbar/quick-actions.css
- Filters: resources/views/admin/components/filters/filter-bar.blade.php with resources/css/admin/components/filters/filter-bar.css

Cards, tables, charts

- Stat card: resources/views/admin/components/cards/stat-card.blade.php with resources/css/admin/components/cards/stat-card.css
- Skeleton card: resources/views/admin/components/cards/skeleton-card.blade.php with resources/css/admin/components/cards/skeleton-card.css
- Data table: resources/views/admin/components/tables/data-table.blade.php with resources/css/admin/components/tables/data-table.css
- Charts (placeholders): resources/views/admin/components/charts/_ with resources/css/admin/components/charts/_

Empty state and modals

- Empty state: resources/views/admin/components/empty-state/empty-state.blade.php with resources/css/admin/components/empty-state/empty-state.css
- Base modal: resources/views/admin/components/modals/base-modal.blade.php with resources/css/admin/components/modals/base-modal.css

Legacy components (parallel system)

- Admin sidebar legacy: resources/views/admin/components/admin-sidebar.blade.php with resources/css/admin/components/admin-sidebar.css and resources/css/components/admin-sidebar.css
- Admin navbar legacy: resources/views/admin/components/navbar-admin.blade.php with resources/css/admin/components/navbar-admin.css

### 18.6 Controller Inventory (Admin)

Modern admin controllers (view-only UI skeletons)

- AnalyticsController: index() returns admin.analytics.index (status: scaffold UI)
- ArtworkController: index() returns admin.artworks.index (status: scaffold UI)
- ExhibitionController: index() returns admin.exhibitions.index (status: scaffold UI)
- OrderController: index() returns admin.orders.index (status: scaffold UI)
- PaymentController: index() returns admin.payments.index (status: scaffold UI)
- SettingController: index() returns admin.settings.index (status: scaffold UI)
- TicketController: index() returns admin.tickets.index (status: scaffold UI)
- UserController: index() returns admin.users.index (status: scaffold UI)

Mixed admin controller (modern dashboard shell + legacy data functions)

- DashboardController:
    - index(): returns admin.dashboard.index (modern UI)
    - transactions(): data-driven transactions list and charts (legacy data)
    - artworks(): data-driven artworks list and stats (legacy data)
    - exportTransactions(): CSV export (legacy operational)
    - storeArtwork(): data write for artworks (legacy operational)
    - Status: partially implemented (mix of modern UI and legacy data logic)

Legacy admin CRUD controller

- ArtController:
    - dashboard(): legacy dashboard metrics (admin.dashboard.dashboard)
    - index(): list artworks (admin.art.art)
    - create(), store(), edit(), update(), show(), destroy(), deleteImage()
    - Status: implemented legacy CRUD, not wired in current admin route group

### 18.7 Route Inventory

Admin routes (modern admin skeleton)

- GET /admin -> DashboardController@index
- GET /admin/tickets -> TicketController@index
- GET /admin/orders -> OrderController@index
- GET /admin/payments -> PaymentController@index
- GET /admin/users -> UserController@index
- GET /admin/artworks -> ArtworkController@index
- GET /admin/exhibitions -> ExhibitionController@index
- GET /admin/analytics -> AnalyticsController@index
- GET /admin/settings -> SettingController@index
- GET /admin-preview -> returns admin.dashboard.index (preview only)

Public routes

- /, /about
- /art/collection, /art/curatorial-areas, /art/collection/search, /art/collection/{id}
- /plan-your-visit/\*, /visit-guides/accessibility
- /members/membership and /members/membership/{id}
- /member/membership (static view)

Authentication and account routes

- /account/\* (register, login, forgot-password, account, logout)
- /register (POST), /login (GET), /guest-login (POST), /guest-checkout (POST)

Transaction routes

- /tickets (index, show, scan)
- /admission (ticket admission)
- /cart (GET/POST), /cart/group/{id} (delete, modify)
- /cart/add, /admission/cart/store
- /checkout (GET fallback, POST checkout)
- /checkout/payments/{order}, /checkout/pay/{order}, /checkout/success/{order}

Legacy compatibility routes

- /art/{slug}
- /order/create, /order/store, /order/show, /order/show/{order}

### 18.8 File Status Inventory

Modern admin system

- Layout and shell (resources/views/admin/layouts/admin-layout.blade.php, resources/css/admin/layouts/admin-layout.css): implemented and stabilized
- Core components (toolbar, breadcrumbs, quick actions, navbar, sidebar, footer): implemented and stabilized
- Utility components (filters, empty state, skeletons, data table, charts, base modal): implemented UI; charts/table/modal are scaffold placeholders
- Modern admin pages (analytics, artworks, exhibitions, orders, payments, reports, settings, tickets, users, dashboard index): implemented UI; pending data integration

Legacy admin system

- Legacy dashboard pages (admin/dashboard/dashboard.blade.php, transactions.blade.php, artworks.blade.php): implemented data views using legacy layout and styles
- Legacy artwork CRUD pages (admin/art/\*): implemented, data-driven, legacy layout
- Legacy layout files (admin/admin.blade.php, admin/layouts-admin/layout-admin.blade.php, css/admin/layout.css, css/admin/components/navbar-admin.css): legacy modified, parallel system

Integration status

- Empty state component: pending integration in all admin pages
- Notification shell: present in navbar markup, no JS logic (UI only)
- Charts and tables: placeholders, pending real data and JS integration

### 18.9 Modified File Inventory (No Git Metadata)

Note: The workspace is not a git repository, so modification tracking is inferred from current structure and the enterprise admin phase scope.

Known stabilization changes

- routes/web.php (route normalization, admin grouping, checkout fallback)
- app/Http/Controllers/CheckoutController.php (checkout idempotency and XOR handling)
- app/Models/CartGroup.php (timestamps enabled, UPDATED_AT = null)

Enterprise admin UI updates (observed in current structure)

- resources/views/admin/layouts/admin-layout.blade.php
- resources/css/admin/layouts/admin-layout.css
- resources/views/admin/\*/index.blade.php (modern admin pages)
- resources/css/admin/\*/index.css (modern admin page styles)
- resources/views/admin/components/\* (admin components)
- resources/css/admin/components/\* (component styles)

### 18.10 Newly Created File Inventory (No Git Metadata)

Note: This list reflects the enterprise admin system additions; validate against external version control if needed.

Admin components and styles

- resources/views/admin/components/toolbar/page-toolbar.blade.php
- resources/views/admin/components/toolbar/breadcrumbs.blade.php
- resources/views/admin/components/toolbar/quick-actions.blade.php
- resources/views/admin/components/cards/skeleton-card.blade.php
- resources/views/admin/components/empty-state/empty-state.blade.php
- resources/views/admin/components/charts/\*
- resources/views/admin/components/tables/data-table.blade.php
- resources/views/admin/components/filters/filter-bar.blade.php
- resources/views/admin/components/modals/base-modal.blade.php
- resources/css/admin/components/toolbar/\*
- resources/css/admin/components/cards/\*
- resources/css/admin/components/empty-state/empty-state.css
- resources/css/admin/components/charts/\*
- resources/css/admin/components/tables/data-table.css
- resources/css/admin/components/filters/filter-bar.css
- resources/css/admin/components/modals/base-modal.css
- resources/css/admin/components/icon-placeholder.css
- resources/css/admin/components/navbar/notification-shell.css

Documentation

- ENTERPRISE_TECHNICAL_HANDOVER.md

### 18.11 Scaffold File Inventory

Scaffold and placeholder UI (no real data or logic)

- resources/views/admin/analytics/index.blade.php
- resources/views/admin/artworks/index.blade.php
- resources/views/admin/exhibitions/index.blade.php
- resources/views/admin/orders/index.blade.php
- resources/views/admin/payments/index.blade.php
- resources/views/admin/tickets/index.blade.php
- resources/views/admin/users/index.blade.php
- resources/views/admin/components/charts/\*
- resources/views/admin/components/tables/data-table.blade.php
- resources/views/admin/components/modals/base-modal.blade.php

Scaffold and placeholder styles

- resources/css/admin/components/charts/\*
- resources/css/admin/components/tables/data-table.css
- resources/css/admin/components/modals/base-modal.css

### 18.12 File Status Quick Flags

- Implemented: admin/layouts/admin-layout.blade.php, admin/components/\* (toolbar, navbar, sidebar, footer)
- Partially implemented: admin/dashboard/index.blade.php (UI complete, data pending)
- Legacy modified: admin/art/\*, admin/dashboard/dashboard.blade.php, admin/dashboard/transactions.blade.php, admin/dashboard/artworks.blade.php
- Stabilized: routes/web.php, resources/css/admin/layouts/admin-layout.css
- Pending integration: empty-state component, notification shell behavior, chart data bindings
