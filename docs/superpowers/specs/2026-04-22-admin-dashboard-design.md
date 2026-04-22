# Admin Dashboard Design — SweetHome E-commerce

**Date:** 2026-04-22
**Project:** SweetHome Laravel/Filament Admin Dashboard

---

## Context

The user wants to create an admin dashboard page for their SweetHome e-commerce application that matches a reference screenshot. The dashboard should have:
- Dark sidebar navigation with icons and labels
- Top bar with search, notifications bell, and user avatar
- 4 stat cards: Revenue, Orders, Customers, Products
- Dual-axis chart (bars + line) showing monthly data
- Recent orders table with pagination (10 per page)

---

## Decisions

| Topic | Decision |
|---|---|
| **Stat cards** | Show total lifetime totals (cumulative from all time) |
| **Chart metric** | Revenue drives the line (€), order count drives the bars |
| **Chart scope** | All payment statuses included |
| **Table pagination** | 10 per page |

---

## Architecture

### Layout Structure

```
Filament Admin Panel (existing dark sidebar)
├── Stats Row (4 cards in a 4-column grid)
│   ├── Revenue — lifetime total (€)
│   ├── Orders — lifetime count
│   ├── Customers — lifetime user count
│   └── Products — lifetime product count
├── Monthly Orders Chart (dual-axis: bars = count, line = revenue)
└── Recent Orders Table (paginated, 10 per page)
```

### Stat Cards

Each card uses `Stat::make()` from Filament's `StatsOverviewWidget`:

| Label | Value | Icon | Description |
|---|---|---|---|
| Revenue | `Order::where('payment_status', 'paid')->sum('total')` | `heroicon-o-currency-euro` | Totale incassato |
| Orders | `Order::count()` | `heroicon-o-shopping-cart` | Totale ordini |
| Customers | `User::count()` | `heroicon-o-users` | Totale clienti |
| Products | `Product::count()` | `heroicon-o-shopping-bag` | Totale prodotti |

### Chart — Monthly Orders Chart Widget

**Type:** Dual-axis bar + line chart (Chart.js via a Livewire component)

**Data query:**
```php
// Monthly aggregates grouped by month
Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(total) as revenue")
    ->groupBy('month')
    ->orderBy('month')
    ->get();
```

**Axes:**
- Y-axis left: Order count (bars)
- Y-axis right: Revenue in € (line)
- X-axis: Month labels (e.g., "Gen", "Feb", "Mar")

**In-chart elements:**
- Bar fill color: `rgba(99, 102, 241, 0.8)` (indigo)
- Line color: `rgba(16, 185, 129, 0.8)` (emerald)
- Tooltip showing both values

### Table — Recent Orders

**Columns:**
| Column | Source | Notes |
|---|---|---|
| Customer | `order.user.name` | Eager-load `user` relationship |
| Product | `order.items.first().product.name` | First item's product |
| Date | `created_at` | Formatted `dd/MM/yyyy` |
| Amount | `total` | Formatted `€X,XX` |
| Status | `payment_status` | Colored badge |

**Pagination:** 10 per page, using Filament's built-in pagination

---

## Implementation Plan

1. **Expand `StatsOverview.php`** — Replace existing stats with 4 cards (Revenue, Orders, Customers, Products) showing lifetime totals
2. **Create `MonthlyOrdersChart.php`** — Livewire widget using Chart.js for dual-axis bar+line chart
3. **Create `RecentOrdersTable.php`** — Filament table widget or custom Livewire table component for orders with pagination
4. **Register widgets** on the Filament dashboard page

---

## Components

### 1. `app/Filament/Widgets/StatsOverview.php` (expanded)

```php
protected function getStats(): array
{
    return [
        Stat::make('Revenue', '€' . number_format(Order::where('payment_status', 'paid')->sum('total'), 2, ',', '.'))
            ->description('Totale incassato')
            ->icon('heroicon-o-currency-euro'),
        Stat::make('Ordini', Order::count())
            ->description('Totale ordini')
            ->icon('heroicon-o-shopping-cart'),
        Stat::make('Clienti', User::count())
            ->description('Totale clienti')
            ->icon('heroicon-o-users'),
        Stat::make('Prodotti', Product::count())
            ->description('Totale prodotti')
            ->icon('heroicon-o-shopping-bag'),
    ];
}
```

### 2. `app/Filament/Widgets/MonthlyOrdersChart.php`

- Extends `Chart` (Filament's built-in chart widget)
- Type: `Chart::TYPE_BAR` with a line dataset overlay
- Or: Custom Livewire component with Chart.js for full dual-axis control

### 3. `app/Filament/Widgets/RecentOrdersTable.php`

- Extends `Table` widget (Filament's built-in table widget)
- Paginated 10 per page
- Shows recent 10 orders with status badges

---

## Error Handling

- If no orders exist: chart shows empty state with message "Nessun dato disponibile"
- If `user` or `product` relationship is null: show "—" placeholder
- If chart data query fails: log error and show friendly message

---

## Testing Checklist

- [ ] Stats cards show correct totals matching database
- [ ] Chart renders with correct dual-axis labels
- [ ] Chart updates when new orders are placed
- [ ] Table pagination shows exactly 10 orders per page
- [ ] Table status badges are color-coded correctly
- [ ] Layout is responsive on smaller screens
- [ ] No console errors on dashboard load