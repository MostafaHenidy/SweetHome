# SweetHome E-Commerce — Architecture Design Spec

> Rebuild of WordPress/WooCommerce building materials e-commerce platform for Italian market (Milan-based). Laravel 12 + FilamentPHP + Livewire + Tailwind.

## Overview

**Business:** B2C/B2B e-commerce + service provider for home renovation materials (tiles, flooring, bathroom fixtures, etc.)
**Market:** Italian (Milan) — all UI in Italian, prices in EUR
**Scale:** 831+ products, 13 main categories with nested subcategories, brand filtering

## Tech Stack

- **Backend:** Laravel 12, MySQL, FilamentPHP 3 (admin panel), Laravel Sanctum
- **Frontend:** Blade templates, Livewire 3, Alpine.js, Tailwind CSS
- **Payments:** Stripe + PayPal
- **Email:** Laravel Mail (SMTP)
- **Search:** Laravel Scout + database driver (Meilisearch optional later)
- **Images:** Spatie Laravel MediaLibrary

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        STOREFRONT                            │
│  Homepage │ Shop │ Product Detail │ Cart │ Checkout │ Services│
│  (Blade + Livewire + Alpine.js + Tailwind)                   │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER                       │
│  Controllers │ Livewire Components │ Services │ Jobs │ Mail  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      DOMAIN LAYER                            │
│  Models │ Policies │ Observers │ Events/Listeners            │
│  Product │ Category │ Brand │ Cart │ Order │ Review          │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    FILAMENT ADMIN PANEL                      │
│  Dashboard │ Products │ Categories │ Orders │ Customers       │
│  Reviews │ Content │ Settings                                │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      DATABASE (MySQL)                        │
│  users │ products │ categories │ brands │ cart_items          │
│  orders │ order_items │ reviews │ product_attributes          │
│  media │ settings │ contact_submissions                       │
└─────────────────────────────────────────────────────────────┘
```

## Database Schema

### Core Tables

**users** — Jetstream teams already provides this
- id, name, email, password, phone (nullable), two_factor columns

**brands**
- id, name, slug, logo (media), description, sort_order, active

**categories** (self-referencing nested set)
- id, parent_id (nullable), name, slug, description, level (1-4), sort_order, image (media), active

**products**
- id, sku (unique), name, slug, short_description, description, price, compare_price (nullable), cost_price (nullable), status (draft/published), stock_quantity, track_stock (boolean), brand_id (nullable), sort_order

**product_categories** (pivot)
- product_id, category_id

**product_attributes**
- id, product_id, name (e.g., "dim borealis"), values (JSON array: ["20x120x0,9", "20x180x1,5"])

**product_images** — handled by Spatie MediaLibrary on products model

**cart_items**
- id, session_id, user_id (nullable), product_id, attribute_selection (JSON nullable), quantity, created_at, updated_at

**orders**
- id, order_number (unique, formatted), user_id (nullable), customer_name, customer_email, customer_phone, shipping_address (JSON), billing_address (JSON), subtotal, shipping_cost, tax, total, status (pending/processing/shipped/completed/cancelled), payment_method, payment_status (pending/paid/failed/refunded), notes (nullable), created_at, updated_at

**order_items**
- id, order_id, product_id, product_name (snapshot), product_sku (snapshot), attribute_selection (JSON), quantity, unit_price, total

**reviews**
- id, product_id, user_name, user_email, rating (1-5), comment, status (pending/approved/rejected), created_at

**contact_submissions**
- id, name, email, phone, subject, message, status (new/read/replied), created_at

**settings** (key-value store for site config)
- id, key (unique), value (JSON), group

## URL Structure

```
/                           Homepage
/shop                       Product listing (all products)
/shop/{category-slug}       Category listing
/shop/{category-slug}/{product-slug}  Product detail
/cart                       Shopping cart
/checkout                   Checkout flow
/checkout/success/{order}   Order confirmation
/services                   Services page
/contact                    Contact page
/admin                      Filament admin (protected)
```

## Key Design Decisions

1. **Cart:** Session-based for guests, user-linked for authenticated. Merges on login.
2. **Product variants:** Simple attribute selection (dropdowns), not full SKU-per-variant. Each attribute is a dropdown on the product page.
3. **Categories:** Nested via parent_id, max 4 levels deep. Breadcrumbs generated from ancestry.
4. **Images:** Spatie MediaLibrary for product images (multiple per product, with conversions).
5. **Admin:** FilamentPHP 3 for all admin CRUD. Custom dashboard with stats.
6. **Payments:** Stripe for cards, PayPal for PayPal accounts. Both via official Laravel packages.
7. **Emails:** Laravel Mailables with Blade templates, queued via database queue.
8. **SEO:** Meta tags per page, Open Graph, sitemap.xml, robots.txt, clean slugs.

## Phases

1. **Foundation** — Database, models, Filament admin, auth
2. **Product Catalog** — Categories, products, brands, shop pages, product detail
3. **E-commerce Core** — Cart, checkout, orders, payments
4. **Services & Content** — Services page, contact forms, reviews, newsletter
5. **Polish & Launch** — SEO, performance, email system, testing
