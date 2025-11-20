-- Migration: Add rb_country column to ub_retailbillmaster table
-- Date: 2025-11-20
-- Purpose: Add country field for multi-currency billing

-- Add rb_country column (only if it doesn't exist)
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_country` int(11) DEFAULT NULL COMMENT 'Country ID for billing' AFTER `rb_state`;

-- Add index for better query performance
CREATE INDEX `idx_country` ON `ub_retailbillmaster` (`rb_country`);
