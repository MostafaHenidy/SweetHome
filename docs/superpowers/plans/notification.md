Phase 1: Foundation & Data Architecture (Week 1-2)
1.1 Database Schema Design
New Tables to Create:
notifications table:

id (bigint, primary key)
user_id (foreign key to users)
type (enum: order_created, order_status_changed, order_assigned, order_completed, etc.)
notifiable_type (polymorphic: Order, User, etc.)
notifiable_id (polymorphic ID)
data (json: flexible payload with order details, old/new status, etc.)
read_at (timestamp, nullable)
created_at, updated_at
Indexes: user_id, read_at, type, created_at

user_device_tokens table:

id (bigint, primary key)
user_id (foreign key to users)
device_token (text, unique)
platform (enum: ios, android, web)
is_active (boolean, default true)
last_used_at (timestamp)
created_at, updated_at
Indexes: user_id, device_token, is_active

notification_preferences table:

id (bigint, primary key)
user_id (foreign key to users, unique)
email_enabled (boolean, default true)
push_enabled (boolean, default true)
in_app_enabled (boolean, default true)
notification_types (json: per-type preferences like {"order_created": true, "order_status_changed": false})
created_at, updated_at

order_status_history table:

id (bigint, primary key)
order_id (foreign key to orders)
old_status (string, nullable)
new_status (string)
changed_by (foreign key to users)
notes (text, nullable)
created_at
Indexes: order_id, created_at

Modify orders table:

Add watchers (json array of user IDs who want notifications)
Add assigned_user_id (foreign key to users, nullable)


1.2 Configuration Setup
Environment Variables to Add:

MAIL_FROM_ADDRESS and MAIL_FROM_NAME (already in Laravel)
FCM_SERVER_KEY or FCM_CREDENTIALS_PATH (for Firebase Admin SDK)
FCM_PROJECT_ID
NOTIFICATION_QUEUE_CONNECTION (redis/database)
NOTIFICATION_RETRY_ATTEMPTS (default: 3)
NOTIFICATION_RETRY_DELAY (seconds, default: 60)

Config Files:
Create config/notifications.php:

Default channels per notification type
Admin user IDs or roles
Rate limiting thresholds
Batch size for bulk notifications
Localization settings per notification type


Phase 2: Core Services Architecture (Week 2-3)
2.1 Service Layer Structure
Create These Service Classes:
NotificationService (Main orchestrator):

Determines which channels to use based on user preferences
Coordinates between EmailService, FCMService, and DatabaseNotificationService
Handles notification dispatching logic
Implements deduplication (check if similar notification sent recently)

EmailNotificationService:

Builds email from templates (Blade views)
Queues email jobs
Handles localization based on user's preferred language
Implements retry logic with exponential backoff
Tracks delivery status

FCMNotificationService:

Manages device token registration/removal
Sends to individual devices or topics
Handles token invalidation (when FCM returns invalid token errors)
Constructs platform-specific payloads (data vs notification payload)
Batches multiple sends for efficiency

DatabaseNotificationService:

Creates notification records
Handles bulk inserts for multiple users
Marks notifications as read
Cleans up old notifications (30+ days read notifications)

NotificationPreferenceService:

Retrieves user preferences
Updates preferences
Validates channel availability per user


2.2 Event-Driven Architecture
Laravel Events to Create:
OrderCreated:

Payload: Order model, creator User
Listeners: NotifyAdminsOfNewOrder, NotifyUserOfOrderCreation, NotifyWatchers

OrderStatusChanged:

Payload: Order model, old_status, new_status, changed_by User
Listeners: NotifyAdminsOfStatusChange, NotifyOrderParticipants, LogStatusHistory

OrderAssigned:

Payload: Order model, assigned User, assigner User
Listeners: NotifyAssignedUser, NotifyPreviousAssignee (if reassigned)

OrderCompleted:

Payload: Order model, completed_by User
Listeners: NotifyOrderCreator, NotifyWatchers, GenerateCompletionReport

Event Registration Strategy:

Register in EventServiceProvider
All listeners should implement ShouldQueue for async processing
Set queue priority (high for admins, normal for users)


Phase 3: Notification Channels Implementation (Week 3-4)
3.1 Email Notifications
Email Template Structure:
Create Blade views in resources/views/emails/:

order-created.blade.php (for users)
order-created-admin.blade.php (for admins with more details)
order-status-changed.blade.php
order-assigned.blade.php
order-completed.blade.php

Mailable Class Strategy:
Each notification type gets a Mailable:

OrderCreatedMail
OrderStatusChangedMail
OrderAssignedMail

Localization Approach:

Use lang/en/notifications.php and lang/ar/notifications.php
Detect user's locale from users.locale column
Pass locale to queue job
Set App::setLocale() before building email

Subject Line Format:

Arabic RTL support: [اسم المشروع] - إشعار طلب جديد #{{order_id}}
English: [Project Name] - New Order Notification #{{order_id}}

Retry Policy:

Queue on notifications queue with 3 retry attempts
Exponential backoff: 1min, 5min, 15min
After failure, log to failed_jobs table for manual review
Send fallback notification to admins if critical email fails


3.2 FCM Push Notifications
Device Token Management:

Users register tokens via API endpoint when app launches
Store multiple tokens per user (phone + tablet + web)
Invalidate old tokens after 90 days of inactivity
Remove tokens when FCM returns NotRegistered error

Message Construction Strategy:
For Admins:

Title: "طلب جديد #123" / "New Order #123"
Body: Short summary with customer name and order type
Data payload: {type: 'order_created', order_id: 123, action: 'view_order'}
Priority: high
Sound: enabled

For Users:

Title: "تحديث على طلبك" / "Update on Your Order"
Body: Status change message
Data payload: {type: 'order_status_changed', order_id: 123, new_status: 'processing'}
Priority: normal
Badge: increment by 1

Topic-Based Notifications:

Subscribe admins to admin-notifications topic
Subscribe users to user-{user_id}-orders topic for their orders
Use topics for broadcasts (e.g., system maintenance)

Platform-Specific Considerations:

iOS: Include content-available: 1 for background updates, configure mutable-content: 1 for notification extensions
Android: Use notification AND data payload for foreground/background handling
Web: Include icon, badge, and click_action URL

Batching Strategy:

For 500+ recipients, use FCM multicast (up to 500 tokens per request)
Queue in chunks of 100 tokens per job
Track success/failure per token


3.3 In-Database Notifications
Creation Logic:

Insert records AFTER order event is committed to database
Use bulk insert for multiple recipients (admins + watchers)
Set read_at to null initially

Notification Types and Messages:
order_created: "تم إنشاء طلب جديد #{order_id} بواسطة {customer_name}"
order_status_changed: "تم تغيير حالة الطلب #{order_id} من {old_status} إلى {new_status}"
order_assigned: "تم تعيينك للطلب #{order_id}"
order_completed: "تم إنجاز الطلب #{order_id} بنجاح"
Data Payload Structure (JSON):
json{
  "order_id": 123,
  "order_number": "ORD-2024-001",
  "customer_name": "أحمد محمد",
  "old_status": "pending",
  "new_status": "processing",
  "changed_by": {
    "id": 5,
    "name": "مصطفى"
  },
  "url": "/orders/123"
}
Read/Unread Management:

API endpoint: POST /api/notifications/{id}/mark-as-read
Bulk mark: POST /api/notifications/mark-all-read
Livewire component shows unread count in header
Auto-mark as read when user views order details page


Phase 4: Business Logic & Workflows (Week 4-5)
4.1 Notification Target Determination
Admin Identification:

Query users with role admin or super_admin (using Spatie Permission or similar)
Cache admin user IDs in Redis for 1 hour
Fallback: specific admin email addresses in config

Order Creator & Assigned User:

Order creator: orders.user_id
Assigned user: orders.assigned_user_id
Always include these unless they've opted out

Other Customers (Watchers):

Stored in orders.watchers JSON array
Users can "watch" an order via UI
Automatically add:

Users who commented on the order
Users in the same customer organization (if applicable)
Users who previously worked on similar orders (optional)




4.2 Event Emission Points
Order Creation Hook:

In OrderController@store or Filament action
After order is saved to database
Emit: event(new OrderCreated($order, auth()->user()))

Status Change Hook:

In OrderController@updateStatus or Filament form action
Before saving, capture $oldStatus = $order->status
After saving, emit: event(new OrderStatusChanged($order, $oldStatus, $newStatus, auth()->user()))

Assignment Hook:

In assignment action (Filament action or API endpoint)
Capture previous assignee if exists
Emit: event(new OrderAssigned($order, $newUser, auth()->user()))


4.3 Idempotency & Deduplication
Duplicate Prevention:

Before creating notification, check if similar notification exists:

Same user_id + type + order_id
Created within last 5 minutes
If exists, skip or update timestamp



Idempotent Email Sending:

Generate unique message ID: order-{order_id}-status-{new_status}-{timestamp}
Store in cache (Redis) for 24 hours
Check cache before queuing email

FCM Token Deduplication:

If user has multiple active tokens, send to all
BUT if two tokens point to same device (rare), detect and merge


4.4 Error Handling & Fallback
Email Failures:

If email queue fails 3 times, log to admin dashboard
Send Slack/Telegram notification to tech team
Store failed notification details for manual retry
Show "Email delivery failed" in admin panel

FCM Failures:

If token invalid, set is_active = false in database
If service unavailable, retry after 5 minutes
If quota exceeded, switch to batching mode (slower sends)
Log all FCM errors to fcm_logs table

Database Notification Failures:

Should rarely fail, but if it does:
Log error without blocking order creation
Retry in background job after 30 seconds

Graceful Degradation:

If FCM is down, rely on email + in-app
If email is down, ensure in-app + FCM work
Never block order creation due to notification failures


4.5 Rate Limiting & Batching
Per-User Rate Limits:

Max 10 notifications per user per hour
If limit exceeded, batch into digest notification
Admins: higher limit (50 per hour)

System-Wide Rate Limits:

Max 1000 FCM messages per minute (to avoid quota issues)
Queue excess messages with delay
Use Laravel's RateLimiter facade

Batch Notification Scenarios:

If 20+ orders created in 5 minutes, send admins a summary email
Daily digest option for users (opt-in)


Phase 5: User Preferences & Permissions (Week 5-6)
5.1 Notification Preference UI
Filament User Settings Page:

Checkbox toggles: Email, Push, In-App
Per-notification-type granularity:

"Notify me when my orders are created" ✓
"Notify me when order status changes" ✓
"Notify me when new comments are added" ✗



Default Preferences:

All channels enabled by default
Users can disable individually
Admins cannot fully disable order creation notifications (policy)

API Endpoint for Mobile Apps:

GET /api/user/notification-preferences
PUT /api/user/notification-preferences (with validation)


5.2 Permission & Privacy
Admin vs Customer Access:

Admins see ALL order notifications
Customers only see their own orders + watched orders
Use Laravel Policies: NotificationPolicy@view

Data Privacy:

Notification data (JSON) should not expose sensitive info (e.g., payment details)
Emails sent over TLS
FCM tokens stored encrypted (optional, but recommended)

Opt-Out Compliance:

Users can disable notifications entirely
Still show critical notifications (e.g., account security)
Respect user's locale for all communications


Phase 6: API Endpoints & Integration Points (Week 6)
6.1 Public API Endpoints
Device Token Management:

POST /api/user/device-tokens - Register new token

Body: {device_token, platform}
Response: {success: true, token_id}


DELETE /api/user/device-tokens/{id} - Remove token

Notification Retrieval:

GET /api/notifications - List user's notifications (paginated)

Query params: ?unread_only=true&limit=20
Response: Array of notifications with read_at, data, created_at


GET /api/notifications/unread-count - Get unread count (for badge)

Notification Actions:

POST /api/notifications/{id}/mark-as-read - Mark single as read
POST /api/notifications/mark-all-read - Mark all as read
DELETE /api/notifications/{id} - Delete notification


6.2 Admin API Endpoints
Manual Notification Sending:

POST /api/admin/notifications/send - Send custom notification

Body: {user_ids[], message, type, channels[]}
Use case: Manual announcements or follow-ups



Notification Analytics:

GET /api/admin/notifications/stats - Delivery rates, open rates, failure counts


6.3 Webhook Integrations (Optional)
External System Notifications:

Allow third-party systems to trigger notifications via webhook
Example: Payment gateway sends webhook → trigger "Payment Received" notification
Secure with HMAC signature validation


Phase 7: Testing Strategy (Week 7)
7.1 Unit Tests
Service Tests:

NotificationServiceTest: Test channel selection logic based on preferences
EmailNotificationServiceTest: Test email building, localization, retry logic
FCMNotificationServiceTest: Mock FCM API, test token invalidation
DatabaseNotificationServiceTest: Test bulk inserts, deduplication

Event Listener Tests:

Test each listener in isolation
Mock NotificationService calls
Verify correct data passed to service


7.2 Integration Tests
End-to-End Workflows:

Create order → verify admin email queued + in-app notification created + FCM job dispatched
Change order status → verify all parties notified
User opts out of email → verify only FCM and in-app sent

Database Tests:

Verify notification records created correctly
Test foreign key constraints
Test notification deletion cascades


7.3 Manual Testing Scenarios
Test Cases:

Admin creates order → check all admins receive notifications
User with no device token → should only get email + in-app
User with Arabic locale → verify RTL email and Arabic push notification
100 orders created simultaneously → verify no duplicate notifications
FCM server down → verify graceful fallback to email
User marks notification as read → verify unread count updates in real-time (Livewire)

Load Testing:

Simulate 1000 orders created in 1 minute
Measure queue processing time
Ensure no notification loss


Phase 8: Deployment & Monitoring (Week 8)
8.1 Database Migrations
Migration Order:

Create user_device_tokens table
Create notification_preferences table (with default rows for existing users)
Create notifications table
Create order_status_history table
Alter orders table (add watchers, assigned_user_id)

Rollback Plan:

Migrations should be reversible
Keep old notification system running for 1 week during transition


8.2 Queue Configuration
Queue Setup:

Use Redis for queue driver (better performance than database)
Separate queues: notifications, emails, fcm
Priority: high, default, low
Queue workers: Run at least 3 workers per queue

Supervisor Configuration:

Restart workers daily to prevent memory leaks
Set max_jobs per worker to 1000
Configure failure thresholds


8.3 Monitoring & Logging
Metrics to Track:

Notification delivery rate (email, FCM, in-app)
Average time from event to notification delivery
Failed notification count by type
Queue length and processing time
FCM token invalidation rate

Logging Strategy:

Log all notification events to notifications.log
Separate log for FCM: fcm.log
Use Laravel Telescope in staging for debugging
Set up alerts: Slack notification if notification failure rate > 5%

Dashboard (Filament Admin Panel):

Widget: Notifications sent today (by type)
Widget: Failed notifications (with retry button)
Table: Recent notifications (filterable by user, type, status)


Phase 9: Enhancements & Future Features (Post-MVP)
9.1 Advanced Features
Notification Scheduling:

Allow admins to schedule notifications for future delivery
Use case: "Notify customer 24 hours before order due date"

Notification Templates:

Admin UI to customize email/push templates without code changes
Support dynamic placeholders: {customer_name}, {order_id}

Rich Push Notifications:

iOS: Notification actions (Accept, Decline, View)
Android: Expandable notifications with images
Web: Notification with custom buttons

SMS Notifications (Optional):

Integrate Twilio or similar for critical notifications
Use case: Order ready for pickup

WhatsApp Notifications (Optional):

Use WhatsApp Business API for high-priority updates


9.2 Optimization Opportunities
Notification Aggregation:

If user has 10 unread notifications, send 1 digest email instead of 10
Configurable: Aggregate after N notifications or X minutes

Intelligent Notification Timing:

Respect user's timezone
Don't send push notifications outside 8am-10pm (user's local time)

Notification Importance Scoring:

AI/ML to predict which notifications user will engage with
Prioritize high-importance notifications



// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyA3ulJoBAsa0NUjEQyVX1HbQfkXbDxCqnE",
  authDomain: "sweethome-10699.firebaseapp.com",
  projectId: "sweethome-10699",
  storageBucket: "sweethome-10699.firebasestorage.app",
  messagingSenderId: "361896118081",
  appId: "1:361896118081:web:b81b5f22847b60585903c0",
  measurementId: "G-7KJLV9BMLF"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

sender id = 361896118081
web push key = BG0QDmjK0eVD98kLM3TxpzYasmkoZaM0Cyaw6N_D_lWerC9xO5SgQqH034aUV5pT1iZow12WwdTE2FdNlEOCm1k

C:\xampp2\htdocs\SweetHome\sweethome-10699-firebase-adminsdk-fbsvc-d35ebff368.json