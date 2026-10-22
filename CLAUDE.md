# Care Log: Daily Caregiving Journal

## What this app is
A local, single-user web app that replaces a paper notebook. My mother uses it to log the daily routine of caring for her mother, who has dementia: meals, medicine, bathing, toileting, exercise, dentures, sleep, stories/conversations, and vital signs. She is not technical, so the UI must be simple, large, and forgiving.

- Runs locally only (127.0.0.1). **No authentication, no users, no registration.**
- Used on a desktop/laptop mostly, sometimes on a phone or tablet, so it must be responsive.
- Dates are displayed as **mm/dd/yyyy**; times as 12-hour with AM/PM. Store dates as ISO `YYYY-MM-DD` in the database.
- App timezone: `Asia/Manila` (set in `config/app.php`).

## Tech stack
- Laravel (latest stable), PHP 8.3+
- SQLite (single file, easy to back up)
- Blade + Tailwind CSS + Alpine.js (keep JS minimal; no SPA)
- `barryvdh/laravel-dompdf` for PDF generation
- Chart.js for dashboard charts
- Pest or PHPUnit for feature tests

## Data model
Single table `care_entries`, one row per logged event:

| column | type | notes |
|---|---|---|
| id | bigint | PK |
| entry_date | date | the "primary day" |
| entry_time | time | |
| activity | string | one of the activity types below |
| status | string | one of the statuses below |
| description | text, nullable | free-text notes |
| systolic | unsigned smallint, nullable | mmHg |
| diastolic | unsigned smallint, nullable | mmHg |
| heart_rate | unsigned smallint, nullable | bpm |
| spo2 | unsigned tinyint, nullable | % |
| temperature | decimal(4,1), nullable | °C |
| timestamps | | |

Index `entry_date` and `(entry_date, entry_time)`.

Vitals are optional on any row. Any entry can carry them, and "Vitals" is also its own activity type for entries that are only a vitals check.

### Activity types (use a PHP backed enum)
Breakfast, Lunch, Dinner, Snack, Medicine, Bath, Urination, Pedal (exercise), Dentures, Sleep, Story, Vitals, Other.
Keep the list in one place so it is easy to add more (e.g. Bowel Movement, Mood/Behavior).

### Statuses (PHP backed enum)
Done, Partial, Refused, Missed, Needs Attention.

## Screens and features
1. **Daily Log (home page after Dashboard)**
   - Big heading with the selected date in mm/dd/yyyy, plus Previous / Today / Next buttons and a date picker.
   - A quick-add form at the top (time defaults to now, activity dropdown, status, description, and a collapsible "Vitals" section).
   - Below it, a spreadsheet-style table for that day, sorted by time: Time | Activity | Status | Description | Vitals | Actions (Edit / Delete).
   - On small screens, the table becomes stacked cards.
2. **Full CRUD** for entries: create, view, edit, delete (with a confirmation dialog). Validate input (e.g. systolic 50-250, diastolic 30-150, heart rate 20-250, SpO2 50-100, temperature 30-45).
3. **Dashboard**: today at a glance (meals done, medicines given, last bath, last urination, last dentures entry), latest vitals with out-of-range highlighting, a 7/30-day vitals trend chart, and recent entries.
4. **Search**: across description, activity, and status, with highlighted matches.
5. **Pagination**: on the all-entries list and report results (15 per page). The daily log shows the whole day.
6. **Reports**: filter by date range, activity, and status. Show a summary (counts per activity, average vitals) and the matching entries grouped by day.
7. **Print**: a print-friendly report view (`@media print` stylesheet, no nav or buttons, page-break rules, header with the date range).
8. **PDF**: download the same report as PDF via dompdf (landscape, readable table, page numbers).
9. **Responsive**: works from a 360px phone up to desktop. Touch targets at least 44px, large readable font.

## Conventions
- Follow Laravel conventions: resource controller, Form Request classes for validation, Eloquent model with casts, enums for activity and status.
- Keep controllers thin. Put report-filtering logic in a query scope or small service class.
- No authentication middleware anywhere. Do not add Breeze/Jetstream/Fortify.
- Seed a few sample entries with a factory/seeder so the UI can be previewed.
- Plain, friendly UI copy (e.g. "Add entry", "Save", "Delete this entry?"). No jargon.
- Comment non-obvious code only. Do not over-engineer.

## Commands
- Install: `composer install && npm install`
- Setup: `cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate --seed`
- Run: `php artisan serve` (and `npm run dev` for assets)
- Test: `php artisan test`

## Working agreements
- Build in small slices and tell me how to verify each one. Do not build everything at once.
- Order: scaffold, migration and model, daily log with CRUD, search and pagination, dashboard, reports, print, PDF, polish and responsive pass.
- Commit after each working slice.
- Ask before adding any new package beyond the stack above.
- Back up by copying `database/database.sqlite`. Mention this in the README at the end.

## Git workflow
- Remote `origin` is my private GitHub repo `care-log`; the branch is `main`.
- At the end of each milestone, after tests pass:
  1. Run `git status` and show me the list of files.
  2. Commit with a short imperative subject and a body explaining what changed and why.
  3. Tag it `milestone-N`.
  4. Push with `git push origin main --tags`.
- Never commit `.env`, the SQLite database, `vendor`, `node_modules` or `public/build`.
