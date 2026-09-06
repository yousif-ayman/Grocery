# 🏪 Grocery Platform — Entity Relationship Diagram (ERD)

## 📊 Visual ERD (Mermaid Format)

```mermaid
erDiagram
    %% ═══════════════════════════════════════════════════════════
    %% CORE ENTITIES
    %% ═══════════════════════════════════════════════════════════

    users {
        uuid id PK
        string email UK
        string phone UK
        string password_hash
        string first_name
        string last_name
        string role "guest|registered|delivery|support|admin"
        string status "active|inactive|banned|deleted"
        string preferred_language "en|ar"
        boolean is_verified
        timestamp last_login_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    user_profiles {
        uuid id PK
        uuid user_id FK
        string avatar_url
        date_of_birth date
        string gender
        string locale
        jsonb preferences
        timestamp created_at
        timestamp updated_at
    }

    addresses {
        uuid id PK
        uuid user_id FK
        string label "home|work|other"
        string street_address
        string building
        string apartment
        string city
        string state
        string country
        decimal latitude
        decimal longitude
        boolean is_default
        timestamp created_at
        timestamp updated_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% AUTHENTICATION & SECURITY
    %% ═══════════════════════════════════════════════════════════

    auth_mfa {
        uuid id PK
        uuid user_id FK
        string method "email|phone|authenticator"
        string secret_encrypted
        boolean is_primary
        boolean is_active
        timestamp created_at
        timestamp verified_at
    }

    otp_codes {
        uuid id PK
        uuid user_id FK
        string code_hash
        string purpose "login|mfa|password_reset|sensitive_action"
        string channel "email|phone"
        integer attempts_left
        boolean is_used
        timestamp expires_at
        timestamp created_at
    }

    sessions {
        uuid id PK
        uuid user_id FK
        string token_hash UK
        string ip_address
        string user_agent
        string device_id
        string device_name
        string platform "web|ios|android"
        timestamp last_active_at
        timestamp expires_at
        timestamp created_at
    }

    user_devices {
        uuid id PK
        uuid user_id FK
        string device_id UK
        string device_name
        string platform "web|ios|android"
        string push_token
        boolean is_trusted
        timestamp last_seen_at
        timestamp created_at
    }

    password_history {
        uuid id PK
        uuid user_id FK
        string password_hash
        timestamp created_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% CATALOG & PRODUCTS
    %% ═══════════════════════════════════════════════════════════

    categories {
        uuid id PK
        uuid parent_id FK "self-referencing for subcategories"
        jsonb name "en|ar multilingual"
        jsonb description
        string image_url
        integer sort_order
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    products {
        uuid id PK
        uuid category_id FK
        uuid subcategory_id FK
        jsonb name "en|ar multilingual"
        jsonb description
        string sku UK
        decimal base_price
        string currency "USD|SAR|AED"
        decimal tax_rate
        string unit "kg|piece|pack"
        integer min_order_qty
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    product_media {
        uuid id PK
        uuid product_id FK
        string type "image|video|3d"
        string url
        string alt_text
        integer sort_order
        boolean is_primary
        timestamp created_at
    }

    product_variants {
        uuid id PK
        uuid product_id FK
        jsonb name
        string sku UK
        decimal price
        integer stock_quantity
        jsonb attributes "size|color|weight"
        boolean is_active
        timestamp created_at
    }

    product_pricing {
        uuid id PK
        uuid product_id FK
        decimal price
        decimal compare_at_price "original price for discount display"
        string currency
        date valid_from
        date valid_until
        boolean is_active
        timestamp created_at
    }

    product_discounts {
        uuid id PK
        uuid product_id FK
        string type "percentage|fixed|buy_x_get_y"
        decimal value
        integer min_quantity
        date valid_from
        date valid_until
        boolean is_active
        timestamp created_at
    }

    product_recommendations {
        uuid id PK
        uuid product_id FK
        uuid recommended_product_id FK
        decimal score
        string algorithm "collaborative|content_based|trending"
        timestamp created_at
    }

    inventory {
        uuid id PK
        uuid product_id FK
        uuid variant_id FK
        integer quantity
        integer reserved_quantity
        integer reorder_point
        string warehouse_location
        timestamp last_restocked_at
        timestamp updated_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% CART & COUPONS
    %% ═══════════════════════════════════════════════════════════

    carts {
        uuid id PK
        uuid user_id FK "nullable for guest"
        string session_token "for guest users"
        decimal subtotal
        decimal tax_total
        decimal delivery_fee
        decimal discount_total
        decimal grand_total
        string currency
        timestamp created_at
        timestamp updated_at
        timestamp expires_at
    }

    cart_items {
        uuid id PK
        uuid cart_id FK
        uuid product_id FK
        uuid variant_id FK
        integer quantity
        decimal unit_price
        decimal total_price
        timestamp added_at
        timestamp updated_at
    }

    coupons {
        uuid id PK
        string code UK
        string type "percentage|fixed|free_shipping"
        decimal value
        decimal minimum_order_amount
        integer maximum_uses
        integer used_count
        integer per_user_limit
        date valid_from
        date valid_until
        boolean is_active
        jsonb applicable_categories
        timestamp created_at
    }

    coupon_usages {
        uuid id PK
        uuid coupon_id FK
        uuid user_id FK
        uuid order_id FK
        decimal discount_amount
        timestamp used_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% PAYMENTS & TRANSACTIONS
    %% ═══════════════════════════════════════════════════════════

    payment_methods {
        uuid id PK
        uuid user_id FK
        string type "card|cod|bank_transfer"
        string stripe_payment_method_id
        string card_brand "visa|mastercard|amex"
        string card_last_four
        string card_expiry
        boolean is_default
        boolean is_active
        timestamp created_at
    }

    payments {
        uuid id PK
        uuid order_id FK
        uuid payment_method_id FK
        string stripe_payment_intent_id UK
        string status "pending|processing|completed|failed|refunded|partially_refunded"
        decimal amount
        string currency
        string gateway_response
        timestamp created_at
        timestamp completed_at
        timestamp failed_at
    }

    payment_refunds {
        uuid id PK
        uuid payment_id FK
        uuid order_id FK
        string stripe_refund_id UK
        decimal amount
        string reason
        string status "pending|completed|failed"
        string initiated_by "user|admin|system"
        timestamp created_at
        timestamp completed_at
    }

    transactions {
        uuid id PK
        uuid order_id FK
        uuid payment_id FK
        uuid user_id FK
        string type "charge|refund|adjustment"
        decimal amount
        string currency
        string reference
        string status
        jsonb metadata
        timestamp created_at
    }

    invoices {
        uuid id PK
        uuid order_id FK
        uuid user_id FK
        string invoice_number UK
        decimal subtotal
        decimal tax_amount
        decimal total
        string currency
        string pdf_url
        timestamp issued_at
        timestamp created_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% ORDERS & DELIVERY
    %% ═══════════════════════════════════════════════════════════

    orders {
        uuid id PK
        uuid user_id FK
        uuid delivery_address_id FK
        uuid assigned_driver_id FK
        string order_number UK
        string status "pending|confirmed|processing|out_for_delivery|delivered|cancelled|refunded"
        string type "delivery|pickup"
        string priority "standard|priority"
        decimal subtotal
        decimal tax_amount
        decimal delivery_fee
        decimal discount_amount
        decimal grand_total
        string currency
        string coupon_code
        string special_instructions
        timestamp estimated_delivery_at
        timestamp actual_delivery_at
        timestamp created_at
        timestamp updated_at
    }

    order_items {
        uuid id PK
        uuid order_id FK
        uuid product_id FK
        uuid variant_id FK
        integer quantity
        decimal unit_price
        decimal total_price
        string status "pending|picked|packed|delivered|returned"
        timestamp created_at
    }

    order_status_history {
        uuid id PK
        uuid order_id FK
        string from_status
        string to_status
        string changed_by "user|driver|admin|system"
        uuid changed_by_user_id FK
        string reason
        timestamp created_at
    }

    order_notes {
        uuid id PK
        uuid order_id FK
        uuid user_id FK
        string note_type "customer|driver|support|system"
        text content
        timestamp created_at
    }

    delivery_zones {
        uuid id PK
        string name
        jsonb polygon "GeoJSON coordinates"
        decimal base_fee
        decimal per_km_fee
        integer estimated_minutes
        boolean is_active
        timestamp created_at
    }

    delivery_sla {
        uuid id PK
        string delivery_type "standard|priority"
        integer max_minutes
        integer grace_period_minutes
        decimal penalty_percentage
        boolean is_active
        timestamp created_at
    }

    drivers {
        uuid id PK
        uuid user_id FK
        string vehicle_type "bike|car|van"
        string license_number
        string vehicle_plate
        decimal current_latitude
        decimal current_longitude
        string status "available|busy|offline|on_leave"
        decimal rating
        integer total_deliveries
        timestamp last_location_update
        timestamp created_at
    }

    driver_shifts {
        uuid id PK
        uuid driver_id FK
        timestamp shift_start
        timestamp shift_end
        string status "scheduled|active|completed|cancelled"
        timestamp created_at
    }

    driver_earnings {
        uuid id PK
        uuid driver_id FK
        uuid order_id FK
        decimal base_fee
        decimal tips
        decimal bonus
        decimal total_earned
        string payout_status "pending|paid|held"
        timestamp created_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% TRACKING & NOTIFICATIONS
    %% ═══════════════════════════════════════════════════════════

    order_tracking {
        uuid id PK
        uuid order_id FK
        uuid driver_id FK
        decimal latitude
        decimal longitude
        decimal speed
        decimal heading
        timestamp recorded_at
    }

    notifications {
        uuid id PK
        uuid user_id FK
        string type "order_update|promo|price_drop|delivery|system"
        jsonb title
        jsonb body
        jsonb data "action payload"
        string channel "push|email|sms|in_app"
        string status "pending|sent|delivered|read|failed"
        boolean is_read
        timestamp sent_at
        timestamp read_at
        timestamp created_at
    }

    notification_preferences {
        uuid id PK
        uuid user_id FK
        string type "order_update|promo|price_drop|delivery"
        boolean push_enabled
        boolean email_enabled
        boolean sms_enabled
        timestamp created_at
    }

    user_activity_log {
        uuid id PK
        uuid user_id FK
        string action "view_product|add_to_cart|checkout|purchase"
        uuid product_id FK
        uuid order_id FK
        jsonb metadata
        timestamp created_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% SMART LISTS & FAVORITES
    %% ═══════════════════════════════════════════════════════════

    smart_lists {
        uuid id PK
        uuid user_id FK
        string name
        string description
        boolean is_shared
        integer item_count
        decimal estimated_total
        timestamp last_used_at
        timestamp created_at
        timestamp updated_at
    }

    smart_list_items {
        uuid id PK
        uuid smart_list_id FK
        uuid product_id FK
        integer quantity
        boolean is_frequent
        decimal last_price
        timestamp added_at
    }

    favorites {
        uuid id PK
        uuid user_id FK
        uuid product_id FK
        timestamp created_at
    }

    price_alerts {
        uuid id PK
        uuid user_id FK
        uuid product_id FK
        decimal target_price
        string alert_type "price_drop|back_in_stock"
        boolean is_triggered
        timestamp triggered_at
        timestamp created_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% SUPPORT & AI
    %% ═══════════════════════════════════════════════════════════

    support_tickets {
        uuid id PK
        uuid user_id FK
        uuid assigned_agent_id FK
        string ticket_number UK
        string subject
        text description
        string category "order|payment|delivery|account|other"
        string priority "low|medium|high|urgent"
        string status "open|in_progress|waiting_customer|waiting_internal|resolved|closed"
        string channel "chat|email|phone|in_app"
        timestamp first_response_at
        timestamp resolved_at
        timestamp created_at
        timestamp updated_at
    }

    support_ticket_messages {
        uuid id PK
        uuid ticket_id FK
        uuid sender_id FK
        string sender_type "customer|agent|system"
        text message
        jsonb attachments
        timestamp created_at
    }

    support_agents {
        uuid id PK
        uuid user_id FK
        string department "billing|technical|general"
        integer max_concurrent_tickets
        integer current_ticket_count
        string status "available|busy|away|offline"
        decimal avg_rating
        timestamp last_active_at
        timestamp created_at
    }

    faqs {
        uuid id PK
        uuid category_id FK
        jsonb question
        jsonb answer
        integer sort_order
        integer helpful_count
        boolean is_active
        timestamp created_at
    }

    chat_sessions {
        uuid id PK
        uuid user_id FK
        string type "ai|human|hybrid"
        string status "active|waiting|escalated|closed"
        uuid current_handler_id FK
        uuid escalated_to_agent_id FK
        integer message_count
        timestamp started_at
        timestamp ended_at
    }

    chat_messages {
        uuid id PK
        uuid session_id FK
        uuid sender_id FK
        string sender_type "user|ai|agent"
        text content
        jsonb metadata "intent|confidence|escalation_reason"
        timestamp created_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% ANALYTICS & DASHBOARD
    %% ═══════════════════════════════════════════════════════════

    analytics_events {
        uuid id PK
        uuid user_id FK
        string event_name
        jsonb event_data
        string session_id
        string platform "web|ios|android"
        string device_info
        timestamp created_at
    }

    user_analytics {
        uuid id PK
        uuid user_id FK
        integer total_orders
        decimal total_spent
        integer total_products_viewed
        integer total_sessions
        integer minutes_on_platform
        decimal avg_order_value
        timestamp last_order_at
        timestamp last_active_at
        timestamp calculated_at
    }

    product_analytics {
        uuid id PK
        uuid product_id FK
        integer view_count
        integer add_to_cart_count
        integer purchase_count
        decimal conversion_rate
        decimal avg_rating
        integer review_count
        timestamp period_start
        timestamp period_end
    }

    %% ═══════════════════════════════════════════════════════════
    %% COMPLIANCE & AUDIT
    %% ═══════════════════════════════════════════════════════════

    audit_logs {
        uuid id PK
        uuid user_id FK
        string action
        string entity_type
        uuid entity_id
        jsonb old_values
        jsonb new_values
        string ip_address
        string user_agent
        timestamp created_at
    }

    data_retention_policies {
        uuid id PK
        string entity_type
        integer retention_days
        string action_after_expiry "anonymize|delete"
        boolean is_active
        timestamp created_at
    }

    compliance_settings {
        uuid id PK
        string key UK
        string value
        string description
        boolean is_active
        timestamp updated_at
    }

    %% ═══════════════════════════════════════════════════════════
    %% RELATIONSHIPS
    %% ═══════════════════════════════════════════════════════════

    users ||--o| user_profiles : "has profile"
    users ||--o{ addresses : "has addresses"
    users ||--o{ auth_mfa : "has MFA methods"
    users ||--o{ otp_codes : "receives OTPs"
    users ||--o{ sessions : "has sessions"
    users ||--o{ user_devices : "has devices"
    users ||--o{ password_history : "password changes"

    users ||--o{ carts : "has carts"
    users ||--o{ orders : "places orders"
    users ||--o{ favorites : "has favorites"
    users ||--o{ smart_lists : "has smart lists"
    users ||--o{ notifications : "receives notifications"
    users ||--o{ notification_preferences : "has preferences"
    users ||--o{ user_activity_log : "generates activity"

    categories ||--o{ categories : "parent-subcategory"
    categories ||--o{ products : "contains products"
    products ||--o{ product_media : "has media"
    products ||--o{ product_variants : "has variants"
    products ||--o{ product_pricing : "has pricing"
    products ||--o{ product_discounts : "has discounts"
    products ||--o{ inventory : "has inventory"

    products ||--o{ product_recommendations : "recommended with"
    products ||--o{ product_analytics : "has analytics"

    carts ||--o{ cart_items : "contains items"
    cart_items }o--|| products : "references product"
    cart_items }o--o| product_variants : "references variant"

    orders ||--o{ order_items : "contains items"
    orders ||--o{ order_status_history : "status changes"
    orders ||--o{ order_notes : "has notes"
    orders ||--o{ payments : "has payments"
    orders ||--o{ transactions : "has transactions"
    orders ||--o{ invoices : "has invoices"
    orders ||--o{ order_tracking : "tracked"

    users ||--o{ payments : "makes payments"
    users ||--o{ payment_methods : "has payment methods"
    users ||--o{ transactions : "has transactions"
    users ||--o{ invoices : "has invoices"

    coupons ||--o{ coupon_usages : "used by users"
    coupon_usages }o--|| orders : "applied to order"

    drivers ||--o{ driver_shifts : "has shifts"
    drivers ||--o{ driver_earnings : "has earnings"
    drivers ||--o{ order_tracking : "tracked location"
    users ||--o| drivers : "delivery partner"

    support_tickets ||--o{ support_ticket_messages : "has messages"
    users ||--o{ support_tickets : "creates tickets"
    users ||--o| support_agents : "is support agent"
    faqs ||--o{ support_tickets : "referenced in"

    chat_sessions ||--o{ chat_messages : "contains messages"
    users ||--o{ chat_sessions : "has chat sessions"

    smart_lists ||--o{ smart_list_items : "contains items"
    smart_list_items }o--|| products : "references product"

    price_alerts }o--|| products : "monitors product"
    users ||--o{ price_alerts : "has price alerts"

    users ||--o{ analytics_events : "generates events"
    users ||--o| user_analytics : "has analytics"
    users ||--o{ audit_logs : "audit trail"
```

---

## 📋 Entity Summary Table

| # | Entity | Description | Key Relationships |
|---|--------|-------------|-------------------|
| 1 | **users** | Core user accounts with roles | Hub of all user-related entities |
| 2 | **user_profiles** | Extended profile info | 1:1 with users |
| 3 | **addresses** | Delivery locations | 1:N with users |
| 4 | **auth_mfa** | MFA methods | 1:N with users |
| 5 | **otp_codes** | OTP verification | N:1 with users |
| 6 | **sessions** | Active sessions | 1:N with users |
| 7 | **user_devices** | Registered devices | 1:N with users |
| 8 | **password_history** | Previous passwords | 1:N with users |
| 9 | **categories** | Product hierarchy | Self-referencing, 1:N products |
| 10 | **products** | Core catalog | N:1 category, 1:N variants |
| 11 | **product_media** | Product images/videos | 1:N with products |
| 12 | **product_variants** | Size/color options | N:1 with products |
| 13 | **product_pricing** | Time-based pricing | 1:N with products |
| 14 | **product_discounts** | Product promotions | 1:N with products |
| 15 | **product_recommendations** | AI suggestions | N:M products |
| 16 | **inventory** | Stock management | 1:N products |
| 17 | **carts** | Shopping carts | 1:N cart_items |
| 18 | **cart_items** | Cart contents | N:1 cart, N:1 product |
| 19 | **coupons** | Discount codes | 1:N coupon_usages |
| 20 | **coupon_usages** | Coupon redemptions | N:1 coupon, N:1 order |
| 21 | **payment_methods** | Saved payment info | 1:N users |
| 22 | **payments** | Payment transactions | N:1 order |
| 23 | **payment_refunds** | Refund records | N:1 payment |
| 24 | **transactions** | Financial ledger | N:1 order |
| 25 | **invoices** | Generated invoices | 1:1 order |
| 26 | **orders** | Core orders | Hub of order entities |
| 27 | **order_items** | Order line items | N:1 order, N:1 product |
| 28 | **order_status_history** | Status audit trail | N:1 order |
| 29 | **order_notes** | Order notes | N:1 order |
| 30 | **delivery_zones** | Service areas | GeoJSON polygons |
| 31 | **delivery_sla** | SLA rules | Config table |
| 32 | **drivers** | Delivery partners | 1:1 with users |
| 33 | **driver_shifts** | Work schedules | N:1 drivers |
| 34 | **driver_earnings** | Earnings records | N:1 drivers |
| 35 | **order_tracking** | GPS tracking | N:1 order |
| 36 | **notifications** | All notifications | N:1 users |
| 37 | **notification_preferences** | Notification settings | 1:N users |
| 38 | **user_activity_log** | User behavior | N:1 users |
| 39 | **smart_lists** | Shopping lists | 1:N items |
| 40 | **smart_list_items** | List contents | N:1 list, N:1 product |
| 41 | **favorites** | Saved products | N:M users-products |
| 42 | **price_alerts** | Price notifications | N:1 users, N:1 products |
| 43 | **support_tickets** | Support requests | 1:N messages |
| 44 | **support_ticket_messages** | Ticket conversations | N:1 tickets |
| 45 | **support_agents** | Agent profiles | 1:1 with users |
| 46 | **faqs** | Knowledge base | Config table |
| 47 | **chat_sessions** | AI/Human chat | 1:N messages |
| 48 | **chat_messages** | Chat content | N:1 sessions |
| 49 | **analytics_events** | Event tracking | N:1 users |
| 50 | **user_analytics** | User metrics | 1:1 with users |
| 51 | **product_analytics** | Product metrics | 1:1 with products |
| 52 | **audit_logs** | Security audit | N:1 users |
| 53 | **data_retention_policies** | Data lifecycle | Config table |
| 54 | **compliance_settings** | Compliance config | Key-value store |

---

## 🔗 Key Relationships Diagram

```
                        ┌─────────────┐
                        │    users    │
                        │   (core)    │
                        └──────┬──────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
        ▼                      ▼                      ▼
  ┌──────────┐          ┌──────────┐          ┌──────────┐
  │ profiles │          │ addresses│          │ sessions │
  │  (1:1)   │          │  (1:N)   │          │  (1:N)   │
  └──────────┘          └──────────┘          └──────────┘
        │                      │                      │
        │                      │                      │
        ▼                      ▼                      ▼
  ┌──────────┐          ┌──────────┐          ┌──────────┐
  │auth_mfa  │          │devices   │          │password  │
  │  (1:N)   │          │  (1:N)   │          │history   │
  └──────────┘          └──────────┘          └──────────┘

                        ┌─────────────┐
                        │   orders    │
                        │   (core)    │
                        └──────┬──────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
        ▼                      ▼                      ▼
  ┌──────────┐          ┌──────────┐          ┌──────────┐
  │  items   │          │ payments │          │ tracking │
  │  (1:N)   │          │  (1:N)   │          │  (1:N)   │
  └──────────┘          └──────────┘          └──────────┘
        │                      │                      │
        │                      │                      │
        ▼                      ▼                      ▼
  ┌──────────┐          ┌──────────┐          ┌──────────┐
  │products  │          │refunds   │          │ invoices │
  │          │          │  (1:N)   │          │  (1:1)   │
  └──────────┘          └──────────┘          └──────────┘

                        ┌─────────────┐
                        │  products   │
                        │   (core)    │
                        └──────┬──────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
        ▼                      ▼                      ▼
  ┌──────────┐          ┌──────────┐          ┌──────────┐
  │categories│          │ variants │          │  media   │
  │  (N:1)   │          │  (1:N)   │          │  (1:N)   │
  └──────────┘          └──────────┘          └──────────┘
        │                      │                      │
        │                      │                      │
        ▼                      ▼                      ▼
  ┌──────────┐          ┌──────────┐          ┌──────────┐
  │pricing   │          │discounts │          │inventory │
  │  (1:N)   │          │  (1:N)   │          │  (1:1)   │
  └──────────┘          └──────────┘          └──────────┘
```


