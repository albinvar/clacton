# Multi-Currency Implementation Plan for CLACTON ERP

## Overview
This document outlines the implementation of multi-currency support for international billing without taxes.

## Requirements
1. Add currency field to customers (optional, defaults to INR)
2. When creating bills with foreign currency customers:
   - Auto-populate currency from customer
   - Allow changing currency at bill time
   - Remove ALL taxes (GST/VAT) for foreign currency
   - Show currency conversion rate (API + manual fallback)
   - Store all amounts in selected currency
3. Update print templates to show foreign currency without taxes

## Database Schema Changes

### 1. Customer Table (`ub_customers`)
```sql
ALTER TABLE `ub_customers`
ADD COLUMN `ct_currency` VARCHAR(10) NULL DEFAULT 'INR' COMMENT 'Customer currency code' AFTER `ct_gstin`;
```

### 2. Retail Bill Master Table (`ub_retailbillmaster`)
```sql
ALTER TABLE `ub_retailbillmaster`
ADD COLUMN `rb_currency` VARCHAR(10) NULL DEFAULT 'INR' COMMENT 'Bill currency code' AFTER `rb_state`,
ADD COLUMN `rb_conversionrate` DECIMAL(12,6) NULL DEFAULT 1.000000 COMMENT 'Conversion rate to INR' AFTER `rb_currency`;
```

### 3. Common Currencies to Support
- INR (Indian Rupee) - Default
- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)
- AED (UAE Dirham)
- SAR (Saudi Riyal)
- OMR (Omani Rial)
- KWD (Kuwaiti Dinar)
- QAR (Qatari Riyal)
- BHD (Bahraini Dinar)

## Implementation Steps

### Phase 1: Customer Module

#### Files to Modify:
1. **ci/application/modules/business/views/addcustomer.php**
   - Add currency dropdown field
   - Position after GSTIN field

2. **ci/application/modules/business/views/customers.php**
   - Add currency column to customer list

3. **ci/application/modules/business/controllers/Business.php**
   - Function `addcustomerprocess()`: Add ct_currency to insert/update

4. **ci/application/modules/business/models/Customers_model.php**
   - Add ct_currency to select_fields

### Phase 2: Currency Helper

#### Create New File: **ci/application/helpers/currency_helper.php**
```php
<?php
function get_currencies() {
    return [
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ'],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'ر.س'],
        'OMR' => ['name' => 'Omani Rial', 'symbol' => 'ر.ع.'],
        'KWD' => ['name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك'],
        'QAR' => ['name' => 'Qatari Riyal', 'symbol' => 'ر.ق'],
        'BHD' => ['name' => 'Bahraini Dinar', 'symbol' => '.د.ب']
    ];
}

function get_currency_symbol($code) {
    $currencies = get_currencies();
    return isset($currencies[$code]) ? $currencies[$code]['symbol'] : $code;
}

// Free Exchange Rate API: exchangerate-api.com
function get_exchange_rate($from = 'USD', $to = 'INR') {
    $url = "https://api.exchangerate-api.com/v4/latest/{$from}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['rates'][$to])) {
            return $data['rates'][$to];
        }
    }

    return false; // API failed, manual entry required
}
```

### Phase 3: Retail Billing Module

#### Files to Modify:
1. **ci/application/modules/sale/views/dashboard.php**
   - Add currency dropdown (auto-populated from customer)
   - Add conversion rate field (auto-filled from API or manual)
   - Add JavaScript to handle currency selection
   - Hide/show tax fields based on currency

2. **ci/application/modules/sale/controllers/Sale.php**
   - Function `addsale()`: Handle foreign currency logic
   - If currency != INR:
     - Set rb_totalgstamnt = 0
     - Set item taxes = 0
     - Store conversion rate

3. **ci/application/modules/sale/models/Retailbillmaster_model.php**
   - Add rb_currency and rb_conversionrate to selectedfields

### Phase 4: Print Templates

#### Files to Modify:
1. **ci/application/modules/sale/views/saleprint1.php**
   - Check rb_currency field
   - If foreign currency:
     - Hide tax columns completely
     - Show currency symbol instead of ₹
     - Show "Tax-Free International Bill" note

2. **ci/application/modules/sale/views/saleprint2.php**
   - Same changes as saleprint1.php

3. **ci/application/modules/sale/views/thermalprint.php**
   - Same changes for thermal printing

## JavaScript Logic for Billing Form

### Currency Selection Handler
```javascript
$('#customerid').change(function() {
    var custid = $(this).val();
    if(custid) {
        $.ajax({
            url: base_url + 'business/getcustomerdetails',
            data: {customerid: custid},
            success: function(response) {
                var cust = JSON.parse(response);
                if(cust.ct_currency && cust.ct_currency != 'INR') {
                    $('#currency').val(cust.ct_currency);
                    fetchExchangeRate(cust.ct_currency);
                    hideTaxFields();
                } else {
                    $('#currency').val('INR');
                    $('#conversionrate').val(1);
                    showTaxFields();
                }
            }
        });
    }
});

$('#currency').change(function() {
    var currency = $(this).val();
    if(currency == 'INR') {
        $('#conversionrate').val(1);
        showTaxFields();
    } else {
        fetchExchangeRate(currency);
        hideTaxFields();
    }
});

function fetchExchangeRate(currency) {
    $.ajax({
        url: base_url + 'sale/getexchangerate',
        data: {currency: currency},
        success: function(rate) {
            if(rate) {
                $('#conversionrate').val(rate);
            } else {
                alert('Exchange rate API unavailable. Please enter manually.');
                $('#conversionrate').focus();
            }
        }
    });
}

function hideTaxFields() {
    $('.tax-field').hide();
    $('.tax-column').hide();
    // Set all tax inputs to 0
    $('[name^="gst"]').val(0);
    recalculateTotals();
}

function showTaxFields() {
    $('.tax-field').show();
    $('.tax-column').show();
    recalculateTotals();
}
```

## Testing Checklist

- [ ] Add customer with USD currency
- [ ] Add customer with INR currency (default)
- [ ] Create bill with USD customer - verify tax is 0
- [ ] Create bill with INR customer - verify tax calculates normally
- [ ] Change currency on bill from INR to USD - verify taxes disappear
- [ ] Verify conversion rate auto-fills from API
- [ ] Test manual conversion rate entry when API fails
- [ ] Print USD bill - verify no tax columns shown
- [ ] Print INR bill - verify tax columns shown
- [ ] Edit USD bill and verify currency persists
- [ ] Check customer balance in foreign currency

## API Information

**Free Exchange Rate API:** https://www.exchangerate-api.com/
- Free tier: 1,500 requests/month
- No API key required for basic endpoint
- Updates daily
- Endpoint: `https://api.exchangerate-api.com/v4/latest/{currency}`

## Rollback Plan

If issues arise:
```sql
-- Remove currency fields
ALTER TABLE `ub_customers` DROP COLUMN `ct_currency`;
ALTER TABLE `ub_retailbillmaster` DROP COLUMN `rb_currency`, DROP COLUMN `rb_conversionrate`;
```

Restore modified files from git:
```bash
git checkout HEAD -- ci/application/modules/business/
git checkout HEAD -- ci/application/modules/sale/
```
