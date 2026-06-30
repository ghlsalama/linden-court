# Linden Court Serviced Apartments — WordPress Booking Demo

A complete, fully-functional accommodation booking website, built to serve as a
portfolio piece for accommodation-booking jobs. Real availability search, live
£ pricing, seasonal rates, services, and a working booking flow — on WordPress.

> **Note:** "Linden Court" is a fictional demo brand. Photography is placeholder
> stock (Lorem Picsum) and is intended to be swapped for real apartment imagery.

## What's in here

| Layer | What |
|---|---|
| **Stack** | Docker: WordPress (PHP 8.2 + Apache) + MariaDB 10.11 + phpMyAdmin |
| **Theme** | `linden-court` — a custom dark-luxury child theme on **Hello Elementor** (Cormorant Garamond + Inter, charcoal/brass palette, mobile-first) |
| **Builder** | **Elementor** — the About / Amenities / Contact pages are genuine Elementor documents |
| **Custom fields** | **Advanced Custom Fields** — testimonials (rating, stay) via local JSON sync |
| **Booking engine** | **MotoPress Hotel Booking** — 5 apartments, bookable room units, all-year season, active rates, GBP currency, services, availability search, single-page booking form |
| **Seed scripts** | `scripts/seed-motopress.php` (rooms/rates/season) + `scripts/seed-content.php` (Elementor pages, testimonials, services, nav) |

## Run it (fresh)

```bash
cp .env.example .env          # edit credentials
docker compose up -d --build  # WordPress + MariaDB + phpMyAdmin
./setup.sh                    # WP install + theme + Elementor/ACF/MotoPress

# seed content (run from host; scripts are copied into the container):
docker compose cp scripts/seed-motopress.php wordpress:/tmp/s.php
docker compose exec wordpress wp eval-file /tmp/s.php --allow-root
docker compose cp scripts/seed-content.php   wordpress:/tmp/c.php
docker compose exec wordpress wp eval-file /tmp/c.php --allow-root
docker compose exec wordpress wp rewrite flush --allow-root
```

- **Site:** http://localhost:8080
- **Admin:** http://localhost:8080/wp-admin (see `.env` for creds)
- **DB UI:** http://localhost:8081

## Pages

| URL | What |
|---|---|
| `/` | Home — hero, statement, featured apartments, amenities, testimonials, CTA (theme template) |
| `/accommodation/` | Apartments listing with live availability search + all 5 rooms (custom archive template) |
| `/accommodation/<slug>/` | Single apartment — gallery, details, live availability calendar + booking form |
| `/about/` · `/amenities/` · `/contact/` | Elementor-built interior pages (Contact embeds the availability-search shortcode) |

## Teardown

```bash
docker compose down -v   # -v wipes the DB + WP data volumes
```

## Why a child theme instead of pure Elementor?

The brand's premium look (fixed translucent header, hero, room cards, styled
MotoPress booking form, mobile menu) is implemented in the child theme for full
control and performance; Elementor is used for the editable marketing pages so
content stays editable in the visual editor. ACF local-JSON keeps the field
groups version-controlled.

## License

Demo project. Plugin/theme software retains upstream licenses (GPL).
