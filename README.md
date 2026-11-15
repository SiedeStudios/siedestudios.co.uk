# SiedeStudios

Portfolio, client payments and studio tools for [siedestudios.co.uk](https://siedestudios.co.uk), built with **Laravel 13**, **Inertia 3 + Vue 3**, **Tailwind CSS 4** and **GSAP**.

- **Public site:** Home, Work (projects by category, with screenshot galleries), Services, Contact, and Pay (invoices via PayPal).
- **Hidden tools:** a private pastebin (`/s/{id}`) and testimonial invite links (`/testimonial/{token}`).
- **Admin panel** at `/admin`, with **Discord-only sign-in**. It has a dashboard, projects, categories, services, testimonials, invoices, the PayPal IPN log, shares, messages and settings. Settings are stored in the database and include logo, favicon, social image, accent colour and PayPal email uploads/fields.

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/SiedeStudios/siedestudios.co.uk?ref=redesign)

## Try it in your browser (GitHub Codespaces)

Open **<https://codespaces.new/SiedeStudios/siedestudios.co.uk?ref=redesign>** and click **Create codespace**. The first build takes about 3–5 minutes. It starts PHP and a MySQL 8 container, installs everything, loads demo content, and opens the site on port 8000.

- Admin: go to `/admin` and click **Demo sign-in**. This button only exists in Codespaces and local dev (`APP_ENV=local` with `DEV_LOGIN=true`). It is never available on a production server.
- The PayPal checkout runs in sandbox mode with a placeholder business email, so it won't take real payments.
- If the site isn't running after the codespace restarts, run `bash .devcontainer/serve.sh`.

---

## Install on a server

On a fresh **Ubuntu 22.04/24.04** or **Debian 12/13** server, as root:

```bash
apt-get update && apt-get install -y git
git clone -b redesign https://github.com/SiedeStudios/siedestudios.co.uk.git /var/www/siedestudios
BRANCH=redesign bash /var/www/siedestudios/deploy/install.sh
```

(After the redesign is merged into `main`, drop `-b redesign` and `BRANCH=redesign`.)

The installer asks for your domain, a Let's Encrypt email, the server IP (default `138.201.130.254`), a **Cloudflare API token**, your Discord app keys and your admin Discord ID. It then:

1. Installs Nginx, PHP 8.3 (FPM), **MariaDB**, Node 22, Composer and Certbot with its Cloudflare plugin.
2. **Points your DNS at the server through the Cloudflare API.** It creates or updates the A records for the domain and `www` and removes old CNAME/AAAA records that would send traffic elsewhere. Other records, such as MX for email, are left alone.
3. **Creates its own MariaDB database and user**, with a random 32-character password, and checks that the user can connect. It then writes a production `.env` with `APP_DEBUG=false` and secure cookies.
4. Installs dependencies, builds the front-end, migrates and seeds starter content.
5. Configures an Nginx site for `/var/www/siedestudios/public` with gzip, caching and security headers.
6. **Issues a Let's Encrypt SSL certificate** for the domain and `www`, redirects HTTP → HTTPS, and verifies automatic renewal. With a Cloudflare token it uses the DNS challenge, so it works straight away even before DNS has propagated, and also with the Cloudflare proxy on.
7. Adds the Laravel scheduler cron job (it prunes expired shares).

It's safe to re-run. The `.env`, database, DNS records and certificate are kept or updated in place.

### Cloudflare API token

In Cloudflare, go to **My Profile → API Tokens → Create Token** and use the **Edit zone DNS** template. Under **Zone Resources**, choose **Include → Specific zone → siedestudios.co.uk**. Paste the token when the installer asks. It's stored only in `/root/.secrets/cloudflare.ini` (root-only), so certificate renewals keep working.

The DNS records are created **DNS only** (grey cloud) by default. Answer `yes` to the proxy question, or set `CF_PROXIED=yes`, to put the site behind Cloudflare's proxy (orange cloud). The installer then also tries to set Cloudflare's SSL mode to **Full (strict)**. That needs the token to have **Zone Settings: Edit** too; otherwise set it in the dashboard yourself.

### Unattended install

Every prompt can be answered with an environment variable:

```bash
BRANCH=redesign DOMAIN=siedestudios.co.uk LE_EMAIL=you@example.com \
SERVER_IP=138.201.130.254 CF_API_TOKEN=... CF_PROXIED=no \
DISCORD_CLIENT_ID=... DISCORD_CLIENT_SECRET=... ADMIN_DISCORD_IDS=123456789012345678 \
NONINTERACTIVE=1 bash /var/www/siedestudios/deploy/install.sh
```

If the MariaDB root account uses a password instead of the default socket login, add `DB_ROOT_PASSWORD=...`. Use `DB_NAME`, `DB_USER` and `DB_PASS` to choose your own database name, user and password.

> **Private repository?** Clone over SSH with a [deploy key](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/managing-deploy-keys) (`git clone git@github.com:SiedeStudios/siedestudios.co.uk.git …`) and pass `REPO_URL` the same way.

### Deploying updates

```bash
sudo bash /var/www/siedestudios/deploy/update.sh
```

This enables maintenance mode, pulls the branch, reinstalls dependencies, rebuilds assets, migrates, re-warms caches, then brings the site back up.

---

## Discord login (admin panel)

There are no passwords. The admin panel only accepts Discord accounts whose user ID is on the allowlist.

1. Create an application at <https://discord.com/developers/applications>.
2. Under **OAuth2 → Redirects**, add `https://siedestudios.co.uk/auth/discord/callback`.
3. Put the client ID and secret in `.env`:
   ```dotenv
   DISCORD_CLIENT_ID=...
   DISCORD_CLIENT_SECRET=...
   ADMIN_DISCORD_IDS=123456789012345678
   ```
4. To find your Discord user ID: enable **Settings → Advanced → Developer Mode**, then right-click your name and choose **Copy User ID**.

More admins can be added later in **Settings → Access**. IDs in `.env` are always allowed. Removing an ID signs that account out on its next request.

---

## Payments (PayPal IPN)

1. In **Admin → Settings → Payments**, set your PayPal business email and currency. Leave **Sandbox mode** on while testing.
2. Create an invoice in **Admin → Invoices**. It gets a unique reference such as `SS-2611-K3F9QA` and a pay link: `https://siedestudios.co.uk/pay/SS-2611-K3F9QA`.
3. The client opens the link and pays with PayPal (card payments work via guest checkout).
4. PayPal posts an Instant Payment Notification to `https://siedestudios.co.uk/paypal/ipn`. The site verifies it with PayPal, checks it, and marks the invoice **Paid**. Every notification is recorded in **Admin → Payments**.

The IPN handler in `app/Services/PayPal` is a modernised port of [PHP-PayPal-IPN](https://github.com/imLiaMxo/PHP-PayPal-IPN). Compared with the original:

| Original | Now |
|---|---|
| Posts back to `www.paypal.com` | Uses PayPal's IPN verification hosts `ipnpb.paypal.com` / `ipnpb.sandbox.paypal.com` |
| Forces SSLv3 and ships a 2012 CA bundle, both rejected by PayPal today | Uses TLS 1.2+ and the system CA store |
| No `User-Agent` header (PayPal now requires one) | Sends a `User-Agent` |
| Re-encodes `$_POST`, which can break non-ASCII messages | Echoes the raw body back byte-for-byte |
| Matches the response with `strpos` | Requires an exact `VERIFIED` / `INVALID` body and handles timeouts and outages |
| Leaves the checks to you | Enforces PayPal's checks: `Completed` status, unique `txn_id`, receiver email, and exact amount and currency. It also handles refunds and reversals and locks the invoice row while updating it |

---

## Testimonials

1. **Admin → Testimonials → Invite client.** Enter their name, company and role, and optionally upload an avatar and attach the testimonial to a project.
2. Copy the private link (`/testimonial/{token}`) and send it to the client.
3. They choose a star rating, write their testimonial and submit. The link only works once.
4. Approve or reject it from the dashboard or the Testimonials page. Approved testimonials appear on the home page and on the attached project's page.

## Shares (hidden pastebin)

**Admin → Shares → New share.** Paste code or text, pick the syntax, and optionally set a password, an expiry or **burn after reading**. Links look like `/s/ab12cd34` and are unlisted and `noindex`. Viewers get syntax highlighting, copy, download and a raw view at `/s/{id}/raw`.

## Notifications

Add a Discord webhook URL in **Settings → Notifications** to get a channel alert for new enquiries, paid invoices and testimonial submissions.

---

## Local development

Requirements: PHP 8.3+, Composer, Node 20.19+ (or 22.12+), and MariaDB 10.6+ (or MySQL 8).

```bash
mysql -uroot -e "CREATE DATABASE siedestudios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
composer install
npm install
cp .env.example .env                      # then set DB_USERNAME / DB_PASSWORD
php artisan key:generate
php artisan migrate --seed
php artisan db:seed --class=DemoSeeder   # optional sample testimonials, invoices, shares (local only)
php artisan storage:link
composer run dev                          # or: php artisan serve & npm run dev
```

### Tests

```bash
php artisan test
```

The feature tests cover the IPN rules (verification, amount, currency, receiver, duplicates, refunds, outages), Discord sign-in and the allowlist, public pages and forms, invoices, shares, testimonials and settings uploads.

### Project layout

```
app/Services/PayPal/     IPN listener + invoice payment processor
app/Support/Settings.php Database-backed settings (schema in config/site.php)
app/Http/Controllers/    Public pages, Discord auth, admin (Admin/*)
resources/js/pages/      Inertia pages (public + Admin/*)
resources/js/components/ Logo, aurora, header/footer, admin UI pieces
resources/js/lib/        GSAP/Lenis motion helpers, formatting, syntax highlighting
deploy/                  install.sh (server + SSL) and update.sh
```

All editable settings are declared in `config/site.php`. Adding a field there adds it to the admin Settings page automatically, along with its validation.
