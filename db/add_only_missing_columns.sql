-- Add ONLY the missing currency columns
-- Run each command one by one

-- Try to add rb_conversionrate (might exist already)
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_conversionrate` decimal(12,6) DEFAULT 1.000000 COMMENT 'Conversion rate to INR' AFTER `rb_currency`;

-- Try to add rb_country (might exist already)
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_country` int(11) DEFAULT NULL COMMENT 'Country ID for billing' AFTER `rb_conversionrate`;

-- Add indexes (these might also exist)
CREATE INDEX `idx_currency` ON `ub_retailbillmaster` (`rb_currency`);
CREATE INDEX `idx_country` ON `ub_retailbillmaster` (`rb_country`);
