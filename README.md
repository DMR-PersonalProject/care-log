# Care Log

A daily caregiving journal that replaces the paper notebook. It is used to log
the daily care of a loved one with dementia: meals, medicine, bathing,
toileting, exercise, dentures, sleep, stories and conversations, and vital
signs.

It runs on your own computer only (at `http://127.0.0.1:8000`). There are no
accounts or passwords, and nothing is sent over the internet.

## What it does

- **Dashboard**: today at a glance (meals, medicine, last bath, urination and
  dentures), the latest vitals with readings outside the usual range in red, a
  7 or 30 day vitals chart, and the most recent entries.
- **Daily Log**: pick a day, add entries at the top, and see the whole day in
  time order. Every entry can be viewed, edited or deleted.
- **All Entries**: every entry, newest first, 15 per page, with search across
  notes, activity and status.
- **Reports**: choose a date range, activity and status to see counts, average
  vitals and the matching entries by day. Reports can be printed or downloaded
  as a PDF.

Dates are shown as mm/dd/yyyy and times as 12-hour with AM/PM. It works on a
desktop, tablet or phone.

## What you need

- PHP 8.3 or newer, with the `pdo_sqlite`, `mbstring` and `dom` extensions
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) and npm

## First-time setup

From the project folder:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
```

On Windows PowerShell, use these instead of `cp` and `touch`:

```powershell
Copy-Item .env.example .env
New-Item database/database.sqlite -ItemType File
```

`--seed` adds a few sample entries so you can try the app. To start with an
empty log, run `php artisan migrate` without `--seed`.

## Running the app

```bash
php artisan serve
```

Then open <http://127.0.0.1:8000> in a browser. Leave the window running while
you use the app, and press `Ctrl+C` in it to stop.

If you are changing the page styles, also run `npm run dev` in a second window
so changes show up straight away.

## Backing up your data

Everything is stored in one file: **`database/database.sqlite`**.

To back up:

1. Stop the app (`Ctrl+C` in the window running `php artisan serve`).
2. Copy `database/database.sqlite` somewhere safe, such as a USB drive or a
   cloud folder. Putting the date in the name helps, for example
   `care-log-2026-10-23.sqlite`.

To restore, stop the app, copy your backup back to `database/database.sqlite`
(replacing the one there), and start the app again.

Back up regularly, for example once a week. This file is not stored in Git.

## Settings

- **Timezone**: `Asia/Manila`, set in `config/app.php` (`'timezone'`).
- **App name**: `APP_NAME` in `.env`.
- **Usual vitals ranges** (used for the red High/Low warnings): in
  `app/Support/Vitals.php`. These are general adult ranges; change them to what
  her doctor recommends.

## Adding an activity type

All activity types are listed in one place, `app/Enums/Activity.php`. To add
one, such as Bowel Movement, add a case:

```php
case BowelMovement = 'bowel_movement';
```

and, if the name should read differently from the case name, a label in
`label()`:

```php
self::BowelMovement => 'Bowel Movement',
```

It then appears in every dropdown, the search and the reports. No database
change is needed.

## Running the tests

```bash
php artisan test
```
