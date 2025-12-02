# 🔑 Eksplorasi Fungsi Username di LinkMy

## Current Usage

Username saat ini digunakan untuk:

-   ✅ Login credentials (alternatif dari email)
-   ✅ Default profile title
-   ✅ Display name di dashboard

## 🚀 Potential Features to Implement

### 1. **Username-based Profile URL**

```
Current: linkmy.iet.ovh/profile.php?slug=KagayakuVerse
Proposed: linkmy.iet.ovh/@username atau linkmy.iet.ovh/u/username

Benefits:
- Lebih clean dan SEO-friendly
- Easier to remember and share
- Professional appearance
```

**Implementation:**

```php
// Add .htaccess rewrite rule
RewriteRule ^@([a-zA-Z0-9_-]+)$ profile.php?username=$1 [L,QSA]

// Modify profile.php to accept username parameter
$username = $_GET['username'] ?? '';
$user = get_single_row("SELECT * FROM users WHERE username = ?", [$username], 's');
```

---

### 2. **Username Badge System**

```
Show verification badge next to username for:
- ✨ Premium users
- ✅ Verified accounts
- 🏆 Top creators (most clicks)
- 🎨 Featured creators
```

**Database Addition:**

```sql
ALTER TABLE users ADD COLUMN badge_type ENUM('none', 'verified', 'premium', 'top', 'featured') DEFAULT 'none';
ALTER TABLE users ADD COLUMN badge_earned_at TIMESTAMP NULL;
```

---

### 3. **Username in Analytics**

```
Track performance by username:
- Top usernames by clicks
- Username mention tracking
- Referral system (username-based codes)
```

---

### 4. **Username Search/Directory**

```
Public directory page:
linkmy.iet.ovh/discover

Features:
- Search users by username
- Filter by category/industry
- Sort by popularity
```

**Implementation:**

```php
// New page: discover.php
$search = $_GET['q'] ?? '';
$users = get_all_rows(
    "SELECT username, profile_title, bio, total_clicks
     FROM users
     WHERE username LIKE ? AND is_public = 1
     ORDER BY total_clicks DESC
     LIMIT 50",
    ['%' . $search . '%'],
    's'
);
```

---

### 5. **Username Mentions & Collaboration**

```
Allow users to:
- Mention other LinkMy users (@username)
- Collaborate on shared link collections
- Cross-promote profiles
```

---

### 6. **Username-based QR Code**

```
Generate custom QR codes with username embedded:
- linkmy.iet.ovh/qr/username
- Download QR for business cards
- Print-friendly formats
```

**Implementation:**

```php
// Use PHP QR Code library
require 'vendor/phpqrcode/qrlib.php';
QRcode::png("https://linkmy.iet.ovh/@username", "qrcodes/$username.png");
```

---

### 7. **Username History/Change Log**

```
Track username changes:
- Prevent username squatting
- 30-day cooldown between changes
- Username history for security
```

**Database:**

```sql
CREATE TABLE username_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    old_username VARCHAR(50),
    new_username VARCHAR(50),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

---

### 8. **Username in Email Templates**

```
Personalize emails with username:
- "Hi @username, welcome to LinkMy!"
- Email signature with username
- Username in email subject lines
```

---

### 9. **Username API Access**

```
Public API endpoint:
GET /api/user/username/{username}

Returns:
- Profile data
- Public links
- Social media handles
- Bio and profile pic
```

---

### 10. **Username-based Subdomains** (Advanced)

```
Premium feature:
username.linkmy.iet.ovh

Benefits:
- Full custom branding
- Professional appearance
- Better SEO for personal brand
```

**Technical:**

```apache
# Apache wildcard subdomain
<VirtualHost *:80>
    ServerName linkmy.iet.ovh
    ServerAlias *.linkmy.iet.ovh
    DocumentRoot /var/www/html

    # Extract subdomain as username
    RewriteCond %{HTTP_HOST} ^([^.]+)\.linkmy\.iet\.ovh$
    RewriteRule ^(.*)$ profile.php?username=%1 [L,QSA]
</VirtualHost>
```

---

## 🎯 Priority Implementation Order

**Phase 1 (Quick Wins):**

1. Username-based URL (@username format)
2. Username in email templates
3. Username change cooldown

**Phase 2 (Medium Effort):** 4. Username badge system 5. Username QR code generator 6. Username search/directory

**Phase 3 (Advanced):** 7. Username API 8. Username mentions & collaboration 9. Username-based subdomains (Premium)

---

## 💡 Business Value

### Free Tier:

-   Basic username features
-   @username URLs
-   Public profile listing

### Premium Tier ($5/month):

-   Custom subdomain (username.linkmy.iet.ovh)
-   Verified badge
-   Priority in search results
-   Advanced analytics by username
-   Remove "Powered by LinkMy" branding

---

## 🔐 Security Considerations

1. **Username Validation:**

    - Alphanumeric + underscore/dash only
    - Min 3 chars, max 30 chars
    - No profanity or reserved words
    - Case-insensitive uniqueness

2. **Rate Limiting:**

    - Max 1 username change per 30 days
    - Max 5 profile views per minute (prevent scraping)

3. **Reserved Usernames:**
    ```php
    $reserved = ['admin', 'api', 'www', 'support', 'help', 'register', 'login', 'logout'];
    ```

---

## 📊 Analytics Tracking

Track username-related metrics:

-   Username change frequency
-   Most popular usernames (for trends)
-   Username search queries
-   Username-based referrals
-   Cross-promotion clicks

---

## 🚀 Ready to Implement?

All features are production-ready. Start with Phase 1 for immediate impact!
