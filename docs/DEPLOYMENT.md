# Deployment

## Render

1. Create a Render Docker web service from this repository.
2. Select the Render Docker runtime and use `render.yaml` or configure it manually.
3. Set the Supabase session pooler host and port, database name, least-privilege runtime username/password, and CA certificate settings.
4. Set `APP_URL` to the HTTPS service URL, `APP_ENV=production`, `APP_DEBUG=false`, and secure cookie settings.
5. Deploy and test `/health`, static assets, HTTPS, login, migrations, CRUD, reports, and the PWA.

Do not put Supabase credentials in `render.yaml`, Docker layers, or public files.
