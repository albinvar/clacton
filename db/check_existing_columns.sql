-- Check what currency-related columns actually exist
SHOW COLUMNS FROM ub_retailbillmaster LIKE 'rb_%currency%';
SHOW COLUMNS FROM ub_retailbillmaster LIKE 'rb_%conversion%';
SHOW COLUMNS FROM ub_retailbillmaster LIKE 'rb_%country%';

-- Or view all columns to see the structure
DESCRIBE ub_retailbillmaster;
