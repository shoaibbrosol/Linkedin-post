# LinkedIn Daily Post Automation

Laravel admin panel for creating, scheduling, publishing, retrying, and auditing LinkedIn posts.

## Features

- Email/password authentication with password reset routes.
- LinkedIn account settings with OAuth support and manual token entry.
- Encrypted access and refresh token storage.
- LinkedIn post CRUD with draft, pending, posted, failed, and cancelled statuses.
- Date, status, keyword, and sort filters.
- Monthly calendar view.
- Failed post retry flow with retry limits.
- Posting command and queued publish job.
- Publishing logs with request/response payload storage.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Create your first user at `/register`.

## LinkedIn OAuth

Set these values in `.env`:

```env
LINKEDIN_CLIENT_ID=
LINKEDIN_CLIENT_SECRET=
LINKEDIN_SCOPES="openid profile email w_member_social"
LINKEDIN_MAX_RETRIES=3
```

Use this redirect URL in the LinkedIn developer app:

```text
http://your-domain.test/linkedin/callback
```

## Scheduler And Queue

Run the scheduler every minute in production:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Run a queue worker for publishing jobs:

```bash
php artisan queue:work
```

Manual publish check:

```bash
php artisan linkedin:publish-posts
```

## Notes

Text publishing is implemented through LinkedIn's UGC API. Media upload support is prepared at the UI/database level, but the LinkedIn media upload step should be connected once the target LinkedIn product permissions are approved.
