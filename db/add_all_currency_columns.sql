-- Add ALL missing currency columns to ub_retailbillmaster
-- Copy and run this ENTIRE script in phpMyAdmin SQL tab

-- Add rb_currency column
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_currency` varchar(10) DEFAULT 'INR' COMMENT 'Currency code (INR, USD, EUR, etc.)' AFTER `rb_state`;

-- Add rb_conversionrate column
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_conversionrate` decimal(12,6) DEFAULT 1.000000 COMMENT 'Conversion rate to INR' AFTER `rb_currency`;

-- Add rb_country column
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_country` int(11) DEFAULT NULL COMMENT 'Country ID for billing' AFTER `rb_conversionrate`;

-- Add indexes for better performance
CREATE INDEX `idx_currency` ON `ub_retailbillmaster` (`rb_currency`);
CREATE INDEX `idx_country` ON `ub_retailbillmaster` (`rb_country`);
