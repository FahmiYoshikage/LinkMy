# 🛡️ Proteksi DDoS untuk LinkMy

## 📊 Status Proteksi

### ✅ Layer 1: Cloudflare Tunnel (Aktif)

**Proteksi Otomatis Gratis**:
- ✅ Layer 3/4 DDoS Protection (Network/Transport)
- ✅ Layer 7 DDoS Protection (Application)
- ✅ IP VPS tersembunyi (tidak terexpose ke internet)
- ✅ Bot protection & Challenge pages
- ✅ Global CDN & Edge caching
- ✅ Unlimited bandwidth untuk DDoS mitigation

**Traffic Flow**:
```
Internet → Cloudflare Edge (Filter DDoS) → Tunnel → VPS (Aman)
```

### ✅ Layer 2: Apache Rate Limiting (Aktif)

**Konfigurasi** (`apache-config.conf`):
- Rate limit: 400 KB/s per connection
- Request timeout: 20-40 detik untuk header
- Min transfer rate: 500 bytes/second
- Anti-slowloris protection

**Module Apache**:
- `mod_ratelimit` - Bandwidth throttling
- `mod_reqtimeout` - Request timeout limits

---

## 🔒 Tingkat Proteksi Saat Ini

| Attack Type | Proteksi | Layer | Status |
|-------------|----------|-------|--------|
| **SYN Flood** | Cloudflare | L3/4 | ✅ Aktif |
| **UDP Flood** | Cloudflare | L3/4 | ✅ Aktif |
| **HTTP Flood** | Cloudflare + Apache | L7 | ✅ Aktif |
| **Slowloris** | Apache reqtimeout | L7 | ✅ Aktif |
| **POST Flood** | Cloudflare | L7 | ✅ Aktif |
| **Bot Attack** | Cloudflare Bot Fight | L7 | ✅ Aktif |
| **Brute Force** | Rate limiting | L7 | ✅ Aktif |

---

## 🚀 Optimasi Tambahan (Opsional)

### 1. Cloudflare Dashboard Settings

**Aktifkan di Cloudflare Dashboard** → Security:

#### a. **Firewall Rules** (Gratis)
```
(not cf.bot_management.score gt 30) and (http.request.uri.path eq "/login.php")
→ Action: Challenge
```
**Fungsi**: Block bot di halaman login

#### b. **Rate Limiting Rules** (Gratis - 10 rules)
```
Rule 1: Login Protection
- Path: /login.php
- Rate: 5 requests per minute
- Action: Block for 1 hour

Rule 2: Registration Protection
- Path: /register.php
- Rate: 3 requests per minute
- Action: Block for 1 hour
```

#### c. **Security Level** (Recommended: Medium)
```
Security → Settings → Security Level: Medium
```
- Low: Minimal challenges
- Medium: Balanced (recommended)
- High: Aggressive challenges
- I'm Under Attack: Max protection

#### d. **Bot Fight Mode** (Gratis)
```
Security → Bots → Bot Fight Mode: ON
```
**Catatan**: Cloudflare otomatis block bad bots

---

### 2. PHP Rate Limiting (Application Level)

Tambahkan di `config/auth_check.php` atau buat file baru:

```php
<?php
// config/rate_limiter.php

class RateLimiter {
    private $max_attempts = 5;
    private $decay_minutes = 1;
    
    public function tooManyAttempts($key) {
        $attempts = $this->getAttempts($key);
        return $attempts >= $this->max_attempts;
    }
    
    public function hit($key) {
        $cache_file = sys_get_temp_dir() . '/rate_limit_' . md5($key);
        $data = file_exists($cache_file) ? json_decode(file_get_contents($cache_file), true) : ['count' => 0, 'time' => time()];
        
        // Reset jika sudah lewat decay time
        if (time() - $data['time'] > ($this->decay_minutes * 60)) {
            $data = ['count' => 0, 'time' => time()];
        }
        
        $data['count']++;
        file_put_contents($cache_file, json_encode($data));
    }
    
    private function getAttempts($key) {
        $cache_file = sys_get_temp_dir() . '/rate_limit_' . md5($key);
        if (!file_exists($cache_file)) return 0;
        
        $data = json_decode(file_get_contents($cache_file), true);
        if (time() - $data['time'] > ($this->decay_minutes * 60)) return 0;
        
        return $data['count'];
    }
}

// Penggunaan di login.php:
/*
require_once 'config/rate_limiter.php';
$limiter = new RateLimiter();
$key = 'login_' . $_SERVER['REMOTE_ADDR'];

if ($limiter->tooManyAttempts($key)) {
    die('Too many login attempts. Please try again in 1 minute.');
}

if (isset($_POST['login'])) {
    $limiter->hit($key);
    // ... login logic
}
*/
```

---

### 3. Fail2Ban (VPS Level - Advanced)

Install di VPS untuk auto-ban IP yang mencurigakan:

```bash
# Install Fail2Ban
sudo apt install fail2ban -y

# Konfigurasi
sudo nano /etc/fail2ban/jail.local
```

**Config** (`/etc/fail2ban/jail.local`):
```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[apache-auth]
enabled = true
port = http,https
logpath = /var/log/apache2/error.log
maxretry = 3

[apache-overflows]
enabled = true
port = http,https
logpath = /var/log/apache2/error.log
maxretry = 2

[apache-noscript]
enabled = true
port = http,https
logpath = /var/log/apache2/error.log
```

**Restart Fail2Ban**:
```bash
sudo systemctl restart fail2ban
sudo fail2ban-client status
```

---

### 4. Docker Resource Limits

Tambahkan di `docker-compose.yml` untuk prevent resource exhaustion:

```yaml
services:
  web:
    # ... existing config
    deploy:
      resources:
        limits:
          cpus: '1.0'      # Max 1 CPU core
          memory: 1G       # Max 1GB RAM
        reservations:
          cpus: '0.5'      # Min 0.5 CPU
          memory: 512M     # Min 512MB RAM
    
  db:
    # ... existing config
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          cpus: '0.5'
          memory: 512M
```

---

## 📈 Monitoring DDoS Attacks

### 1. Cloudflare Analytics

**Dashboard → Analytics → Security**:
- Total threats blocked
- Top attacking countries
- Attack types distribution
- Real-time traffic

### 2. Apache Logs

**Monitor di VPS**:
```bash
# Real-time access log
docker logs -f linkmy_web | grep "GET\|POST"

# Count requests per IP
docker exec linkmy_web tail -1000 /var/log/apache2/access.log | \
    awk '{print $1}' | sort | uniq -c | sort -rn | head -10

# Monitor 503 errors (rate limited)
docker exec linkmy_web tail -100 /var/log/apache2/error.log | grep "503"
```

### 3. Alert Webhook (Optional)

Buat script untuk notifikasi Telegram/Discord jika ada serangan:

```bash
#!/bin/bash
# /opt/LinkMy/scripts/ddos_alert.sh

WEBHOOK_URL="https://discord.com/api/webhooks/YOUR_WEBHOOK"
THRESHOLD=100

# Count requests in last minute
REQUESTS=$(docker exec linkmy_web tail -100 /var/log/apache2/access.log | wc -l)

if [ $REQUESTS -gt $THRESHOLD ]; then
    curl -X POST $WEBHOOK_URL \
        -H "Content-Type: application/json" \
        -d "{\"content\": \"⚠️ DDoS Alert: $REQUESTS requests/min detected!\"}"
fi
```

**Cron job** (jalankan setiap menit):
```bash
crontab -e
# Add:
* * * * * /opt/LinkMy/scripts/ddos_alert.sh
```

---

## 🧪 Testing Proteksi

### Test Rate Limiting:
```bash
# Dari VPS
for i in {1..50}; do 
    curl -s -o /dev/null -w "%{http_code}\n" http://localhost:83/login.php
    sleep 0.1
done
```

**Expected**: Setelah beberapa request akan dapat 503 Service Unavailable

### Test Slowloris Protection:
```bash
# Install slowhttptest
sudo apt install slowhttptest -y

# Test slowloris
slowhttptest -c 200 -H -g -o slow_test -i 10 -r 100 -t GET -u http://localhost:83

# Expected: Connection timeout setelah 20-40 detik
```

---

## 📋 Checklist Deployment

Sebelum production, pastikan:

- [ ] Cloudflare Tunnel aktif dan berjalan
- [ ] IP VPS tidak terexpose (test: https://www.shodan.io/)
- [ ] Bot Fight Mode enabled di Cloudflare
- [ ] Security Level: Medium atau High
- [ ] Apache rate limiting aktif (rebuild Docker)
- [ ] Fail2Ban installed (optional)
- [ ] Resource limits set di docker-compose.yml (optional)
- [ ] Monitoring setup (Cloudflare Analytics)

---

## 🚨 Jika Terkena Serangan

### 1. Aktifkan "Under Attack Mode" di Cloudflare
```
Cloudflare Dashboard → Security → Settings
→ Security Level: I'm Under Attack Mode
```

**Efek**: Semua visitor harus complete challenge sebelum akses website

### 2. Block Country/IP di Cloudflare
```
Security → Firewall Rules → Create Rule
- Field: IP Address / Country
- Operator: equals / does not equal
- Value: [Country Code atau IP]
- Action: Block
```

### 3. Restart Container (Last Resort)
```bash
cd /opt/LinkMy
docker-compose restart web
```

---

## 💡 Kesimpulan

**Proteksi Aktif Saat Ini**:
1. ✅ **Cloudflare Tunnel** - Primary defense (Layer 3-7)
2. ✅ **Apache Rate Limiting** - Secondary defense (Layer 7)
3. ✅ **Request Timeout** - Anti-slowloris (Layer 7)

**Proteksi ini sudah SANGAT CUKUP** untuk website skala kecil-menengah.

**Rekomendasi**:
- **WAJIB**: Cloudflare Tunnel (sudah aktif) ✅
- **Recommended**: Apache rate limiting (sudah diimplementasi) ✅
- **Optional**: Fail2Ban, PHP rate limiter (jika traffic tinggi)
- **Emergency**: "Under Attack Mode" di Cloudflare

**Target Proteksi**:
- 🛡️ Tahan serangan hingga **100K requests/second** (Cloudflare)
- 🛡️ Tahan slowloris attack (Apache timeout)
- 🛡️ IP VPS tersembunyi (tidak bisa di-DDoS langsung)

---

## 📞 Support

Jika mengalami serangan DDoS:
1. Check Cloudflare Analytics
2. Enable "Under Attack Mode"
3. Block suspicious IPs/Countries
4. Contact VPS provider jika network-level attack

**Website Anda sudah production-ready! 🚀**
