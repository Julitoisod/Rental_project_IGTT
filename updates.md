# System Updates Log

---

## 2026-03-20
- Initial system setup (Users, Categories, Items, Rentals)
- User roles updated (added owner, customer, snd_admin)
- Item image upload support added

## 2026-03-23
- Notifications system added
- Extra images support for items

## 2026-03-24
- Booking status and booking fee added to rentals
- User profile fields added (phone, address, etc.)
- Valid ID upload and verification system
- ID OCR text extraction feature
- Email verification for new accounts
- Owner-based item management (owner_id)
- Rental rate options (hourly, daily, weekly, monthly)

## 2026-03-25
- Reviews and ratings system
- Wallet system (balance, transactions)

## 2026-04-14
- Payment gateways management
- Payment reference tracking for rentals

## 2026-04-16
- Wallet top-up request and approval system

## 2026-05-18
- HOW-TO-RUN.md documentation created
- Updates log file created
- Fixed rental rates showing ₱0 in item detail modal (data attribute naming issue)
- Fixed modal not popping up properly (moved modal to body for correct overlay)
- Fixed 404 on redirect after rental actions (added role-based URL prefixes)
- Fixed rental total cost calculation mismatch (ceil vs floor hours)
- Added payment method selection (Wallet or GCash) popup modal on rental form
- Fixed 404 on review submission (added customer prefix to review store URL)
- Fixed 404 on notification click (added role-based prefix to notification links)
- Fixed all existing rental totals in database (recalculated based on correct pricing)
- Fixed 404 on owner profile page (added profile route to owner group)
- Changed owner login page colors to match system red palette
- Changed section title and button colors to use system accent color
- Added text animations to portal hero section (slide-in, fade-in effects)

## 2026-07-02
- Updated CodeIgniter framework 4.7.0 → 4.7.3 (bug-fix releases; composer audit clean)
- Updated symfony/deprecation-contracts 3.6.0 → 3.7.1
- Added composer.phar to project root (Composer 2.10.2; no global Composer on this machine)
- Moved hardcoded admin secret code from AuthController to .env (`admin.secretCode`) before publishing code to GitHub

## 2026-07-03
- Added dedicated **Manage Items** page: sidebar "All Items" now links to `/owner/items` (the full management view with Add/Edit/Delete, search, filters, grid/list) instead of the read-only dashboard grid
- Fixed Edit/Delete action URLs in `items/index` and ItemController redirects to include the role prefix (`/owner`) — they previously pointed to `/items/*` (no route → 404)
- Added `rolePrefix()` helper to BaseController
- Dashboard "Add Item" button replaced with a "Manage Items" link so all item actions live on the one page
- Fixed 404 after adding a unit: Add/Edit form actions (`items/store`, `items/update`) now include the role prefix; same fix for the items search/filter form, Clear link, and the rental modal URLs
- Added missing owner routes: `POST /owner/rentals/store`, `GET /owner/rentals/check-availability(-range)` so owners can create rentals from the items page
- Fixed crash when deleting an item that has rentals (FK constraint 1451): delete is now blocked with a friendly message suggesting to set quantity to 0 instead — rental/payment history is preserved
- Added Delete button (with confirm dialog) on the Rentals list for returned/cancelled rentals; added matching `POST /admin/rentals/(:num)/delete` route for admins
- Rentals-list Delete button made one-click (confirm dialog removed by request)
- Item delete now cascades: finished rentals (returned/cancelled) and reviews are deleted with the item; deletion is only blocked while the item has ACTIVE rentals/bookings
- "Rental Terms & Details" free-text box on Add/Edit Item replaced with predefined checkboxes (list lives in `ItemController::RENTAL_TERM_OPTIONS`); checked terms are stored in `rental_details` and shown to customers in the item modal
- Removed "Manage Items" button from the dashboard All Items section (sidebar link is the single entry point)
- Fixed 404 on bare `/rentals` and `/rentals/{id}` URLs (used by stored notification links): added role-based redirect routes, same pattern as `/dashboard`
- Extended the bare-path redirects to `/wallet`, `/wallet/topup`, `/profile`, `/notifications` (one loop in Routes.php covers all stored notification links and old views)
- Fixed broken admin wallet approval paths: `/wallet/admin/requests|approve|reject` → correct `/admin/wallet/...` routes (controller redirects + admin_requests view)
- Added global `role_url_prefix()` helper (app/Common.php) and prefixed EVERY remaining bare link/form action across views: wallet, notifications, rentals show/return, customer profile, users, categories, payment gateways, reports, reviews — fixes the `POST wallet/topup` 404 and the whole class of unprefixed-URL 404s
- Added `payment-gateways/for-item` route to owner/admin groups and `users/(:num)/profile(.json)` routes to admin group (shared views link there)
- Added `GET /admin/wallet` (Store Earnings) route — the sidebar shows the link for admins but only owners had the route
- **Full route audit** (script cross-checked every generated URL against every defined route). Fixed all remaining gaps:
  - Controller redirects to bare `/users`, `/categories`, `/payment-gateways` → now role-prefixed via `rolePrefix()`
  - Store Earnings filter form posted to nonexistent `/store-wallet` → role-prefixed `/wallet`
  - Admin group given full rentals management routes (create/store/availability/detail.json/return/activate/confirm-payment/cancel-booking), items CRUD, own profile, and reports CSV export — its pages rendered those buttons but the routes didn't exist
  - Owner group given `rentals/(:num)/cancel-booking` (Cancel Booking button rendered but route missing)
- Smoke-tested 32 pages across all three roles after the audit: all HTTP 200

---

*This file tracks all major changes to the SND Rental system. Update this log whenever changes are made.*
