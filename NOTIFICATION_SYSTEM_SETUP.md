# Notification System Implementation - SweetHome

## Overview
A comprehensive multi-channel notification system has been implemented for the SweetHome Laravel e-commerce project. The system supports email, Firebase push notifications, and database notifications for order and quote-related events.

## Features Implemented

### 1. Notification Types
- **Order Created** - Sent when new orders are placed
- **Order Status Changed** - Sent when order status updates (processing, shipped, completed, etc.)
- **Quote Request Created** - Sent when customers request quotes
- **Quote Status Changed** - Sent when quote status updates (quote_sent, accepted, rejected, etc.)

### 2. Notification Channels
- **Email** - Via Laravel's built-in mail system
- **Firebase Push** - Via Firebase Cloud Messaging (FCM)
- **Database** - For in-app notifications via Laravel's notification system

### 3. User Features
- **Notification Preferences** - Users can customize which notifications they receive via each channel
- **Device Token Management** - API endpoints for registering/unregistering Firebase device tokens
- **Multi-device Support** - Users can have multiple devices registered for push notifications

### 4. Admin Features
- **Admin Email Configuration** - Configurable admin email addresses in `.env`
- **Admin Notifications** - Admins receive notifications for all order and quote events
- **Firebase Admin Topics** - Support for admin-specific notification topics

## Technical Implementation

### Database Tables Created
1. `notifications` - Laravel's standard notifications table
2. `notification_preferences` - User notification preferences per channel and event type
3. `firebase_tokens` - Device tokens for Firebase push notifications

### Laravel Components Created

#### Notifications (app/Notifications/)
- `OrderCreatedNotification.php` - Handles order creation notifications
- `OrderStatusChangedNotification.php` - Handles order status change notifications
- `QuoteRequestNotification.php` - Handles quote request notifications
- `QuoteStatusChangedNotification.php` - Handles quote status change notifications

#### Events (app/Events/)
- `OrderCreated.php` - Event triggered when order is created
- `OrderStatusChanged.php` - Event triggered when order status changes
- `QuoteRequestCreated.php` - Event triggered when quote request is created
- `QuoteStatusChanged.php` - Event triggered when quote status changes

#### Listeners (app/Listeners/)
- `SendOrderNotifications.php` - Handles order-related notifications
- `SendQuoteNotifications.php` - Handles quote-related notifications

#### Services (app/Services/)
- `FirebaseService.php` - Service for sending Firebase Cloud Messaging notifications
- Updated `PaymentService.php` - Added event dispatching for order status changes
- Updated `QuoteService.php` - Added event dispatching for quote operations

#### Channels (app/Notifications/Channels/)
- `FirebaseChannel.php` - Custom Laravel notification channel for Firebase

#### Controllers & Models
- `FirebaseController.php` - API endpoints for device token management
- `FirebaseToken.php` - Model for device tokens
- `NotificationPreference.php` - Model for user notification preferences

#### Livewire Components
- `NotificationPreferences.php` - Component for managing notification settings
- `QuoteHistory.php` - Component for displaying quote request history

### Configuration Files
- `config/firebase.php` - Firebase configuration
- Updated `.env` - Added Firebase and admin email configuration

## Environment Configuration

Add these to your `.env` file:

```env
# Firebase Configuration (using existing Firebase project)
FIREBASE_SERVER_KEY=your_fcm_server_key_here
FIREBASE_SENDER_ID=your_sender_id_here
FIREBASE_PROJECT_ID=your_project_id_here
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com

# Admin Email Addresses for Notifications (comma-separated)
ADMIN_EMAILS=admin@sweethome.com,backup@sweethome.com
```

## API Endpoints

### Firebase Token Management
- `POST /firebase/token` - Register device token
- `DELETE /firebase/token` - Unregister device token
- `GET /firebase/tokens` - List registered device tokens

All endpoints require authentication.

## User Interface

### Account Page Updates
- Added "Notifiche" link in sidebar navigation
- Added notification preferences section at bottom of account page
- Users can toggle notification preferences for each channel and event type

### Quote History
- Dedicated page showing all quote requests and their status
- PDF download links for approved quotes
- Status indicators with color coding

## Notification Flow

1. **Order/Quote Created** → Event dispatched → Listeners send notifications via configured channels
2. **Status Changed** → Event dispatched → Listeners send notifications via configured channels
3. **Channel Delivery**:
   - **Email**: Sent to user and configured admin emails
   - **Firebase**: Sent to user's registered device tokens
   - **Database**: Stored in notifications table for in-app display

## Queue Processing

Notifications are processed asynchronously using Laravel's queue system (database driver). To process notifications, run:

```bash
php artisan queue:work
```

## Testing the System

1. **Email Testing**: Configure mail trap or test email service in `.env`
2. **Firebase Testing**: Add Firebase credentials and test device token registration
3. **Database Testing**: Check notifications table for stored notifications
4. **Queue Testing**: Run queue worker and monitor for any errors

## Security Considerations

- All Firebase token endpoints require authentication
- User notification preferences are respected for all notification types
- Admin notifications are sent regardless of user preferences
- Device tokens are unique per user and can be unregistered at any time

## Performance Notes

- Notifications are queued for async processing
- Firebase service uses HTTP client (no heavy SDK dependencies)
- Database indexes optimize notification queries
- Device tokens older than 30 days are filtered out

## Next Steps

1. Configure Firebase credentials in `.env`
2. Set admin email addresses
3. Test notification delivery with a test order
4. Monitor queue processing for any errors
5. Configure email templates if custom branding is needed

## Files Modified

- `config/fortify.php` - Changed home redirect to `/account`
- `app/Models/User.php` - Added notification relationships
- `app/Services/PaymentService.php` - Added event dispatching
- `app/Services/QuoteService.php` - Added event dispatching
- `app/Http/Livewire/CheckoutPage.php` - Added order creation event
- `app/Providers/AppServiceProvider.php` - Registered Firebase channel and Livewire components
- `routes/web.php` - Added Firebase API routes and account routes
- `resources/views/account.blade.php` - Added notification preferences section

## Files Created

- 4 Notification classes
- 4 Event classes
- 2 Listener classes
- 1 Firebase service
- 1 Firebase channel
- 1 Firebase controller
- 2 Model classes
- 2 Livewire components
- 3 Database migrations
- Configuration files

The system is now ready for use! Users will receive notifications via their preferred channels when orders and quotes are created or their status changes.