# Deploy to Render

Render doesn't run `docker-compose`, so this project ships a **single
self-contained image** (`render.Dockerfile`) that runs WordPress + MariaDB in one
web service and persists state on a **Render Disk** (`/data`).

> ⚠️ Render Disks require a **paid plan** (Starter ≈ $7/mo). The free tier spins
> down and cannot persist a database — not viable for WordPress.

## One-time setup (Render dashboard)

1. **Push this repo to GitHub** (already done if you followed the README).
2. In Render, **New → Blueprint**, select the GitHub repo. Render reads
   `render.yaml` and proposes a `linden-court` web service.
3. Fill the two secret env vars (`sync: false` in the blueprint, Render prompts):
   - `WORDPRESS_DB_PASSWORD` — any strong value
   - `WP_ADMIN_PASSWORD` — the WordPress admin password (use a strong one)
4. Set **`WP_URL`** to the service's public URL, e.g.
   `https://linden-court.onrender.com` (the subdomain matches the service name
   you choose).
5. **Apply**. On the service → **Settings**:
   - **Health Check Grace Period**: set to **`300`** seconds (first boot runs
     WP install + plugin downloads + content seed; ~1–3 min).
6. **Manual Deploy → Deploy latest commit**, or push a commit to autodeploy.

## First boot (automatic)

The entrypoint (`render-entrypoint.sh`) runs once and:
- initialises MariaDB on the disk,
- installs WordPress + activates the theme + **Elementor / ACF / MotoPress**,
- seeds 5 apartments, rates, pages, testimonials, services, the nav menu,
- sets GBP currency and pretty permalinks.

Subsequent boots skip seeding (guarded by `/data/.seeded`). Every boot keeps
`home`/`siteurl` synced to `WP_URL`.

## After it's live

- Visit the public URL → the site should appear.
- Admin: `https://<your-url>/wp-admin` with `admin` + your `WP_ADMIN_PASSWORD`.
- If pages show without styles on first load, hit **Elementor → Tools →
  Regenerate CSS** in wp-admin (one-time).

## Notes / caveats

- The DB + uploads live on the disk; the container filesystem is rebuilt on each
  deploy (WP core + theme come from the image).
- Photography is placeholder (Lorem Picsum) — swap via wp-admin Media before
  sending to a real client.
- Render sleeps Starter instances after inactivity; first hit after sleep takes
  ~30–60s. Upgrade the plan if you need always-on.
