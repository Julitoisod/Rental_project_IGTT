# RentalProject (IGTT) — Claude instructions

CodeIgniter 4 equipment-rental app. Public storefront at `/portal`; role portals for customer (blue), owner (green), admin (crimson `#9c0b09`).

Full system reference (architecture, roles, lifecycle, data model, business rules): https://claude.ai/code/artifact/2d5887eb-615d-4f8f-87b0-49e5ea4de518 — fetch with WebFetch when deep system context is needed; update it (same URL) when the system changes materially.

## Environment

- Local URL: **http://rentalproject.localhost** (own vhost — plain `http://localhost` belongs to a DIFFERENT project, gsmms2). `.env` `app.baseURL` must stay pointed at this host.
- XAMPP Apache/MySQL are NOT Windows services. Start with `C:\xampp\apache\bin\httpd.exe` and `C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone`.
- PHP is not on PATH: use `C:\xampp\php\php.exe`.
- Never delete `C:\xampp\mysql\data\ibdata1` to "fix" MySQL — it orphans every InnoDB table. Restore from `writable/backup-rentalproject-2026-07-10.sql` instead.
- Repo is public (github.com/Julitoisod/Rental_project_IGTT): `.env` and `public/uploads/` must stay untracked.

## Proof artifact after every update (required)

After completing any revision/update session that changes app behavior or UI, ALWAYS publish an Artifact proof page before finishing:

1. Capture screenshots of the affected pages with headless Edge, e.g.
   `msedge --headless=new --disable-gpu --hide-scrollbars --virtual-time-budget=8000 --window-size=1440,3400 --screenshot=out.png http://rentalproject.localhost/<page>`
   (Headless clamps window width to ~500px minimum — use 640px for "mobile" captures, not 390.)
2. Resize/compress to JPEG and embed as base64 data URIs (Artifact CSP blocks external images).
3. Publish via the Artifact tool, titled `<Feature> — revision proof`, containing: the screenshots with captions, a "What changed" list (New/Fixed tags), and what was verified.
4. Give the user the artifact link in the final summary.

Design tokens for proof pages: crimson accent `#9c0b09` (light) / `#e0665d` (dark), warm-neutral ground, Segoe UI, both themes via `data-theme` overrides.
