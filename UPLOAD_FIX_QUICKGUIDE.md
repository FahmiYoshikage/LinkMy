# Upload Folders Fix - Quick Guide

## 🚨 Problem

Upload masih gagal dengan error:

```
Warning: move_uploaded_file(): Failed to open stream: Permission denied
```

## 🔧 Root Cause

Folder `/var/www/html/uploads/` **BELUM DIBUAT** di VPS Docker container.

---

## ✅ Solution 1: Run Script in Container (RECOMMENDED)

### Step 1: Pull Latest Code

```bash
cd /opt/LinkMy
git pull origin master
```

### Step 2: Copy Script to Container

```bash
docker cp create_upload_folders.sh linkmy_web:/var/www/html/
```

### Step 3: Run Script Inside Container

```bash
docker exec linkmy_web bash /var/www/html/create_upload_folders.sh
```

**Expected Output:**

```
Creating upload folders for LinkMy...
Creating directories...
Setting ownership to www-data...
Setting permissions...

Verification:
drwxrwxrwx 2 www-data www-data profile_pics
drwxrwxrwx 2 www-data www-data backgrounds
drwxrwxrwx 2 www-data www-data folder_pics

Testing write permission...
✅ Uploads directory is writable!

Done! Upload functionality should work now.
```

### Step 4: Test Upload

1. Go to linkmy.iet.ovh/admin/appearance.php
2. Upload profile picture
3. Upload background image
4. Should work! ✅

---

## ✅ Solution 2: Manual Commands

Jika script tidak work, run manual commands:

```bash
# Run in container
docker exec -it linkmy_web bash

# Inside container:
mkdir -p /var/www/html/uploads/profile_pics
mkdir -p /var/www/html/uploads/backgrounds
mkdir -p /var/www/html/uploads/folder_pics
chown -R www-data:www-data /var/www/html/uploads
chmod -R 777 /var/www/html/uploads

# Verify
ls -la /var/www/html/uploads/
```

**Should show:**

```
drwxrwxrwx 2 www-data www-data profile_pics
drwxrwxrwx 2 www-data www-data backgrounds
drwxrwxrwx 2 www-data www-data folder_pics
```

**Exit container:**

```bash
exit
```

---

## ✅ Solution 3: Update Dockerfile (PERMANENT FIX)

Tambahkan ke `Dockerfile` sebelum EXPOSE:

```dockerfile
# Create upload directories with correct permissions
RUN mkdir -p /var/www/html/uploads/profile_pics \
    && mkdir -p /var/www/html/uploads/backgrounds \
    && mkdir -p /var/www/html/uploads/folder_pics \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 777 /var/www/html/uploads
```

**Then rebuild:**

```bash
cd /opt/LinkMy
docker-compose down
docker-compose up -d --build
```

---

## 🧪 Testing

### Test Profile Picture Upload:

```bash
# Upload via website, then check:
docker exec linkmy_web ls -la /var/www/html/uploads/profile_pics/
```

### Test Background Image Upload:

```bash
docker exec linkmy_web ls -la /var/www/html/uploads/backgrounds/
```

### Check Apache Logs:

```bash
docker logs linkmy_web --tail 50
# Should NOT see permission denied errors
```

---

## 📱 Mobile Drag & Drop Fix

### What Changed:

1. **Prevent document scroll** during drag
2. **Hide element temporarily** to detect element below
3. **Better visual feedback** (shadow, opacity)
4. **Proper cleanup** on touchend

### How to Test on Mobile:

1. Open dashboard on phone
2. Press and hold link item for 0.5 seconds
3. Drag up or down (page should NOT scroll)
4. Drop on another item
5. Order should update ✅

### Debugging on Mobile:

```javascript
// Add to admin.js for debugging
console.log('Touch start:', touchStartY);
console.log('Touch move:', touchCurrentY);
console.log('Element below:', elementBelow);
```

---

## 🖼️ Background Preview Fix

### What Changed:

-   Auto-create `<img id="bgImagePreview">` if not exists
-   Show preview in upload box before submitting
-   Update live preview container background

### How to Test:

1. Go to Appearance → Theme tab
2. Click "Click to Upload Background"
3. Select image
4. ✅ Image preview should show in upload box
5. ✅ Live preview background should update
6. Click "Upload Background Image"
7. ✅ Should upload successfully

---

## 🚀 Quick Deploy Commands

**All in one:**

```bash
cd /opt/LinkMy && \
git pull origin master && \
docker cp create_upload_folders.sh linkmy_web:/var/www/html/ && \
docker exec linkmy_web bash /var/www/html/create_upload_folders.sh && \
docker exec linkmy_web ls -la /var/www/html/uploads/ && \
echo "✅ Done! Test upload now."
```

---

## ✅ Success Criteria

After fix:

-   [ ] Profile picture uploads successfully
-   [ ] Background image uploads successfully
-   [ ] Preview shows before upload
-   [ ] Live preview updates instantly
-   [ ] Mobile drag & drop works (no scroll)
-   [ ] No permission errors in logs

---

**Priority: RUN SOLUTION 1 FIRST!** 🚀
