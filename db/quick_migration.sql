-- Quick migration to add missing rb_country column
-- Run this in phpMyAdmin or MySQL

-- First check if rb_currency column exists
SELECT COUNT(*) as rb_currency_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE table_schema = DATABASE() 
  AND table_name = 'ub_retailbillmaster' 
  AND column_name = 'rb_currency';

-- First check if rb_conversionrate column exists  
SELECT COUNT(*) as rb_conversionrate_exists
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE table_schema = DATABASE() 
  AND table_name = 'ub_retailbillmaster' 
  AND column_name = 'rb_conversionrate';

-- Check if rb_country exists
SELECT COUNT(*) as rb_country_exists
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE table_schema = DATABASE() 
  AND table_name = 'ub_retailbillmaster' 
  AND column_name = 'rb_country';

-- If rb_country doesn't exist (returns 0), run this:
-- ALTER TABLE `ub_retailbillmaster` ADD COLUMN `rb_country` int(11) DEFAULT NULL COMMENT 'Country ID for billing' AFTER `rb_state`;
-- CREATE INDEX `idx_country` ON `ub_retailbillmaster` (`rb_country`);
