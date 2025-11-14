# SQL Commands for Multi-Currency Feature

## IMPORTANT: Run these SQL commands in phpMyAdmin BEFORE deploying the code

### Step 1: Add Currency Field to Customers Table

```sql
ALTER TABLE `ub_customers`
ADD COLUMN `ct_currency` VARCHAR(10) NULL DEFAULT 'INR' COMMENT 'Customer preferred currency' AFTER `ct_gstin`;
```

### Step 2: Add Currency Fields to Retail Billing Table

```sql
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_currency` VARCHAR(10) NULL DEFAULT 'INR' COMMENT 'Bill currency code' AFTER `rb_state`,
ADD COLUMN `rb_conversionrate` DECIMAL(12,6) NULL DEFAULT 1.000000 COMMENT 'Exchange rate used (to INR)' AFTER `rb_currency`;
```

### Step 3: Verify the Changes

```sql
-- Check customers table
DESCRIBE `ub_customers`;

-- Check retail billing table
DESCRIBE `ub_retailbillmaster`;

-- Check if default values are set correctly
SELECT * FROM `ub_customers` LIMIT 1;
SELECT * FROM `ub_retailbillmaster` LIMIT 1;
```

### Step 4: Update Existing Data (Optional)

```sql
-- Set INR as default for all existing customers
UPDATE `ub_customers` SET `ct_currency` = 'INR' WHERE `ct_currency` IS NULL;

-- Set INR and rate 1.0 for all existing bills
UPDATE `ub_retailbillmaster`
SET `rb_currency` = 'INR', `rb_conversionrate` = 1.000000
WHERE `rb_currency` IS NULL;
```

## What This Enables:

1. **Customers can have their preferred currency** (INR, USD, EUR, GBP, AED, etc.)
2. **Billing in foreign currencies** with automatic exchange rates
3. **Tax-free international billing** - No GST/VAT for foreign currency bills
4. **Exchange rate tracking** - Each bill stores the conversion rate used

## Supported Currencies:

- INR - Indian Rupee (₹) - Default, with taxes
- USD - US Dollar ($) - Tax-free
- EUR - Euro (€) - Tax-free
- GBP - British Pound (£) - Tax-free
- AED - UAE Dirham (د.إ) - Tax-free
- SAR - Saudi Riyal (ر.س) - Tax-free
- OMR - Omani Rial (ر.ع.) - Tax-free
- KWD - Kuwaiti Dinar (د.ك) - Tax-free
- QAR - Qatari Riyal (ر.ق) - Tax-free
- BHD - Bahraini Dinar (.د.ب) - Tax-free
- CAD - Canadian Dollar (C$) - Tax-free
- AUD - Australian Dollar (A$) - Tax-free
- SGD - Singapore Dollar (S$) - Tax-free
- MYR - Malaysian Ringgit (RM) - Tax-free
- THB - Thai Baht (฿) - Tax-free

## Rollback (If Needed):

```sql
-- Remove currency fields from customers
ALTER TABLE `ub_customers` DROP COLUMN `ct_currency`;

-- Remove currency fields from retail billing
ALTER TABLE `ub_retailbillmaster` DROP COLUMN `rb_currency`, DROP COLUMN `rb_conversionrate`;
```

## Next Steps After Running SQL:

1. Deploy the updated code files
2. Test adding a customer with foreign currency
3. Test creating a bill with foreign currency customer
4. Verify taxes are removed for foreign currency bills
5. Check bill prints show currency symbols correctly
