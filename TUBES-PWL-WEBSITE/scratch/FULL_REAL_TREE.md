# FULL REAL TREE STRUCTURE

Berikut adalah hasil scan struktur riil tanpa pemotongan, beserta mapping ukuran dan arsitektur.

```text

==================================================
DEEP SCAN: resources/views/admin/
==================================================
📁 resources/views/admin/
│
├── 📁 analytics/
│   └── 📄 index.blade.php
│   👉 Stats: Files: 1 | Chars: 4,658 | Lines: 134 | Complexity: LOW
│
├── 📁 art/
│   ├── 📁 create/
│   │   └── 📄 create.blade.php
│   ├── 📁 edit/
│   │   └── 📄 edit.blade.php
│   ├── 📁 show/
│   │   └── 📄 show.blade.php
│   └── 📄 art.blade.php
│   👉 Stats: Files: 4 | Chars: 36,492 | Lines: 796 | Complexity: LOW
│
├── 📁 artworks/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 251,003 | Lines: 4,314 | Complexity: HIGH
│
├── 📁 categories/
│   ├── 📄 form.blade.php
│   └── 📄 index.blade.php
│   👉 Stats: Files: 2 | Chars: 9,587 | Lines: 153 | Complexity: LOW
│
├── 📁 classifications/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,636 | Lines: 200 | Complexity: LOW
│
├── 📁 components/
│   ├── 📁 cards/
│   │   ├── 📄 skeleton-card.blade.php
│   │   └── 📄 stat-card.blade.php
│   ├── 📁 charts/
│   │   ├── 📄 capacity-chart.blade.php
│   │   ├── 📄 exhibition-chart.blade.php
│   │   ├── 📄 payment-chart.blade.php
│   │   ├── 📄 revenue-chart.blade.php
│   │   ├── 📄 ticket-sales-chart.blade.php
│   │   └── 📄 visitor-chart.blade.php
│   ├── 📁 empty-state/
│   │   └── 📄 empty-state.blade.php
│   ├── 📁 filters/
│   │   └── 📄 filter-bar.blade.php
│   ├── 📁 footer/
│   │   └── 📄 footer.blade.php
│   ├── 📁 modals/
│   │   └── 📄 base-modal.blade.php
│   ├── 📁 nav/
│   │   └── 📄 nav.blade.php
│   ├── 📁 navbar/
│   │   └── 📄 navbar.blade.php
│   ├── 📁 sidebar/
│   │   └── 📄 sidebar.blade.php
│   ├── 📁 tables/
│   │   └── 📄 data-table.blade.php
│   ├── 📁 toolbar/
│   │   ├── 📄 breadcrumbs.blade.php
│   │   ├── 📄 page-toolbar.blade.php
│   │   └── 📄 quick-actions.blade.php
│   ├── 📄 admin-sidebar.blade.php
│   └── 📄 navbar-admin.blade.php
│   👉 Stats: Files: 21 | Chars: 17,832 | Lines: 485 | Complexity: LOW
│
├── 📁 constituents/
│   ├── 📄 form.blade.php
│   └── 📄 index.blade.php
│   👉 Stats: Files: 2 | Chars: 22,030 | Lines: 591 | Complexity: LOW
│
├── 📁 cultures/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,342 | Lines: 200 | Complexity: LOW
│
├── 📁 dashboard/
│   ├── 📄 artworks.blade.php
│   ├── 📄 dashboard.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 transactions.blade.php
│   👉 Stats: Files: 4 | Chars: 51,082 | Lines: 1,314 | Complexity: MEDIUM
│
├── 📁 departments/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 18,593 | Lines: 600 | Complexity: LOW
│
├── 📁 dynasties/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,356 | Lines: 200 | Complexity: LOW
│
├── 📁 exhibitions/
│   └── 📄 index.blade.php
│   👉 Stats: Files: 1 | Chars: 4,314 | Lines: 100 | Complexity: LOW
│
├── 📁 layout/
│   └── 📄 layout.blade.php
│   👉 Stats: Files: 1 | Chars: 5,998 | Lines: 146 | Complexity: LOW
│
├── 📁 layouts/
│   └── 📄 admin-layout.blade.php
│   👉 Stats: Files: 1 | Chars: 1,120 | Lines: 38 | Complexity: LOW
│
├── 📁 layouts-admin/
│   └── 📄 layout-admin.blade.php
│   👉 Stats: Files: 1 | Chars: 2,305 | Lines: 65 | Complexity: LOW
│
├── 📁 locations/
│   ├── 📄 form.blade.php
│   └── 📄 index.blade.php
│   👉 Stats: Files: 2 | Chars: 12,528 | Lines: 403 | Complexity: LOW
│
├── 📁 materials/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,384 | Lines: 200 | Complexity: LOW
│
├── 📁 mediums/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,300 | Lines: 200 | Complexity: LOW
│
├── 📁 object-types/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,458 | Lines: 200 | Complexity: LOW
│
├── 📁 orders/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 75,906 | Lines: 2,278 | Complexity: MEDIUM
│
├── 📁 payment/
│   └── 📄 index.blade.php
│   👉 Stats: Files: 1 | Chars: 16,557 | Lines: 313 | Complexity: LOW
│
├── 📁 payments/
│   └── 📄 index.blade.php
│   👉 Stats: Files: 1 | Chars: 15,487 | Lines: 593 | Complexity: LOW
│
├── 📁 periods/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,300 | Lines: 200 | Complexity: LOW
│
├── 📁 portfolios/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,426 | Lines: 200 | Complexity: LOW
│
├── 📁 posts/
│   └── 📄 index.blade.php
│   👉 Stats: Files: 1 | Chars: 8,953 | Lines: 122 | Complexity: LOW
│
├── 📁 reigns/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,258 | Lines: 200 | Complexity: LOW
│
├── 📁 reports/
│   └── 📄 index.blade.php
│   👉 Stats: Files: 1 | Chars: 4,862 | Lines: 139 | Complexity: LOW
│
├── 📁 repositories/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 8,482 | Lines: 200 | Complexity: LOW
│
├── 📁 settings/
│   └── 📄 index.blade.php
│   👉 Stats: Files: 1 | Chars: 2,969 | Lines: 93 | Complexity: LOW
│
├── 📁 tags/
│   ├── 📄 form.blade.php
│   ├── 📄 index.blade.php
│   └── 📄 show.blade.php
│   👉 Stats: Files: 3 | Chars: 9,992 | Lines: 232 | Complexity: LOW
│
├── 📁 ticket-analytics/
│   ├── 📁 components/
│   │   ├── 📄 filter-bar.blade.php
│   │   └── 📄 stat-card.blade.php
│   └── 📄 index.blade.php
│   👉 Stats: Files: 3 | Chars: 22,549 | Lines: 485 | Complexity: LOW
│
├── 📁 tickets/
│   ├── 📄 index.blade.php
│   └── 📄 management.blade.php
│   👉 Stats: Files: 2 | Chars: 45,577 | Lines: 922 | Complexity: LOW
│
├── 📁 users/
│   ├── 📄 edit.blade.php
│   └── 📄 index.blade.php
│   👉 Stats: Files: 2 | Chars: 21,121 | Lines: 452 | Complexity: LOW
│
└── 📄 admin.blade.php
    👉 Root Files Stats: Files: 1 | Chars: 1,437 | Lines: 43 | Complexity: LOW

==================================================
DEEP SCAN: resources/views/ordinary/
==================================================
📁 resources/views/ordinary/
│
├── 📁 about/
│   ├── 📁 about/
│   │   └── 📄 about.blade.php
│   └── 📄 about.blade.php
│   👉 Stats: Files: 2 | Chars: 642 | Lines: 31 | Complexity: LOW
│
├── 📁 account/
│   ├── 📁 account/
│   │   └── 📄 account.blade.php
│   ├── 📁 account-check/
│   │   └── 📄 account-check.blade.php
│   ├── 📁 forgot-password/
│   │   └── 📄 forgot-password.blade.php
│   ├── 📁 login/
│   │   ├── 📄 layout.blade.php
│   │   └── 📄 login.blade.php
│   ├── 📁 register/
│   │   └── 📄 register.blade.php
│   ├── 📁 reset-password/
│   │   └── 📄 reset-password.blade.php
│   └── 📄 auth-form.blade.php
│   👉 Stats: Files: 8 | Chars: 53,994 | Lines: 1,295 | Complexity: MEDIUM
│
├── 📁 admission/
│   ├── 📄 admission.blade.php
│   └── 📄 select.blade.php
│   👉 Stats: Files: 2 | Chars: 43,102 | Lines: 901 | Complexity: LOW
│
├── 📁 art/
│   ├── 📁 catalog/
│   │   └── 📄 catalog.blade.php
│   ├── 📁 detail/
│   │   └── 📄 detail.blade.php
│   ├── 📁 search/
│   │   └── 📄 search.blade.php
│   ├── 📁 show/
│   │   └── 📄 show.blade.php
│   ├── 📄 art.blade.php
│   └── 📄 curatorial-areas.blade.php
│   👉 Stats: Files: 6 | Chars: 125,451 | Lines: 2,681 | Complexity: MEDIUM
│
├── 📁 auth/
│   └── 📄 login.blade.php
│   👉 Stats: Files: 1 | Chars: 275 | Lines: 9 | Complexity: LOW
│
├── 📁 checkout/
│   ├── 📁 cart/
│   │   └── 📄 cart.blade.php
│   ├── 📁 payments/
│   │   ├── 📄 payments.blade.php
│   │   └── 📄 success.blade.php
│   ├── 📄 form.blade.php
│   └── 📄 success.blade.php
│   👉 Stats: Files: 5 | Chars: 32,865 | Lines: 672 | Complexity: LOW
│
├── 📁 home/
│   └── 📁 welcome/
│       └── 📄 welcome.blade.php
│   👉 Stats: Files: 1 | Chars: 10,883 | Lines: 199 | Complexity: LOW
│
├── 📁 member/
│   ├── 📁 activation/
│   │   └── 📄 result.blade.php
│   ├── 📁 add-member/
│   │   └── 📄 add-member.blade.php
│   └── 📁 membership/
│       └── 📄 membership.blade.php
│   👉 Stats: Files: 3 | Chars: 47,862 | Lines: 929 | Complexity: LOW
│
├── 📁 membership/
│   ├── 📁 show/
│   │   └── 📄 show.blade.php
│   └── 📄 membership.blade.php
│   👉 Stats: Files: 2 | Chars: 6,265 | Lines: 150 | Complexity: LOW
│
├── 📁 order/
│   ├── 📁 create/
│   │   └── 📄 create.blade.php
│   └── 📁 show/
│       └── 📄 show.blade.php
│   👉 Stats: Files: 2 | Chars: 16,586 | Lines: 348 | Complexity: LOW
│
├── 📁 plan-your-visit/
│   ├── 📁 accessibility/
│   │   ├── 📄 accessibility-cloisters.blade.php
│   │   └── 📄 accessibility.blade.php
│   ├── 📁 cloister/
│   │   ├── 📄 cloisters.blade.php
│   │   └── 📄 learn-more.blade.php
│   ├── 📁 fifth/
│   │   ├── 📄 fifth.blade.php
│   │   └── 📄 learn-more.blade.php
│   ├── 📁 our-experience/
│   │   └── 📄 our-experience.blade.php
│   └── 📁 visit/
│       └── 📄 visit.blade.php
│   👉 Stats: Files: 8 | Chars: 95,206 | Lines: 1,768 | Complexity: MEDIUM
│
└── 📁 ticket/
    ├── 📁 admission/
    │   └── 📄 admission.blade.php
    ├── 📁 cart/
    │   └── 📄 cart.blade.php
    ├── 📁 checkout/
    │   └── 📄 checkout.blade.php
    ├── 📄 index.blade.php
    └── 📄 ticket.blade.php
    👉 Stats: Files: 5 | Chars: 9,499 | Lines: 242 | Complexity: LOW

==================================================
DEEP SCAN: app/Http/Controllers/
==================================================
📁 app/Http/Controllers/
│
├── 📁 Admin/
│   ├── 📄 AnalyticsController.php
│   ├── 📄 ArtController.php
│   ├── 📄 ArtworkController.php
│   ├── 📄 CategoryController.php
│   ├── 📄 ClassificationController.php
│   ├── 📄 ConstituentController.php
│   ├── 📄 CultureController.php
│   ├── 📄 DashboardController.php
│   ├── 📄 DepartmentController.php
│   ├── 📄 DynastyController.php
│   ├── 📄 ExhibitionController.php
│   ├── 📄 LocationController.php
│   ├── 📄 MaterialController.php
│   ├── 📄 MediumController.php
│   ├── 📄 ObjectTypeController.php
│   ├── 📄 OrderController.php
│   ├── 📄 PaymentController.php
│   ├── 📄 PeriodController.php
│   ├── 📄 PortfolioController.php
│   ├── 📄 PostController.php
│   ├── 📄 ReignController.php
│   ├── 📄 ReportController.php
│   ├── 📄 RepositoryController.php
│   ├── 📄 SettingController.php
│   ├── 📄 TagController.php
│   ├── 📄 TicketAnalyticsController.php
│   ├── 📄 TicketController.php
│   └── 📄 UserController.php
│   👉 Stats: Files: 28 | Chars: 241,780 | Lines: 6,169 | Complexity: HIGH
│
├── 📄 ArtController.php
├── 📄 ArtWorkController.php
├── 📄 AuthController.php
├── 📄 CartController.php
├── 📄 CheckAccountController.php
├── 📄 CheckoutController.php
├── 📄 Controller.php
├── 📄 GuestCheckoutController.php
├── 📄 GuestLoginController.php
├── 📄 LoginController.php
├── 📄 MembershipController.php
├── 📄 OrderController.php
├── 📄 OurExperienceController.php
├── 📄 RegisterController.php
├── 📄 ResetPasswordController.php
├── 📄 TicketController.php
└── 📄 VisitController.php
    👉 Root Files Stats: Files: 17 | Chars: 139,439 | Lines: 3,593 | Complexity: MEDIUM

==================================================
DEEP SCAN: resources/css/
==================================================
📁 resources/css/
│
├── 📁 BuyTicketCss/
│   └── 📄 Header.css
│   👉 Stats: Files: 1 | Chars: 390 | Lines: 12 | Complexity: LOW
│
├── 📁 admin/
│   ├── 📁 analytics/
│   │   └── 📄 index.css
│   ├── 📁 art/
│   │   ├── 📁 create/
│   │   │   └── 📄 create.css
│   │   ├── 📁 edit/
│   │   │   └── 📄 edit.css
│   │   ├── 📁 show/
│   │   │   └── 📄 show.css
│   │   ├── 📄 art.css
│   │   ├── 📄 create.css
│   │   ├── 📄 edit.css
│   │   ├── 📄 index.css
│   │   └── 📄 show.css
│   ├── 📁 artworks/
│   │   └── 📄 index.css
│   ├── 📁 components/
│   │   ├── 📁 cards/
│   │   │   ├── 📄 skeleton-card.css
│   │   │   └── 📄 stat-card.css
│   │   ├── 📁 charts/
│   │   │   ├── 📄 capacity-chart.css
│   │   │   ├── 📄 exhibition-chart.css
│   │   │   ├── 📄 payment-chart.css
│   │   │   ├── 📄 revenue-chart.css
│   │   │   ├── 📄 ticket-sales-chart.css
│   │   │   └── 📄 visitor-chart.css
│   │   ├── 📁 empty-state/
│   │   │   └── 📄 empty-state.css
│   │   ├── 📁 filters/
│   │   │   └── 📄 filter-bar.css
│   │   ├── 📁 footer/
│   │   │   └── 📄 footer.css
│   │   ├── 📁 modals/
│   │   │   └── 📄 base-modal.css
│   │   ├── 📁 nav/
│   │   │   └── 📄 nav.css
│   │   ├── 📁 navbar/
│   │   │   ├── 📄 navbar.css
│   │   │   └── 📄 notification-shell.css
│   │   ├── 📁 sidebar/
│   │   │   └── 📄 sidebar.css
│   │   ├── 📁 tables/
│   │   │   └── 📄 data-table.css
│   │   ├── 📁 toolbar/
│   │   │   ├── 📄 breadcrumbs.css
│   │   │   ├── 📄 page-toolbar.css
│   │   │   └── 📄 quick-actions.css
│   │   ├── 📄 admin-sidebar.css
│   │   ├── 📄 footer.css
│   │   ├── 📄 icon-placeholder.css
│   │   ├── 📄 nav.css
│   │   └── 📄 navbar-admin.css
│   ├── 📁 dashboard/
│   │   ├── 📄 dashboard.css
│   │   ├── 📄 index.css
│   │   └── 📄 modern.css
│   ├── 📁 exhibitions/
│   │   └── 📄 index.css
│   ├── 📁 layout/
│   │   └── 📄 layout.css
│   ├── 📁 layouts/
│   │   └── 📄 admin-layout.css
│   ├── 📁 orders/
│   │   └── 📄 index.css
│   ├── 📁 payment/
│   │   └── 📄 index.css
│   ├── 📁 payments/
│   │   └── 📄 index.css
│   ├── 📁 reports/
│   │   └── 📄 index.css
│   ├── 📁 settings/
│   │   └── 📄 index.css
│   ├── 📁 ticket-analytics/
│   │   └── 📄 index.css
│   ├── 📁 tickets/
│   │   └── 📄 index.css
│   ├── 📁 users/
│   │   └── 📄 index.css
│   ├── 📄 dashboard.css
│   └── 📄 layout.css
│   👉 Stats: Files: 51 | Chars: 151,613 | Lines: 7,852 | Complexity: HIGH
│
├── 📁 art/
│   ├── 📁 catalog/
│   │   └── 📄 catalog.css
│   ├── 📁 detail/
│   │   └── 📄 detail.css
│   ├── 📁 search/
│   │   └── 📄 search.css
│   ├── 📁 show/
│   │   └── 📄 show.css
│   ├── 📄 art.css
│   ├── 📄 index.css
│   └── 📄 show.css
│   👉 Stats: Files: 7 | Chars: 14,034 | Lines: 777 | Complexity: LOW
│
├── 📁 components/
│   ├── 📄 admin-sidebar.css
│   ├── 📄 footer.css
│   └── 📄 navbar.css
│   👉 Stats: Files: 3 | Chars: 4,244 | Lines: 280 | Complexity: LOW
│
├── 📁 layouts/
│   ├── 📄 admin.css
│   └── 📄 app.css
│   👉 Stats: Files: 2 | Chars: 249 | Lines: 11 | Complexity: LOW
│
├── 📁 orders/
│   ├── 📄 create.css
│   └── 📄 show.css
│   👉 Stats: Files: 2 | Chars: 4,990 | Lines: 298 | Complexity: LOW
│
├── 📁 ordinary/
│   ├── 📁 about/
│   │   ├── 📁 about/
│   │   │   └── 📄 about.css
│   │   └── 📄 about.css
│   ├── 📁 account/
│   │   ├── 📁 account/
│   │   │   └── 📄 account.css
│   │   ├── 📁 account-check/
│   │   │   └── 📄 account-check.css
│   │   ├── 📁 forgot-password/
│   │   │   └── 📄 forgot-password.css
│   │   ├── 📁 login/
│   │   │   └── 📄 login.css
│   │   ├── 📁 register/
│   │   │   └── 📄 register.css
│   │   └── 📄 auth-form.css
│   ├── 📁 admission/
│   │   ├── 📄 admission.css
│   │   └── 📄 select.css
│   ├── 📁 art/
│   │   ├── 📁 catalog/
│   │   │   └── 📄 catalog.css
│   │   ├── 📁 detail/
│   │   │   └── 📄 detail.css
│   │   ├── 📁 search/
│   │   │   └── 📄 search.css
│   │   ├── 📁 show/
│   │   │   └── 📄 show.css
│   │   └── 📄 art.css
│   ├── 📁 checkout/
│   │   ├── 📁 cart/
│   │   │   └── 📄 cart.css
│   │   ├── 📁 payments/
│   │   │   ├── 📄 payments.css
│   │   │   └── 📄 success.css
│   │   ├── 📄 form.css
│   │   └── 📄 success.css
│   ├── 📁 home/
│   │   └── 📁 welcome/
│   │       └── 📄 welcome.css
│   ├── 📁 member/
│   │   ├── 📁 add-member/
│   │   │   └── 📄 add-member.css
│   │   └── 📁 membership/
│   │       └── 📄 membership.css
│   ├── 📁 membership/
│   │   ├── 📁 show/
│   │   │   └── 📄 show.css
│   │   └── 📄 membership.css
│   ├── 📁 order/
│   │   ├── 📁 create/
│   │   │   └── 📄 create.css
│   │   └── 📁 show/
│   │       └── 📄 show.css
│   ├── 📁 plan-your-visit/
│   │   ├── 📁 accessibility/
│   │   │   └── 📄 accessibility.css
│   │   ├── 📁 cloister/
│   │   │   └── 📄 cloisters.css
│   │   ├── 📁 fifth/
│   │   │   ├── 📄 fifth.css
│   │   │   └── 📄 learn-more.css
│   │   ├── 📁 our-experience/
│   │   │   └── 📄 our-experience.css
│   │   └── 📁 visit/
│   │       └── 📄 visit.css
│   └── 📁 ticket/
│       ├── 📁 admission/
│       │   └── 📄 admission.css
│       ├── 📁 cart/
│       │   └── 📄 cart.css
│       ├── 📁 checkout/
│       │   └── 📄 checkout.css
│       └── 📄 ticket.css
│   👉 Stats: Files: 37 | Chars: 180,423 | Lines: 8,411 | Complexity: HIGH
│
├── 📁 pages/
│   └── 📁 art/
│       ├── 📄 index.css
│       └── 📄 show.css
│   👉 Stats: Files: 2 | Chars: 13,882 | Lines: 744 | Complexity: LOW
│
├── 📁 tickets/
│   └── 📄 index.css
│   👉 Stats: Files: 1 | Chars: 1,786 | Lines: 111 | Complexity: LOW
│
├── 📄 admin-clean.css
├── 📄 admin-new.css
├── 📄 admin.css
├── 📄 app.css
├── 📄 layout.css
└── 📄 utilities.css
    👉 Root Files Stats: Files: 6 | Chars: 31,188 | Lines: 1,684 | Complexity: LOW

==================================================
DEEP SCAN: resources/js/
==================================================
📁 resources/js/
│
├── 📁 admin/
│   ├── 📁 payment/
│   │   └── 📄 index.js
│   └── 📁 ticket-analytics/
│       └── 📄 index.js
│   👉 Stats: Files: 2 | Chars: 16,165 | Lines: 538 | Complexity: LOW
│
├── 📄 admin.js
├── 📄 app.js
└── 📄 bootstrap.js
    👉 Root Files Stats: Files: 3 | Chars: 1,082 | Lines: 41 | Complexity: LOW

==================================================
SIZE MAP (DESCENDING ORDER)
==================================================
- resources/views/admin/artworks :
  Files: 3 | Characters: 251,003 | Estimated Lines: 4,314 | Complexity: HIGH
- app/Http/Controllers/Admin :
  Files: 28 | Characters: 241,780 | Estimated Lines: 6,169 | Complexity: HIGH
- resources/css/ordinary :
  Files: 37 | Characters: 180,423 | Estimated Lines: 8,411 | Complexity: HIGH
- resources/css/admin :
  Files: 51 | Characters: 151,613 | Estimated Lines: 7,852 | Complexity: HIGH
- app/Http/Controllers/[Root Files] :
  Files: 17 | Characters: 139,439 | Estimated Lines: 3,593 | Complexity: MEDIUM
- resources/views/ordinary/art :
  Files: 6 | Characters: 125,451 | Estimated Lines: 2,681 | Complexity: MEDIUM
- resources/views/ordinary/plan-your-visit :
  Files: 8 | Characters: 95,206 | Estimated Lines: 1,768 | Complexity: MEDIUM
- resources/views/admin/orders :
  Files: 3 | Characters: 75,906 | Estimated Lines: 2,278 | Complexity: MEDIUM
- resources/views/ordinary/account :
  Files: 8 | Characters: 53,994 | Estimated Lines: 1,295 | Complexity: MEDIUM
- resources/views/admin/dashboard :
  Files: 4 | Characters: 51,082 | Estimated Lines: 1,314 | Complexity: MEDIUM
- resources/views/ordinary/member :
  Files: 3 | Characters: 47,862 | Estimated Lines: 929 | Complexity: LOW
- resources/views/admin/tickets :
  Files: 2 | Characters: 45,577 | Estimated Lines: 922 | Complexity: LOW
- resources/views/ordinary/admission :
  Files: 2 | Characters: 43,102 | Estimated Lines: 901 | Complexity: LOW
- resources/views/admin/art :
  Files: 4 | Characters: 36,492 | Estimated Lines: 796 | Complexity: LOW
- resources/views/ordinary/checkout :
  Files: 5 | Characters: 32,865 | Estimated Lines: 672 | Complexity: LOW
- resources/css/[Root Files] :
  Files: 6 | Characters: 31,188 | Estimated Lines: 1,684 | Complexity: LOW
- resources/views/admin/ticket-analytics :
  Files: 3 | Characters: 22,549 | Estimated Lines: 485 | Complexity: LOW
- resources/views/admin/constituents :
  Files: 2 | Characters: 22,030 | Estimated Lines: 591 | Complexity: LOW
- resources/views/admin/users :
  Files: 2 | Characters: 21,121 | Estimated Lines: 452 | Complexity: LOW
- resources/views/admin/departments :
  Files: 3 | Characters: 18,593 | Estimated Lines: 600 | Complexity: LOW
- resources/views/admin/components :
  Files: 21 | Characters: 17,832 | Estimated Lines: 485 | Complexity: LOW
- resources/views/ordinary/order :
  Files: 2 | Characters: 16,586 | Estimated Lines: 348 | Complexity: LOW
- resources/views/admin/payment :
  Files: 1 | Characters: 16,557 | Estimated Lines: 313 | Complexity: LOW
- resources/js/admin :
  Files: 2 | Characters: 16,165 | Estimated Lines: 538 | Complexity: LOW
- resources/views/admin/payments :
  Files: 1 | Characters: 15,487 | Estimated Lines: 593 | Complexity: LOW
- resources/css/art :
  Files: 7 | Characters: 14,034 | Estimated Lines: 777 | Complexity: LOW
- resources/css/pages :
  Files: 2 | Characters: 13,882 | Estimated Lines: 744 | Complexity: LOW
- resources/views/admin/locations :
  Files: 2 | Characters: 12,528 | Estimated Lines: 403 | Complexity: LOW
- resources/views/ordinary/home :
  Files: 1 | Characters: 10,883 | Estimated Lines: 199 | Complexity: LOW
- resources/views/admin/tags :
  Files: 3 | Characters: 9,992 | Estimated Lines: 232 | Complexity: LOW
- resources/views/admin/categories :
  Files: 2 | Characters: 9,587 | Estimated Lines: 153 | Complexity: LOW
- resources/views/ordinary/ticket :
  Files: 5 | Characters: 9,499 | Estimated Lines: 242 | Complexity: LOW
- resources/views/admin/posts :
  Files: 1 | Characters: 8,953 | Estimated Lines: 122 | Complexity: LOW
- resources/views/admin/classifications :
  Files: 3 | Characters: 8,636 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/repositories :
  Files: 3 | Characters: 8,482 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/object-types :
  Files: 3 | Characters: 8,458 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/portfolios :
  Files: 3 | Characters: 8,426 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/materials :
  Files: 3 | Characters: 8,384 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/dynasties :
  Files: 3 | Characters: 8,356 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/cultures :
  Files: 3 | Characters: 8,342 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/mediums :
  Files: 3 | Characters: 8,300 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/periods :
  Files: 3 | Characters: 8,300 | Estimated Lines: 200 | Complexity: LOW
- resources/views/admin/reigns :
  Files: 3 | Characters: 8,258 | Estimated Lines: 200 | Complexity: LOW
- resources/views/ordinary/membership :
  Files: 2 | Characters: 6,265 | Estimated Lines: 150 | Complexity: LOW
- resources/views/admin/layout :
  Files: 1 | Characters: 5,998 | Estimated Lines: 146 | Complexity: LOW
- resources/css/orders :
  Files: 2 | Characters: 4,990 | Estimated Lines: 298 | Complexity: LOW
- resources/views/admin/reports :
  Files: 1 | Characters: 4,862 | Estimated Lines: 139 | Complexity: LOW
- resources/views/admin/analytics :
  Files: 1 | Characters: 4,658 | Estimated Lines: 134 | Complexity: LOW
- resources/views/admin/exhibitions :
  Files: 1 | Characters: 4,314 | Estimated Lines: 100 | Complexity: LOW
- resources/css/components :
  Files: 3 | Characters: 4,244 | Estimated Lines: 280 | Complexity: LOW
- resources/views/admin/settings :
  Files: 1 | Characters: 2,969 | Estimated Lines: 93 | Complexity: LOW
- resources/views/admin/layouts-admin :
  Files: 1 | Characters: 2,305 | Estimated Lines: 65 | Complexity: LOW
- resources/css/tickets :
  Files: 1 | Characters: 1,786 | Estimated Lines: 111 | Complexity: LOW
- resources/views/admin/[Root Files] :
  Files: 1 | Characters: 1,437 | Estimated Lines: 43 | Complexity: LOW
- resources/views/admin/layouts :
  Files: 1 | Characters: 1,120 | Estimated Lines: 38 | Complexity: LOW
- resources/js/[Root Files] :
  Files: 3 | Characters: 1,082 | Estimated Lines: 41 | Complexity: LOW
- resources/views/ordinary/about :
  Files: 2 | Characters: 642 | Estimated Lines: 31 | Complexity: LOW
- resources/css/BuyTicketCss :
  Files: 1 | Characters: 390 | Estimated Lines: 12 | Complexity: LOW
- resources/views/ordinary/auth :
  Files: 1 | Characters: 275 | Estimated Lines: 9 | Complexity: LOW
- resources/css/layouts :
  Files: 2 | Characters: 249 | Estimated Lines: 11 | Complexity: LOW
```

# MODULE MAP

1. **Admin UI Modules**: Berisi 33 child folders untuk fungsi admin seperti artworks, orders, dashboard, tickets, dsb. Terpusat di `resources/views/admin/` dan `resources/css/admin/`.
2. **Public UI Modules**: Berisi 12 child folders untuk fungsi publik, dengan konsentrasi tertinggi pada koleksi seni (`art/`), edukasi (`plan-your-visit/`), dan transaksi (`checkout/`). Terpusat di `resources/views/ordinary/` dan `resources/css/ordinary/`.
3. **Core Controllers**: Logika sistem (Medium complexity).
4. **Admin Controllers**: Logika khusus admin di dalam subfolder `Admin/` (High complexity).

# SPLIT POTENTIAL

Dari hasil tree di atas, area yang realistis untuk dipecah:

1. `resources/views/admin/artworks/` (Bisa di-assign ke 1 orang khusus).
2. `resources/views/admin/orders/` & `dashboard/` (Bisa di-assign ke 1 orang khusus).
3. Sisa dari `resources/views/admin/` (Ada 30+ folder kecil, bisa disatukan ke 1 orang).
4. `resources/views/ordinary/art/` & `plan-your-visit/` (Cukup masif untuk 1 spesialis UI Frontend).
5. Sisa dari `resources/views/ordinary/` (Bisa dipegang 1 orang pendukung Frontend).
