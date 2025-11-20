-- Migration: Add currency fields to ub_retailbillmaster table (SAFE VERSION)
-- Date: 2025-11-20
-- Purpose: Add support for multi-currency billing
-- This version checks if columns exist before adding them

-- Add rb_country column if it doesn't exist
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      table_schema = DATABASE()
      AND table_name = 'ub_retailbillmaster'
      AND column_name = 'rb_country'
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  'ALTER TABLE `ub_retailbillmaster` ADD COLUMN `rb_country` int(11) DEFAULT NULL COMMENT "Country ID for billing" AFTER `rb_state`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add rb_currency column if it doesn't exist
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      table_schema = DATABASE()
      AND table_name = 'ub_retailbillmaster'
      AND column_name = 'rb_currency'
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  'ALTER TABLE `ub_retailbillmaster` ADD COLUMN `rb_currency` varchar(10) DEFAULT "INR" COMMENT "Currency code (INR, USD, EUR, etc.)" AFTER `rb_country`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add rb_conversionrate column if it doesn't exist
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      table_schema = DATABASE()
      AND table_name = 'ub_retailbillmaster'
      AND column_name = 'rb_conversionrate'
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  'ALTER TABLE `ub_retailbillmaster` ADD COLUMN `rb_conversionrate` decimal(12,6) DEFAULT 1.000000 COMMENT "Conversion rate to INR" AFTER `rb_currency`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add indexes if they don't exist
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      table_schema = DATABASE()
      AND table_name = 'ub_retailbillmaster'
      AND index_name = 'idx_currency'
  ) > 0,
  'SELECT 1',
  'CREATE INDEX `idx_currency` ON `ub_retailbillmaster` (`rb_currency`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      table_schema = DATABASE()
      AND table_name = 'ub_retailbillmaster'
      AND index_name = 'idx_country'
  ) > 0,
  'SELECT 1',
  'CREATE INDEX `idx_country` ON `ub_retailbillmaster` (`rb_country`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
