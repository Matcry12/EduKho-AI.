# Plan chi tiet sua loi mau sac text - EduKho-AI

## Van de goc

Sau nhieu lan thay doi mau bang `sed`, code hien tai bi hon loan:
1. Global CSS rule `color: var(--text-primary)` ghi de len tat ca component
2. Cac class Tailwind bi thay doi hang loat (`text-gray-500` -> `text-gray-900` -> `text-gray-950`)
3. Dark mode override trong `app.blade.php` xung dot voi nhau
4. Pills/badges bi vo hinh trong dark mode

---

## Ke hoach sua - 5 buoc

### Buoc 1: Xoa global CSS rule va cac dark mode override xung dot

**File:** `resources/css/app.css` (dong 5-17)

Xoa:
```css
/* Global text color - all text uses --text-primary for readability */
body, p, span, dt, dd, td, th, li, label, a, h1, h2, h3, h4, h5, h6, div {
    color: var(--text-primary);
}
.gradient-sidebar, .gradient-sidebar p, ... {
    color: inherit;
}
```

Thay bang:
```css
/* Set base text color on body only */
body {
    color: var(--text-primary);
}
```

**File:** `resources/views/layouts/app.blade.php` (dong 17-42)

Xoa tat ca dark mode style overrides dang xung dot:
```css
.dark .text-gray-900 { ... }
.dark .text-gray-950 { ... }
```

**Ly do:** De Tailwind va CSS variables tu xu ly, khong can ghi de thu cong.

---

### Buoc 2: Fix sidebar - dam bao toan bo text la trang

**File:** `resources/css/app.css`

```css
/* Sidebar - all text white */
.gradient-sidebar {
    color: #ffffff;
}
.gradient-sidebar .text-white\/70 {
    color: rgba(255, 255, 255, 0.7);
}
.sidebar-nav-item {
    color: #ffffff !important;
}
.sidebar-nav-item span,
.sidebar-nav-item .sidebar-icon {
    color: inherit;
}
```

**File:** `resources/views/layouts/app.blade.php`

Trong sidebar, tat ca text dung `text-white` hoac `text-white/70`:
- Logo text: `text-white` (da dung)
- Nav items: handled by CSS `.sidebar-nav-item`
- "Quan tri" label: `text-white/70`
- User name: `text-white` (da dung)
- User email: `text-white/70`

---

### Buoc 3: Fix header buttons va navigation

**File:** `resources/views/layouts/app.blade.php`

Thay tat ca header buttons tu hardcoded gray sang CSS variable:

| Tim | Thay bang |
|-----|----------|
| `text-gray-900 hover:text-gray-950` | `text-inherit hover:text-teal-600 dark:hover:text-teal-400` |
| `text-gray-900 group-hover:text-teal-500` | `text-inherit group-hover:text-teal-500` |
| `text-gray-950 dark:text-gray-900` | `text-inherit` |
| `text-gray-900 hover:text-red-500` | `text-inherit hover:text-red-500` |

---

### Buoc 4: Fix pills/badges bi vo hinh trong dark mode

**Tim trong tat ca blade files:**

| Pattern cu (bi loi) | Pattern moi |
|---------------------|-------------|
| `bg-gray-100 text-gray-950 dark:bg-gray-800 dark:text-gray-900` | `bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white` |
| `bg-green-100 text-green-950 dark:bg-green-900 dark:text-green-900` | `bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200` |
| `bg-blue-100 text-blue-950 dark:bg-blue-900 dark:text-blue-900` | `bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200` |
| `bg-red-100 text-red-950 dark:bg-red-900 dark:text-red-900` | `bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200` |
| `bg-yellow-100 text-yellow-950 dark:bg-yellow-900 dark:text-yellow-900` | `bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200` |
| `bg-amber-100 text-amber-950 dark:bg-amber-900 dark:text-amber-900` | `bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200` |
| `bg-teal-100 text-teal-950 dark:bg-teal-900 dark:text-teal-900` | `bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200` |
| `bg-purple-100 text-purple-950 dark:bg-purple-900 dark:text-purple-900` | `bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200` |

**Files can chinh:** 11+ files (xem danh sach ben duoi)

---

### Buoc 5: Fix form labels va cac trang admin

**Tim trong tat ca blade files:**

| Pattern cu | Pattern moi |
|-----------|-------------|
| `text-gray-950 dark:text-gray-900` (form labels) | `text-inherit` |
| `text-gray-950 dark:text-white` | `text-inherit` |
| `text-gray-900` (standalone, khong co dark pair) | `text-inherit` |

**File dac biet:** `admin/rooms/index.blade.php` - can them dark mode classes cho `bg-white` va `bg-gray-50`

---

## Danh sach files can sua

### Uu tien cao (nhieu loi nhat):
1. `resources/css/app.css` - Xoa global rule, fix sidebar
2. `resources/views/layouts/app.blade.php` - Xoa dark overrides, fix header, fix sidebar labels
3. `resources/views/dashboard/admin.blade.php` - Fix pills, stat labels

### Uu tien trung binh (pills/badges):
4. `resources/views/borrow/show.blade.php`
5. `resources/views/teaching-plans/index.blade.php`
6. `resources/views/teaching-plans/show.blade.php`
7. `resources/views/admin/maintenance/index.blade.php`
8. `resources/views/admin/maintenance/show.blade.php`
9. `resources/views/admin/damage-reports/index.blade.php`
10. `resources/views/admin/activity-logs/index.blade.php`
11. `resources/views/admin/activity-logs/show.blade.php`
12. `resources/views/admin/scheduled-reports/index.blade.php`
13. `resources/views/admin/scheduled-reports/show.blade.php`

### Uu tien thap (form labels):
14. `resources/views/admin/audit-reports/activity.blade.php`
15. `resources/views/admin/audit-reports/inventory.blade.php`
16. `resources/views/admin/audit-reports/maintenance.blade.php`
17. `resources/views/admin/audit-reports/borrow.blade.php`
18. `resources/views/admin/rooms/index.blade.php`
19. `resources/views/borrow/calendar.blade.php`
20. `resources/views/search/results.blade.php`

---

## Nguyen tac chung

1. **KHONG** dung `text-gray-*` cho text content nua - dung `text-inherit` de ke thua tu `var(--text-primary)`
2. **Sidebar** luon dung `text-white` vi nen luon la dark gradient
3. **Pills/badges** phai co cap mau ro rang: `bg-X-100 text-X-800 dark:bg-X-900 dark:text-X-200`
4. **Buttons** co mau rieng (`text-white` tren nen mau) - khong can sua
5. Sau khi sua xong, chay `npm run build && php artisan view:clear`

---

## Kiem tra sau khi sua

- [ ] Sidebar: tat ca text trang, nhin ro tren nen dark gradient
- [ ] Header: buttons/icons nhin ro ca light va dark mode
- [ ] Dashboard: stat cards, pills, labels nhin ro
- [ ] Tables: headers va body text nhin ro
- [ ] Forms: labels nhin ro
- [ ] Badges: mau sac tuong phan tot trong ca 2 mode
- [ ] Admin pages: tat ca text nhin ro
