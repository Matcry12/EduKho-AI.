# UI/UX + Backend Bug Fix Notes

Date: 2026-03-21
Project: EduKho-AI

## Scope
This document lists confirmed issues found by reviewing the current codebase and commands, then proposes concrete fixes.

## Priority Legend
- P0: Breaks core flow or causes runtime failures.
- P1: High-impact functional/security issue.
- P2: UX/accessibility inconsistency.
- P3: Cleanup/maintainability.

## UI/UX Bugs

### 1) P1 - Faded / low-contrast text in dark mode (and inconsistent readability in light mode)
- Symptoms:
  - Text appears washed out or hard to read across cards/tables/forms.
  - Some dark mode pages still render with dark text (`text-gray-900` / `text-gray-950`) on dark backgrounds.
- Root cause:
  - Conflicting theme layers:
    - Global CSS variable theme in `resources/css/app.css`.
    - Extra inline dark overrides in `resources/views/layouts/app.blade.php`.
  - Many Blade files still use hardcoded `text-gray-*` classes that bypass variable-based theming.
- Files involved:
  - `resources/views/layouts/app.blade.php`
  - `resources/css/app.css`
  - multiple views under `resources/views/**` (search shows many `text-gray-900` / `text-gray-950` remnants).
- Fix:
  1. Remove inline dark-mode override block from `layouts/app.blade.php` and keep theme source in `app.css` only.
  2. Replace hardcoded content text classes with tokenized styles (`text-inherit`, `var(--text-primary)`, `var(--text-secondary)` via existing utility/component classes).
  3. Standardize badge/status contrast pairs for both themes.
- Acceptance check:
  - On both Light and Dark modes, body text in tables/forms/cards is clearly readable and AA contrast-compliant.

### 2) P2 - Mixed legacy color classes in status pills/badges
- Symptoms:
  - Some pills are readable, others are too dim depending on state and theme.
- Root cause:
  - Inconsistent combinations like `bg-gray-100 text-gray-950 dark:bg-gray-800 dark:text-white` mixed with other patterns.
- Files (examples):
  - `resources/views/borrow/show.blade.php`
  - `resources/views/teaching-plans/show.blade.php`
  - `resources/views/admin/activity-logs/show.blade.php`
  - `resources/views/admin/maintenance/show.blade.php`
- Fix:
  - Normalize status chips to one pattern set (light `100/800`, dark `900/200` tone pairs).

### 3) P2 - Theme state managed in two places (`html` and `body`)
- Symptoms:
  - Potential class sync issues and harder debugging.
- Root cause:
  - `darkMode` class binding exists in both `<html ... :class="{ 'dark': darkMode }">` and `<body ... :class="{ 'dark': darkMode }">`.
- Files:
  - `resources/views/layouts/app.blade.php`
  - `resources/views/layouts/guest.blade.php`
- Fix:
  - Keep dark-mode class on `<html>` only.
  - Remove redundant body-level dark class binding.

### 4) P3 - Tailwind semantic drift (old grayscale class usage)
- Symptoms:
  - Visual inconsistency across pages despite global theme tokens.
- Root cause:
  - Legacy hardcoded grays remain in many views, bypassing design token system.
- Fix:
  - Sweep and replace text colors used for normal content with theme token classes.

## Backend Bugs

### 1) P0 - Scheduler references missing command `ai:cleanup-logs`
- Evidence:
  - `routes/console.php` schedules: `Schedule::command('ai:cleanup-logs --days=90')->monthly();`
  - Running command fails: `php artisan ai:cleanup-logs --days=90` -> `There are no commands defined in the "ai" namespace.`
- Impact:
  - Scheduled task is invalid and will fail when scheduler runs.
- Fix:
  - Either implement `ai:cleanup-logs` command, or remove this schedule entry until command exists.

### 2) P1 - API borrow creation lacks conflict detection
- File:
  - `app/Http/Controllers/Api/BorrowApiController.php` (`store`)
- Issue:
  - API path checks only current availability, but does not run date-window conflict validation used in web flow.
- Impact:
  - API consumers can create overlapping bookings that UI path would reject.
- Fix:
  - Apply same conflict logic as `BorrowController@store`, with robust quantity-based overlap checks.

### 3) P1 - Conflict calculation counts records, not reserved item quantity
- File:
  - `app/Http/Controllers/Api/BorrowApiController.php` (`checkConflict`)
- Issue:
  - `conflictingRecords` uses `count()` on borrow records, not number of conflicting equipment items.
- Impact:
  - Underestimates conflicts when one borrow record contains multiple items.
- Fix:
  - Compute occupied item count from `BorrowDetail` (or distinct item IDs) inside requested window, then compare against total item pool.

### 4) P1 - 2FA verification endpoint has no rate limit and misses login activity log
- Files:
  - `routes/web.php` (two-factor routes)
  - `app/Http/Controllers/TwoFactorController.php` (`verify`)
- Issue:
  - No throttling on 2FA verify attempts.
  - `ActivityLogger::logLogin()` is not called after successful 2FA login.
- Impact:
  - Brute-force risk on OTP and incomplete audit trail for 2FA users.
- Fix:
  1. Add route-level throttle middleware to `/two-factor-challenge` POST.
  2. Log successful 2FA logins consistently with normal login flow.

### 5) P2 - Permission seed data is not included in main seeding flow
- Files:
  - `database/seeders/DatabaseSeeder.php`
  - `database/seeders/PermissionSeeder.php`
- Issue:
  - `PermissionSeeder` exists but is not called from `DatabaseSeeder`.
- Impact:
  - Fresh seeded environments may miss role-permission mappings.
- Fix:
  - Add `$this->call(PermissionSeeder::class);` in `DatabaseSeeder::run()`.

### 6) P2 - Test execution tooling not available in current install
- Evidence:
  - `php artisan test` => command not defined.
  - `vendor/bin/phpunit` not present.
  - `composer show` does not list dev test packages.
- Impact:
  - Cannot run automated tests locally from this environment.
- Fix:
  - Install dev dependencies (`composer install` without `--no-dev`) and re-run test commands.

## Recommended Implementation Order
1. P0 scheduler fix (`ai:cleanup-logs` mismatch).
2. P1 booking conflict consistency (`BorrowApiController` store/checkConflict).
3. P1 security/audit hardening for 2FA route + logging.
4. P1/P2 UI contrast cleanup (remove inline dark overrides + normalize text tokens).
5. P2 seeding and test-tooling setup.

## Verification Checklist
- [ ] `php artisan schedule:list` and scheduled commands run without unknown-command errors.
- [ ] API and web booking flows reject the same conflict scenarios.
- [ ] 2FA verify route enforces rate limits and successful OTP login appears in activity logs.
- [ ] Dark + light mode text remains readable across dashboard, tables, forms, badges.
- [ ] Fresh `migrate:fresh --seed` includes permission mappings.
- [ ] Test commands are executable (`php artisan test` or `vendor/bin/phpunit`).
