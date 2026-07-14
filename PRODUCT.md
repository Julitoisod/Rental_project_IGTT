# Product

## Register

brand

## Users

- **Customers** in Dinagat Islands, PH browsing equipment/items to rent — mostly on mobile, comparing price and availability before signing up.
- **Rental owners** listing inventory and managing bookings.
- **SND/IGTT admins** overseeing users, categories, and wallet top-ups.

The public landing page (`/portal`) is a storefront: its job is to make browsing feel like a familiar online shop (Shopee/Lazada mental model) and convert visitors into registered renters.

## Product Purpose

IGTT (Islands Getaway Travel and Tours) equipment rental platform built on CodeIgniter 4. Customers browse, book, and pay for rentals via an in-app wallet; owners manage items, rentals, and earnings; admins manage the marketplace. Success = visitors find an item and register to rent it.

## Brand Personality

Trustworthy, local, practical. A hardworking island small-business voice — not a slick SaaS. Crimson (#9c0b09) is the committed brand color, used app-wide (admin crimson, customer blue, owner green accents for portals).

## Anti-references

- Generic SaaS landing pages (gradient blobs, hero metrics, feature-card walls).
- Beige/minimal editorial aesthetics — this is a working shop, not a magazine.
- Anything that hides the products; items ARE the page.

## Design Principles

1. **Products first** — the item grid is the hero; everything else supports it.
2. **Shop grammar** — search, categories, prices, ratings, stock. Visitors already know how online shops work; don't reinvent it.
3. **Real data over decoration** — ratings, rental counts, and reviews come from the DB, never faked.
4. **Mobile is the primary viewport.**
5. **Crimson is identity** — keep it; don't drift to trendy palettes.

## Accessibility & Inclusion

- Body text ≥4.5:1 contrast; interactive targets ≥44px on mobile.
- `prefers-reduced-motion` respected for all animation.
- Semantic HTML, alt text on item imagery.
