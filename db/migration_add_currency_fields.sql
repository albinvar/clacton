-- Migration: Add currency fields to ub_retailbillmaster table
-- Date: 2025-11-20
-- Purpose: Add support for multi-currency billing

ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_country` int(11) DEFAULT NULL COMMENT 'Country ID for billing' AFTER `rb_state`,
ADD COLUMN `rb_currency` varchar(10) DEFAULT 'INR' COMMENT 'Currency code (INR, USD, EUR, etc.)' AFTER `rb_country`,
ADD COLUMN `rb_conversionrate` decimal(12,6) DEFAULT 1.000000 COMMENT 'Conversion rate to INR' AFTER `rb_currency`;

-- Add index for better query performance
CREATE INDEX `idx_currency` ON `ub_retailbillmaster` (`rb_currency`);
CREATE INDEX `idx_country` ON `ub_retailbillmaster` (`rb_country`);
