# PHP Configuration Fix for Purchase Entry Limitation

## Problem
Only 65 products are being processed when submitting purchase entries, regardless of how many are added. This is due to PHP's `max_input_vars` limit.

## Immediate Fix (Server Configuration)

### Option 1: Edit php.ini
```ini
; Increase from default 1000 to 10000
max_input_vars = 10000

; Also increase these for large forms
post_max_size = 64M
max_input_time = 300
```

After editing, restart your web server:
```bash
# For Apache
sudo service apache2 restart

# For PHP-FPM
sudo service php-fpm restart
```

### Option 2: Add to .htaccess (if php.ini access not available)
```apache
php_value max_input_vars 10000
php_value post_max_size 64M
php_value max_input_time 300
```

### Option 3: Add to index.php (temporary fix)
Add this at the top of `index.php` after `<?php`:
```php
ini_set('max_input_vars', 10000);
```

## Calculation
- Each product row = 14 input fields
- With max_input_vars = 10000:
  - Maximum products = 10000 ÷ 14 ≈ 714 products
- With max_input_vars = 5000:
  - Maximum products = 5000 ÷ 14 ≈ 357 products

## Permanent Solution (Code Change)
Instead of sending individual form fields, we should send product data as JSON. This would bypass the max_input_vars limitation entirely.

### Implementation Steps:
1. Modify the purchase form to collect product data in JavaScript array
2. Convert array to JSON before submission
3. Send as single field: `products_json`
4. Decode JSON in PHP controller

This would allow unlimited products with just one input variable.