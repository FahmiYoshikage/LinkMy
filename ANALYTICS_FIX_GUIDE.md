# Analytics Fix - Geographic Location & Realtime Data

## Problem Fixed
1. ✅ **Traffic Sources** showed referrer sources (Instagram, Facebook) instead of geographic location
2. ✅ **Click Trends** showed flat zero line because no data was being tracked
3. ✅ **link_analytics table** was not being populated when users clicked links

## Changes Made

### 1. redirect.php - Click Tracking Implementation
**Added realtime analytics tracking:**
- Captures IP address (handling proxies with X-Forwarded-For)
- Gets country/city using free ip-api.com geolocation service
- Inserts into `link_analytics` table with timestamp
- Handles local network IPs gracefully
- Silent fail on geolocation errors to prevent redirect blocking

**Data captured:**
- `referrer` - HTTP_REFERER for source tracking
- `user_agent` - Browser/device information
- `ip_address` - Real IP handling proxies
- `country` - From geolocation API
- `city` - From geolocation API
- `clicked_at` - NOW() for realtime tracking

### 2. admin/dashboard.php - Chart Queries Update

**Click Trends Query (Realtime):**
- Changed from `DATE_SUB(NOW(), INTERVAL 7 DAY)` to `DATE_SUB(CURDATE(), INTERVAL 6 DAY)`
- Added `DATE(clicked_at) <= CURDATE()` to explicitly include today
- Now shows last 7 days including current day for realtime updates

**Traffic Sources Query (Geographic):**
- Changed from referrer-based CASE statement to location-based query
- Primary query: Shows "City, Country" if city available, otherwise just "Country"
- Fallback query: Shows IP-based summary if no country data yet
- Limits to top 10 locations for clean visualization
- Groups by location and sorts by click count

**Chart Display:**
- Title: "Traffic Sources Distribution" → "Traffic by Location"
- Subtitle: "Where your visitors are coming from" → "Geographic distribution of your visitors"
- Variable: `$click_by_referrer` → `$click_by_location`

### 3. Database Migration

**File:** `database_add_city_column.sql`

Adds city column to link_analytics:
```sql
ALTER TABLE link_analytics 
ADD COLUMN IF NOT EXISTS city VARCHAR(100) DEFAULT NULL AFTER country;

ALTER TABLE link_analytics 
ADD INDEX IF NOT EXISTS idx_location (country, city);
```

## Deployment Steps

### Local Testing (XAMPP)
1. Run migration:
```sql
SOURCE c:/xampp/htdocs/database_add_city_column.sql
```

2. Verify column:
```sql
DESCRIBE link_analytics;
```

3. Clear any old data:
```sql
TRUNCATE link_analytics;
```

4. Test clicking a link and check:
```sql
SELECT * FROM link_analytics ORDER BY clicked_at DESC LIMIT 5;
```

### VPS Deployment

**Step 1: Backup database**
```bash
mysqldump -u root -p linkmy_db > backup_before_analytics_$(date +%Y%m%d).sql
```

**Step 2: Pull latest code**
```bash
cd /path/to/linkmy
git pull origin main
```

**Step 3: Run migration**
```bash
mysql -u root -p linkmy_db < database_add_city_column.sql
```

**Step 4: Restart Docker (if using)**
```bash
docker-compose restart
```

**Step 5: Test**
1. Visit your profile page
2. Click one of your links
3. Check analytics table:
```sql
SELECT analytics_id, link_id, country, city, clicked_at 
FROM link_analytics 
ORDER BY clicked_at DESC 
LIMIT 5;
```

4. Visit dashboard and verify:
   - Click Trends shows data for today
   - Traffic by Location shows countries/cities

## Expected Behavior

### Traffic by Location Chart
- Shows pie chart with countries (or city, country if available)
- Example: "Jakarta, Indonesia", "Singapore", "Malaysia", "Unknown"
- Top 10 locations by click count
- Falls back to IP summary if no country data yet

### Click Trends Chart
- Shows last 7 days including today
- Updates in realtime as clicks come in
- Line graph with daily click counts
- Missing dates filled with 0 clicks

## Geolocation Service

**Provider:** ip-api.com (free tier)
- 45 requests/minute limit
- No API key required
- Fields: status, country, city
- Endpoint: `http://ip-api.com/json/{ip}?fields=status,country,city`

**Fallback:** If geolocation fails, stores "Unknown" without blocking redirect

**Local IPs:** Detected and labeled as "Local Network" without API call

## Database Schema

### link_analytics table
```sql
CREATE TABLE `link_analytics` (
  `analytics_id` int NOT NULL AUTO_INCREMENT,
  `link_id` int NOT NULL,
  `clicked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `referrer` varchar(255) DEFAULT NULL,
  `user_agent` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,  -- NEW COLUMN
  PRIMARY KEY (`analytics_id`),
  KEY `link_id` (`link_id`),
  KEY `idx_location` (`country`, `city`),  -- NEW INDEX
  CONSTRAINT `link_analytics_ibfk_1` FOREIGN KEY (`link_id`) REFERENCES `links` (`link_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Performance Notes

1. **Geolocation API calls**: Non-blocking, silent fail
2. **Index added**: On (country, city) for faster queries
3. **Query limit**: Top 10 locations to prevent overcrowding
4. **Caching**: No caching on analytics - always fresh data

## Troubleshooting

### Charts still show no data
```sql
-- Check if data is being inserted
SELECT COUNT(*) FROM link_analytics;

-- Check last 5 clicks
SELECT * FROM link_analytics ORDER BY clicked_at DESC LIMIT 5;
```

### Geolocation not working
```bash
# Test API manually
curl "http://ip-api.com/json/8.8.8.8?fields=status,country,city"
```

### Click Trends not showing today
```sql
-- Check today's clicks
SELECT DATE(clicked_at) as date, COUNT(*) as clicks 
FROM link_analytics 
WHERE DATE(clicked_at) = CURDATE();
```

## Files Modified
- ✅ `redirect.php` - Added analytics tracking with geolocation
- ✅ `admin/dashboard.php` - Updated queries for location + realtime
- ✅ `database_add_city_column.sql` - New migration file

## Testing Checklist
- [ ] Migration runs without errors
- [ ] Clicking links inserts into link_analytics
- [ ] Country column populated
- [ ] City column populated (for non-local IPs)
- [ ] Dashboard shows Traffic by Location chart
- [ ] Dashboard shows Click Trends with today's data
- [ ] Charts update when new clicks happen

---
**Date:** November 2024  
**Version:** 1.1.0  
**Status:** Ready for VPS deployment
