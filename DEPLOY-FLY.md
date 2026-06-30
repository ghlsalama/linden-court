# Deploy to Fly.io (free trial, no card)

Fly.io runs the bundled WordPress + MariaDB image as one machine with a
persistent volume. The free trial gives a **one-time $5 credit** with **no card
upfront**, and the site sleeps when idle to stretch the credit.

## One-time setup

```bash
brew install flyctl                 # 1. install the Fly CLI
fly auth signup                     # 2. sign up — no card needed for the trial credit

cd accommodation-booking-demo
fly launch --no-deploy              # 3. create the app (uses fly.toml)
                                    #    app name: linden-court  ->  linden-court.fly.dev
fly volumes create linden_data --region lhr --size 1   # 4. persistent DB volume
fly secrets set WP_ADMIN_PASSWORD="YourStrongPassword" # 5. WordPress admin password
fly deploy                          # 6. build + deploy
```

First boot auto-installs WordPress, activates the theme + Elementor/ACF/MotoPress,
sets GBP, and seeds the 5 apartments, pages, testimonials, services and nav menu
(guarded by `/data/.seeded`, so it runs once).

## After it's live

- **Site:** https://linden-court.fly.dev
- **Admin:** https://linden-court.fly.dev/wp-admin  →  `admin` / your `WP_ADMIN_PASSWORD`
- If the app name differs, sync the URL: `fly secrets set WP_URL="https://<your-app>.fly.dev"` then `fly deploy`.

## Caveats

- The **$5 credit is one-time**; sleeping when idle makes it last weeks/months for a
  low-traffic demo. Add a card only if you want it always-on or higher resources.
- `fly.toml` requests **512 MB** (256 MB is too tight for WP + MariaDB together). If
  Fly requires a card to allocate 512 MB, add one (charged from the credit) or accept
  256 MB at OOM risk.
- Photography is Lorem Picsum placeholder — swap via wp-admin before any real client use.
