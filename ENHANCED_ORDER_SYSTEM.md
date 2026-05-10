# Enhanced Order System with Shipping & Payment Installments

## Overview

This document describes the enhanced order system implementation that includes comprehensive shipping options, service types, and flexible payment installment plans.

## Features Implemented

### 1. Shipping Method Selection

The system supports two shipping methods:

- **Delivery**: Products are shipped to customer's address
  - Requires shipping address collection
  - Supports shipping fees calculation
  - Optional construction/assembly service

- **Pickup**: Customer picks up from company location
  - No shipping address required
  - No shipping fees
  - Location selection from predefined options

### 2. Service Type Selection

Available only for delivery orders:

- **Shipping Only**: Products delivered, customer handles assembly
- **Shipping and Construction**: Professional delivery and assembly included

### 3. Payment Installment System

Two payment plan types:

#### Scenario A: Shipping Only (50-50 Plan)
- **1st Installment**: 50% deposit (paid immediately at checkout)
- **2nd Installment**: 50% on delivery (paid when order arrives)

#### Scenario B: Shipping and Construction (50-30-20 Plan)
- **1st Installment**: 50% deposit (paid immediately at checkout)
- **2nd Installment**: 30% on delivery (paid when order arrives)
- **3rd Installment**: 20% after construction (paid when installation complete)

## Database Schema

### Orders Table Changes
Added the following fields:
- `shipping_method`: enum('delivery', 'pickup')
- `service_type`: enum('shipping_only', 'shipping_and_construction')
- `payment_plan_type`: enum('50-50', '50-30-20')
- `deposit_amount`: decimal(10, 2)
- Enhanced `status` field with new stages

### New Table: payment_installments
```sql
CREATE TABLE payment_installments (
    id BIGINT PRIMARY KEY,
    order_id BIGINT FOREIGN KEY,
    installment_number INTEGER,
    amount DECIMAL(10, 2),
    percentage INTEGER,
    due_stage ENUM('deposit', 'on_delivery', 'on_construction'),
    status ENUM('pending', 'paid', 'failed'),
    payment_transaction_id VARCHAR(255) NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(order_id, installment_number)
);
```

## Models

### PaymentInstallment Model
Location: `app/Models/PaymentInstallment.php`

Features:
- Relationship with Order model
- Helper methods for status checking
- Formatted amount attribute
- Due stage label attribute

### Updated Order Model
Location: `app/Models\Order.php`

New methods:
- `isDelivery()`: Check if order is delivery
- `isPickup()`: Check if order is pickup
- `hasConstruction()`: Check if includes construction
- `getPendingInstallments()`: Get pending installments
- `getNextPendingInstallment()`: Get next installment to pay

## Services

### PaymentInstallmentService
Location: `app/Services/PaymentInstallmentService.php`

Methods:
- `createInstallments()`: Generate installments based on order total and service type
- `markInstallmentPaid()`: Mark installment as paid with transaction ID
- `getNextInstallmentToPay()`: Get the next pending installment
- `getTotalPaidAmount()`: Calculate total paid amount
- `areAllInstallmentsPaid()`: Check if all installments are paid
- `getInstallmentsSummary()`: Get formatted summary for display

### FawaterakService
Location: `app/Services\FawaterakService.php`

Methods:
- `createPaymentInvoice()`: Create Fawaterak payment invoice
- `verifyPayment()`: Verify payment status
- `handleWebhook()`: Process webhook callbacks
- `getPaymentUrl()`: Get redirect URL for payment

## Checkout Flow Updates

### Livewire Component: CheckoutPage
Location: `app/Http/Livewire\CheckoutPage.php`

New properties:
- `shipping_method`: Selected shipping method
- `service_type`: Selected service type
- `pickup_location`: Selected pickup location
- Separate shipping address fields

Updated validation rules for new shipping fields

### Checkout Steps
1. **Customer Information**: Basic customer details
2. **Shipping Method**: Select delivery/pickup and service type
3. **Payment**: Review payment plan and select payment method

### Payment Methods Supported
- Fawaterak (recommended for installments)
- Stripe
- PayPal
- Bank Transfer

## Admin Features

### Order Status Progression
```
pending → pending_deposit → deposit_paid → processing → shipped → delivered → construction_scheduled → construction_in_progress → completed
```

### Console Commands
- `php artisan payment:reminders`: Send payment reminders for pending installments
- `php artisan orders:update-statuses`: Update order statuses based on payment progress

## Webhooks

### Fawaterak Webhook
- **Endpoint**: `POST /webhook/fawaterak`
- **Route Name**: `webhook.fawaterak`
- **Controller**: `FawaterakWebhookController`

## Frontend Updates

### Checkout Page
- Multi-step checkout with shipping method selection
- Dynamic service type options based on shipping method
- Payment plan summary display
- Fawaterak payment option

### Order Success Page
- Payment plan breakdown
- Installment status visualization
- Download links for documents

## Testing

Comprehensive test suite included in `tests/Feature/EnhancedOrderSystemTest.php`:
- Shipping only order creation
- Shipping and construction order creation
- Pickup order creation
- Installment calculation verification
- Installment payment processing
- Payment status tracking

Run tests with:
```bash
php artisan test tests/Feature/EnhancedOrderSystemTest.php
```

## Configuration

### Environment Variables
Add to `.env` file:
```env
FAWATERAK_API_KEY=your_api_key
FAWATERAK_SECRET_KEY=your_secret_key
FAWATERAK_BASE_URL=https://api.fawaterak.com
```

## Usage Example

### Creating an Order with Installments
```php
$order = Order::create([
    'total' => 1000.00,
    'shipping_method' => 'delivery',
    'service_type' => 'shipping_and_construction',
    // ... other order fields
]);

$service = app(PaymentInstallmentService::class);
$service->createInstallments($order);

// Order now has 3 installments:
// 1. 500.00 (deposit) - due immediately
// 2. 300.00 (on delivery) - due when shipped
// 3. 200.00 (on construction) - due after installation
```

### Processing Installment Payment
```php
$installment = $order->getNextPendingInstallment();
$service->markInstallmentPaid($installment, 'fawaterak_txn_123');
```

## Future Enhancements

- [ ] Full Fawaterak API integration
- [ ] Email notifications for each installment due
- [ ] SMS reminders for payment deadlines
- [ ] Admin dashboard for installment management
- [ ] Customer dashboard for payment tracking
- [ ] Multi-currency support
- [ ] Partial payment support
- [ ] Early payment discounts

## Migration Commands

To set up the enhanced order system:
```bash
# Run migrations
php artisan migrate

# Clear cache
php artisan optimize:clear

# Test the implementation
php artisan test tests/Feature/EnhancedOrderSystemTest.php
```

## Support

For issues or questions about the enhanced order system, refer to:
- Database migrations in `database/migrations/`
- Models in `app/Models/`
- Services in `app/Services/`
- Tests in `tests/Feature/`