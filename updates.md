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

---

*This file tracks all major changes to the SND Rental system. Update this log whenever changes are made.*
