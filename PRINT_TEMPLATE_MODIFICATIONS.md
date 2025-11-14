# Print Template Modifications for Multi-Currency

## Files to Modify:
1. `ci/application/modules/sale/views/saleprint1.php`
2. `ci/application/modules/sale/views/saleprint2.php`
3. `ci/application/modules/sale/views/thermalprint.php`

## Changes Required:

### Step 1: Load Currency Helper at Top of Each File

Add after PHP opening tag:
```php
<?php
$this->load->helper('currency');
$bill_currency = isset($purchasedet->rb_currency) ? $purchasedet->rb_currency : 'INR';
$is_foreign_currency = ($bill_currency != 'INR');
$currency_symbol = get_currency_symbol($bill_currency);
?>
```

### Step 2: Modify Tax Column Headers

**Current Code (around line 238-265):**
```php
<?php
if($this->isvatgst == 1) {
    // VAT Business
?>
<th colspan="2">VAT</th>
<?php
} else {
    // GST Business
    if(($purchasedet->rb_state == $businessdet->bu_state || ...)) {
?>
<th colspan="2">CGST</th>
<th colspan="2">SGST</th>
<?php
    } else {
?>
<th colspan="2">IGST</th>
<?php
    }
}
?>
```

**New Code:**
```php
<?php
// For foreign currency, hide tax columns
if(!$is_foreign_currency) {
    if($this->isvatgst == 1) {
        // VAT Business
    ?>
    <th colspan="2">VAT</th>
    <?php
    } else {
        // GST Business
        if(($purchasedet->rb_state == $businessdet->bu_state || ...)) {
        ?>
        <th colspan="2">CGST</th>
        <th colspan="2">SGST</th>
        <?php
        } else {
        ?>
        <th colspan="2">IGST</th>
        <?php
        }
    }
}
?>
```

### Step 3: Modify Product Row Tax Columns

Wrap all tax column `<td>` elements in the product loop with:
```php
<?php if(!$is_foreign_currency) { ?>
    <!-- Tax columns here -->
<?php } ?>
```

### Step 4: Modify Tax Total Row

Wrap the tax total display with:
```php
<?php if(!$is_foreign_currency) { ?>
    <!-- Tax total columns -->
<?php } ?>
```

### Step 5: Replace Currency Symbol

**Find all instances of:**
```php
<?= $this->currencysymbol ?>
```

**Replace with:**
```php
<?= $currency_symbol ?>
```

### Step 6: Add Currency Info to Bill Header (Optional)

After bill number/date, add:
```php
<?php if($is_foreign_currency) { ?>
<tr>
    <td colspan="2"><strong>Currency:</strong> <?= $bill_currency ?> (Rate: <?= number_format($purchasedet->rb_conversionrate, 6) ?> INR)</td>
</tr>
<tr>
    <td colspan="2" style="color: red;"><em>** TAX-FREE INTERNATIONAL INVOICE **</em></td>
</tr>
<?php } ?>
```

## Testing Checklist:

- [ ] Create customer with USD currency
- [ ] Create bill for USD customer
- [ ] Verify no tax columns shown in print
- [ ] Verify currency symbol is $ not ₹
- [ ] Verify conversion rate is displayed
- [ ] Create bill for INR customer
- [ ] Verify tax columns still show correctly
- [ ] Test with VAT business + foreign currency
- [ ] Test with GST business + foreign currency

## Example Foreign Currency Print Output:

```
====================================
INVOICE #INV-001
Date: 2025-11-15
Currency: USD (Rate: 83.500000 INR)
** TAX-FREE INTERNATIONAL INVOICE **
====================================

Product      Qty  Price    Total
Widget A     10   $50.00   $500.00
Widget B     5    $30.00   $150.00

Total: $650.00
====================================
```

## Example INR Print Output (Unchanged):

```
====================================
INVOICE #INV-002
Date: 2025-11-15
====================================

Product      Qty  Price    CGST   SGST   Total
Widget A     10   ₹500     ₹45    ₹45    ₹590
Widget B     5    ₹300     ₹27    ₹27    ₹354

Tax Total:          ₹72    ₹72
Total: ₹944
====================================
```
