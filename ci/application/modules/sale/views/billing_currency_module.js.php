/**
 * CLACTON Currency Billing Module
 * Clean implementation for multi-currency billing with real-time conversion
 *
 * Features:
 * - Real-time currency conversion
 * - Tax-free billing for foreign currencies
 * - Automatic price updates when currency changes
 * - Proper handling of all price fields (unit, MRP, purchase, net)
 */

<script>
// ============================================================================
// CURRENCY STATE MANAGEMENT
// ============================================================================

var CurrencyState = {
    current: 'INR',
    rate: 1.0,
    decimalPlaces: <?= $this->decimalpoints ?>,

    init: function() {
        this.current = $('#currency').val() || 'INR';
        this.rate = parseFloat($('#conversionrate').val()) || 1.0;
        console.log('Currency State Initialized:', this.current, 'Rate:', this.rate);
    },

    isForeign: function() {
        return this.current !== 'INR';
    },

    update: function(currency, rate) {
        this.current = currency;
        this.rate = parseFloat(rate) || 1.0;
        console.log('Currency State Updated:', this.current, 'Rate:', this.rate);
    },

    convertToForeign: function(inrPrice) {
        if (!inrPrice || isNaN(inrPrice) || inrPrice <= 0) return 0;
        if (!this.isForeign()) return parseFloat(inrPrice);

        var result = parseFloat(inrPrice) / this.rate;
        console.log('Convert:', inrPrice, 'INR →', result, this.current);
        return result;
    },

    convertToINR: function(foreignPrice) {
        if (!foreignPrice || isNaN(foreignPrice) || foreignPrice <= 0) return 0;
        if (!this.isForeign()) return parseFloat(foreignPrice);

        var result = parseFloat(foreignPrice) * this.rate;
        console.log('Convert:', foreignPrice, this.current, '→', result, 'INR');
        return result;
    },

    format: function(amount) {
        if (amount === null || amount === undefined || amount === '' || isNaN(amount)) {
            return '';
        }
        var num = parseFloat(amount);
        if (isNaN(num)) return '';
        return num.toFixed(this.decimalPlaces);
    }
};

// ============================================================================
// PRODUCT ROW DATA STORAGE
// ============================================================================

var ProductRows = {};

function storeProductData(rowNo, data) {
    ProductRows[rowNo] = {
        productId: data.productId,
        originalINRPrice: data.originalINRPrice,
        originalINRMRP: data.originalINRMRP,
        originalTax: data.originalTax,
        currentPrice: data.currentPrice,
        currentMRP: data.currentMRP
    };
    console.log('Stored product data for row', rowNo, ProductRows[rowNo]);
}

function getProductData(rowNo) {
    return ProductRows[rowNo] || null;
}

// ============================================================================
// PRICE CALCULATION FUNCTIONS
// ============================================================================

function calculateProductPrice(apiData, rowNo) {
    console.log('\n=== CALCULATING PRODUCT PRICE ===');
    console.log('API Data:', apiData);
    console.log('Row:', rowNo);
    console.log('Currency:', CurrencyState.current, 'Rate:', CurrencyState.rate);

    // Extract base values from API
    var purchasePrice = parseFloat(apiData.pt_purchaseprice) || 0;
    var mrp = parseFloat(apiData.pt_mrp || apiData.pd_mrp) || 0;
    var tax = parseFloat(apiData.tb_tax) || 0;
    var profitType = parseInt(apiData.pd_profittype) || 0;

    // Calculate retail price based on profit type
    var retailPrice = calculateRetailPrice(apiData);

    console.log('Base Values:', {
        purchasePrice: purchasePrice,
        mrp: mrp,
        tax: tax,
        profitType: profitType,
        retailPrice: retailPrice
    });

    // For foreign currency: NO TAX
    var finalTax = CurrencyState.isForeign() ? 0 : tax;
    var gstAmount = 0;
    var netPrice = retailPrice;
    var totalPrice = retailPrice;

    if (!CurrencyState.isForeign()) {
        // INR: Calculate with tax
        if (profitType == 3) {
            // Tax inclusive
            var gstMult = 100 + tax;
            netPrice = (retailPrice * 100) / gstMult;
            gstAmount = retailPrice - netPrice;
        } else {
            // Tax exclusive
            gstAmount = (netPrice * tax) / 100;
            totalPrice = netPrice + gstAmount;
        }
    }

    // Convert prices to foreign currency if needed
    var displayPurchase = CurrencyState.convertToForeign(purchasePrice);
    var displayMRP = CurrencyState.convertToForeign(mrp);
    var displayUnitPrice = CurrencyState.convertToForeign(totalPrice);
    var displayNetPrice = CurrencyState.convertToForeign(netPrice);

    console.log('Calculated Prices:', {
        netPrice: netPrice,
        gstAmount: gstAmount,
        totalPrice: totalPrice,
        displayUnitPrice: displayUnitPrice,
        displayNetPrice: displayNetPrice
    });

    return {
        // Original INR values (always store these)
        originalINRPrice: totalPrice,
        originalINRMRP: mrp,
        originalINRPurchase: purchasePrice,
        originalTax: tax,

        // Display values (converted if foreign currency)
        displayUnitPrice: displayUnitPrice,
        displayMRP: displayMRP,
        displayPurchase: displayPurchase,
        displayNetPrice: displayNetPrice,
        displayTax: finalTax,
        displayGSTAmount: CurrencyState.isForeign() ? 0 : gstAmount
    };
}

function calculateRetailPrice(apiData) {
    var purchasePrice = parseFloat(apiData.pt_purchaseprice) || 0;
    var profitType = parseInt(apiData.pd_profittype) || 0;
    var profit = 0;

    <?php if($type == 1 || $type == 4 || $type == 5) { ?>
        profit = parseFloat(apiData.pd_wholesaleprofit) || 0;
    <?php } else if($type == 7) { ?>
        profit = parseFloat(apiData.pd_csaleprofit) || 0;
    <?php } else if($type == 8) { ?>
        profit = parseFloat(apiData.pd_dsaleprofit) || 0;
    <?php } else { ?>
        profit = parseFloat(apiData.pd_retailprofit) || 0;
    <?php } ?>

    if (profitType == 1) {
        // Percentage profit
        return purchasePrice + (purchasePrice * profit / 100);
    } else if (profitType == 2) {
        // Fixed amount profit
        return purchasePrice + profit;
    } else {
        // Use MRP
        return parseFloat(apiData.pt_mrp || apiData.pd_mrp) || 0;
    }
}

// ============================================================================
// ROW UPDATE FUNCTIONS
// ============================================================================

function updateProductRow(rowNo, prices, apiData) {
    console.log('\n=== UPDATING ROW', rowNo, '===');

    // Set basic product info
    $('#productid' + rowNo).val(apiData.pd_productid);
    $('#stockid' + rowNo).val(apiData.pt_stockid);
    $('#productcode' + rowNo).val(apiData.pd_productcode);
    $('#productname' + rowNo).val(apiData.pd_productname + ' ' + (apiData.pd_size || '') + ' ' + (apiData.pd_brand || ''));

    // Set prices
    $('#unitprice' + rowNo).val(CurrencyState.format(prices.displayUnitPrice));
    $('#mrp' + rowNo).val(CurrencyState.format(prices.displayMRP));
    $('#purchaseprice' + rowNo).val(CurrencyState.format(prices.displayPurchase));
    $('#netprice' + rowNo).val(CurrencyState.format(prices.displayNetPrice));
    $('#gst' + rowNo).val(prices.displayTax);

    // Set quantities and discounts
    $('#qty' + rowNo).val(1);
    $('#discountper' + rowNo).val(0);
    $('#discountamnt' + rowNo).val(0);
    $('#cess' + rowNo).val(0);

    // Store original values
    storeProductData(rowNo, {
        productId: apiData.pd_productid,
        originalINRPrice: prices.originalINRPrice,
        originalINRMRP: prices.originalINRMRP,
        originalTax: prices.originalTax,
        currentPrice: prices.displayUnitPrice,
        currentMRP: prices.displayMRP
    });

    // Calculate item totals
    recalculateItemTotals(rowNo);

    console.log('Row', rowNo, 'updated successfully');
}

function recalculateItemTotals(rowNo) {
    var unitPrice = parseFloat($('#unitprice' + rowNo).val()) || 0;
    var qty = parseFloat($('#qty' + rowNo).val()) || 1;
    var discountPer = parseFloat($('#discountper' + rowNo).val()) || 0;
    var gst = parseFloat($('#gst' + rowNo).val()) || 0;

    // Calculate net price
    var netPrice = unitPrice;
    if (!CurrencyState.isForeign() && gst > 0) {
        netPrice = (unitPrice * 100) / (100 + gst);
    }

    // Calculate discount
    var discountAmt = (netPrice * discountPer) / 100;
    var discountedPrice = netPrice - discountAmt;

    // Calculate GST amount
    var gstAmt = 0;
    if (!CurrencyState.isForeign()) {
        gstAmt = (discountedPrice * gst) / 100;
    }

    // Calculate totals
    var totalNet = discountedPrice * qty;
    var totalGST = gstAmt * qty;
    var totalDiscount = discountAmt * qty;
    var grandTotal = totalNet + totalGST;

    // Update hidden fields
    $('#itemnetamt' + rowNo).val(CurrencyState.format(totalNet));
    $('#itemgstamt' + rowNo).val(CurrencyState.format(totalGST));
    $('#itemdiscountamt' + rowNo).val(CurrencyState.format(totalDiscount));
    $('#itemtotalamt' + rowNo).val(CurrencyState.format(grandTotal));

    // Update displays
    $('#netamt' + rowNo).html(CurrencyState.format(totalNet));
    $('#gstamt' + rowNo).html(CurrencyState.format(totalGST));
    $('#discountamt' + rowNo).html(CurrencyState.format(totalDiscount));
    $('#totalamt' + rowNo).html(CurrencyState.format(grandTotal));

    // Update grand total
    calculateGrandTotal();
}

function calculateGrandTotal() {
    var total = 0;
    var totalGST = 0;

    $('input[name^="itemtotalamt"]').each(function() {
        if ($(this).val()) {
            total += parseFloat($(this).val()) || 0;
        }
    });

    $('input[name^="itemgstamt"]').each(function() {
        if ($(this).val()) {
            totalGST += parseFloat($(this).val()) || 0;
        }
    });

    $('#totalgstamount').val(CurrencyState.format(totalGST));
    $('#grandtotal').val(CurrencyState.format(total));

    console.log('Grand Total:', total, 'GST:', totalGST);
}

// ============================================================================
// CURRENCY CHANGE HANDLER
// ============================================================================

function fetchExchangeRate(currency) {
    console.log('Fetching exchange rate for:', currency);

    if (currency === 'INR' || currency === '') {
        $('#conversionrate').val('1.000000');
        $('#rateinfo').text('1 INR = 1 INR');
        CurrencyState.update('INR', 1.0);
        updateAllRows();
        return;
    }

    // Show loading state
    $('#rateinfo').html('<i class="fa fa-spinner fa-spin"></i> Fetching rate...');

    $.ajax({
        url: '<?= base_url() ?>sale/getexchangerate',
        type: 'POST',
        dataType: 'JSON',
        data: { currency: currency },
        success: function(response) {
            console.log('Exchange rate response:', response);

            if (response.error) {
                $('#rateinfo').html('<span class="text-danger">API unavailable - Enter rate manually</span>');
                $('#conversionrate').focus();
                console.error('Exchange rate API error:', response.error);
            } else if (response.rate) {
                var rate = parseFloat(response.rate);
                $('#conversionrate').val(rate.toFixed(6));
                $('#rateinfo').html('1 ' + currency + ' = <strong>' + rate.toFixed(2) + '</strong> INR');

                CurrencyState.update(currency, rate);
                updateAllRows();
            } else {
                $('#rateinfo').html('<span class="text-danger">Invalid response from server</span>');
                console.error('Unexpected response:', response);
            }
        },
        error: function(xhr, status, error) {
            $('#rateinfo').html('<span class="text-danger">Error fetching rate</span>');
            console.error('AJAX Error:', error);
        }
    });
}

function handleCurrencyChange() {
    console.log('\n========== CURRENCY CHANGE ==========');

    var newCurrency = $('#currency').val();

    console.log('Currency changed to:', newCurrency);

    // Fetch new exchange rate
    fetchExchangeRate(newCurrency);
}

function updateAllRows() {
    console.log('Updating all rows for currency:', CurrencyState.current);

    // Update all existing rows
    for (var rowNo in ProductRows) {
        if (ProductRows.hasOwnProperty(rowNo)) {
            updateRowForNewCurrency(rowNo);
        }
    }

    console.log('========== CURRENCY UPDATE COMPLETE ==========\n');
}

function updateRowForNewCurrency(rowNo) {
    console.log('\nUpdating row', rowNo, 'for currency', CurrencyState.current);

    var data = getProductData(rowNo);
    if (!data) {
        console.log('No data for row', rowNo);
        return;
    }

    // Convert prices
    var newUnitPrice = CurrencyState.convertToForeign(data.originalINRPrice);
    var newMRP = CurrencyState.convertToForeign(data.originalINRMRP);
    var newTax = CurrencyState.isForeign() ? 0 : data.originalTax;

    // Update fields
    $('#unitprice' + rowNo).val(CurrencyState.format(newUnitPrice));
    $('#mrp' + rowNo).val(CurrencyState.format(newMRP));
    $('#gst' + rowNo).val(newTax);
    $('#cess' + rowNo).val(0);

    console.log('Updated prices:', {
        unitPrice: newUnitPrice,
        mrp: newMRP,
        tax: newTax
    });

    // Recalculate totals
    recalculateItemTotals(rowNo);
}

// ============================================================================
// PRODUCT SELECTION HANDLER
// ============================================================================

var itemno = <?= $itno ?>;
var sln = <?= $itno ?>;

function selectproductdet(prdid, stockid, no) {
    console.log('\n========== PRODUCT SELECTION ==========');
    console.log('Product:', prdid, 'Stock:', stockid, 'Row:', no);

    // Initialize currency state if not already
    if (!CurrencyState.current) {
        CurrencyState.init();
    }

    // Check for existing product
    var exists = false;
    $('input[name^="stockid"]').each(function() {
        if ($(this).val() == stockid) {
            exists = true;
            var existingRow = $(this).attr('id').substring(7);
            var qty = parseInt($('#qty' + existingRow).val()) || 0;
            $('#qty' + existingRow).val(qty + 1);
            recalculateItemTotals(existingRow);
            console.log('Product already exists in row', existingRow, 'increased quantity');
        }
    });

    if (exists) {
        $('#productcode' + no).val('');
        $('#productname' + no).val('');
        $('.tabledropdowndivstyle').hide();
        return;
    }

    // Fetch product details
    $('.tabledropdowndivstyle').hide();

    $.ajax({
        url: "<?= base_url() ?>sale/getproductdetails",
        type: 'POST',
        dataType: 'JSON',
        data: {prodid: prdid, stockid: stockid}
    })
    .done(function(result) {
        console.log('Product data received:', result);

        // Calculate prices
        var prices = calculateProductPrice(result, no);

        // Update row
        updateProductRow(no, prices, result);

        // Add new empty row
        addmoreitem();

        console.log('========== PRODUCT SELECTION COMPLETE ==========\n');
    })
    .fail(function(xhr, status, error) {
        console.error('Failed to fetch product:', error);
    });
}

// ============================================================================
// INITIALIZATION
// ============================================================================

$(document).ready(function() {
    console.log('========== BILLING MODULE INITIALIZED ==========');

    // Initialize currency state
    CurrencyState.init();

    // Attach currency change handler
    $('#currency').on('change', function() {
        handleCurrencyChange();
    });

    // Manual rate change (for testing/override)
    $('#conversionrate').on('change', function() {
        var currency = $('#currency').val();
        var rate = parseFloat($(this).val()) || 1.0;

        console.log('Manual rate change:', currency, rate);
        CurrencyState.update(currency, rate);
        updateAllRows();
    });

    console.log('Currency module ready');
});

</script>
