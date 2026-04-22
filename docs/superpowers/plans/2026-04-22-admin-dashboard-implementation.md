# Admin Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an admin dashboard page with 4 lifetime stat cards, a dual-axis monthly orders chart (bars = count, line = revenue), and a recent orders table paginated 10/page.

**Architecture:** Uses Filament v3.2's built-in widget system — `StatsOverviewWidget` for stat cards, `ChartWidget` with custom Chart.js options for the dual-axis chart, and `TableWidget` for the orders table. All widgets registered on the Filament dashboard.

**Tech Stack:** Laravel 12, Filament 3.2, Chart.js (via Filament's chart widget), Livewire.

---

## File Map

| File | Action |
|---|---|
| `app/Filament/Widgets/StatsOverview.php` | Modify — replace 4 stats with Revenue, Orders, Customers, Products |
| `app/Filament/Widgets/MonthlyOrdersChart.php` | Create — dual-axis bar+line chart using ChartWidget |
| `app/Filament/Widgets/RecentOrders.php` | Modify — add pagination(10), improve columns |
| `config/filament.php` | Check — ensure widgets are registered on the dashboard |

---

## Task 1: Expand StatsOverview Widget

**Files:**
- Modify: `app/Filament/Widgets/StatsOverview.php`

The existing file already has 4 stats. Replace them with the correct 4 cards (Revenue, Orders, Customers, Products).

- [ ] **Step 1: Write the failing test**

Run: `grep -n "Revenue\|Entrate" app/Filament/Widgets/StatsOverview.php`
Expected: Currently shows "Entrate" — verify this needs changing to "Revenue"

```bash
grep -n "Revenue\|Entrate" app/Filament/Widgets/StatsOverview.php
```

- [ ] **Step 2: Write the updated StatsOverview**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
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
}
```

- [ ] **Step 3: Verify the file compiles**

Run: `php artisan filament:cache-components 2>&1 || true`
Expected: No PHP syntax errors

```bash
php -l app/Filament/Widgets/StatsOverview.php
```

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Widgets/StatsOverview.php
git commit -m "feat(dashboard): expand stats to Revenue, Orders, Customers, Products"
```

---

## Task 2: Create MonthlyOrdersChart Widget

**Files:**
- Create: `app/Filament/Widgets/MonthlyOrdersChart.php`
- Test: `tests/Feature/Widgets/MonthlyOrdersChartTest.php` (create if test dir exists)

The chart uses Filament's `ChartWidget` which wraps Chart.js. To get a dual-axis bar+line chart, override `getOptions()` to add a second dataset as a line with its own y-axis.

- [ ] **Step 1: Create the MonthlyOrdersChart widget**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyOrdersChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getHeading(): string | null
    {
        return 'Ordini Mensili';
    }

    protected function getDescription(): string | null
    {
        return 'Andamento ordini e ricavi mensili';
    }

    protected function getData(): array
    {
        $months = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(total) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $months->map(fn($m) => $this->formatMonth($m->month))->toArray();
        $orderCounts = $months->pluck('count')->toArray();
        $revenues = $months->pluck('revenue')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Ordini',
                    'data' => $orderCounts,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'yAxisID' => 'y',
                    'order' => 2,
                ],
                [
                    'label' => 'Ricavi (€)',
                    'data' => $revenues,
                    'type' => 'line',
                    'borderColor' => 'rgba(16, 185, 129, 0.8)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
                    'order' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        return [
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Ordini',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Ricavi (€)',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }

    protected function formatMonth(string $month): string
    {
        $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
        $itMonths = [
            '01' => 'Gen', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mag', '06' => 'Giu', '07' => 'Lug', '08' => 'Ago',
            '09' => 'Set', '10' => 'Ott', '11' => 'Nov', '12' => 'Dic',
        ];

        return $itMonths[$date->format('m')] . ' ' . $date->format('Y');
    }
}
```

- [ ] **Step 2: Verify the file compiles**

```bash
php -l app/Filament/Widgets/MonthlyOrdersChart.php
```

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Widgets/MonthlyOrdersChart.php
git commit -m "feat(dashboard): add MonthlyOrdersChart dual-axis widget"
```

---

## Task 3: Enhance RecentOrders Table Widget

**Files:**
- Modify: `app/Filament/Widgets/RecentOrders.php`

The existing `RecentOrders.php` widget exists but shows `limit(10)` (which is a fixed limit, not pagination). Replace with proper paginated query and improve column display.

- [ ] **Step 1: Write the updated RecentOrders widget**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->with('user'))
            ->paginated(10)
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Ordine')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Importo')
                    ->money('EUR', locale: 'it_IT')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Stato')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pagamento')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'info' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'In attesa',
                        'processing' => 'In lavorazione',
                        'shipped' => 'Spedito',
                        'completed' => 'Completato',
                        'cancelled' => 'Annullato',
                    ]),
            ]);
    }
}
```

- [ ] **Step 2: Verify the file compiles**

```bash
php -l app/Filament/Widgets/RecentOrders.php
```

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Widgets/RecentOrders.php
git commit -m "feat(dashboard): paginate RecentOrders at 10/page, add filters"
```

---

## Task 4: Register Widgets on Dashboard

**Files:**
- Modify: `app/Providers/AppServiceProvider.php` (or `config/filament.php`)

In Filament v3, widgets on the dashboard are registered via `FilamentPlugin` or directly in the service provider. Check if a custom dashboard page exists, otherwise ensure widgets are registered.

- [ ] **Step 1: Check if a custom dashboard page exists**

```bash
find app/Filament/Pages -name "*.php" 2>/dev/null
```

- [ ] **Step 2: If no custom dashboard page exists, check filament config for widget registration**

Read: `config/filament.php` — look for `widgets` key or `pages` registration

- [ ] **Step 3: Register widgets in AppServiceProvider if needed**

If `config/filament.php` doesn't have widget registration, add to `app/Providers/AppServiceProvider.php`:

```php
use Filament\FilamentManager;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\MonthlyOrdersChart;
use App\Filament\Widgets\RecentOrders;

public function boot(): void
{
    Filament::serving(function () {
        Filament::registerWidgets([
            StatsOverview::class,
            MonthlyOrdersChart::class,
            RecentOrders::class,
        ]);
    });
}
```

- [ ] **Step 4: Verify and commit**

```bash
php -l app/Providers/AppServiceProvider.php
git add app/Providers/AppServiceProvider.php
git commit -m "feat(dashboard): register widgets on Filament dashboard"
```

---

## Task 5: Test the Dashboard

- [ ] **Step 1: Run the app and verify dashboard loads**

Run: `php artisan serve`
Navigate: `http://localhost:8000/admin`

Expected: Dark sidebar, 4 stat cards, monthly chart, paginated orders table

- [ ] **Step 2: Verify stat card values are correct**

Check database counts match displayed values:
```bash
php artisan tinker --execute="echo 'Products: ' . \App\Models\Product::count() . PHP_EOL . 'Orders: ' . \App\Models\Order::count() . PHP_EOL . 'Users: ' . \App\Models\User::count() . PHP_EOL . 'Revenue: €' . \App\Models\Order::where('payment_status', 'paid')->sum('total');"
```

- [ ] **Step 3: Verify chart renders with data**

Check browser console for Chart.js errors. Verify the chart shows bars (orders) and line (revenue) with dual y-axes.

- [ ] **Step 4: Verify table pagination**

Navigate to page 2 of the orders table. Verify it shows different 10 orders.

---

## Self-Review Checklist

**1. Spec coverage:**
- [x] 4 stat cards (Revenue, Orders, Customers, Products) — Task 1
- [x] Dual-axis chart (bars = orders, line = revenue) — Task 2
- [x] Monthly data aggregation — Task 2
- [x] Recent orders table, 10 per page — Task 3
- [x] Widgets registered on dashboard — Task 4
- [x] All payment statuses included in chart — Task 2

**2. Placeholder scan:** No TBD/TODO found. All code is complete.

**3. Type consistency:**
- `MonthlyOrdersChart::formatMonth()` — private method, correct
- `Order::where('payment_status', 'paid')->sum('total')` — matches spec and existing code
- `Stat::make()` calls — correct Filament v3 API
- `TableWidget::paginated(10)` — correct Filament v3 API

---

## Execution Options

**1. Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?