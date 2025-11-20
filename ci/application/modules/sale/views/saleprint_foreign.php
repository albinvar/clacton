<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <link rel="icon" href="<?= assets_url() ?>assets/img/favicon.png" type="image/x-icon"/>
 <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>components/css/reportstyle.css">
 <style type="text/css">


  .page-footer-space {
    height: 35px;
    padding: 0px;
    margin-bottom: 0px;
  }
  .page-footer {
    height: 30px;
    position: fixed;
    bottom: 0;
    width: 97%;
    border-top: 0px;
    padding: 0px;
    margin-bottom: 0px;
  }

  .page-header {
    height: 295px;
    width: 98%;
    margin-left: 5px;
    margin-top: 5px;
  }
  .page-header-space{
    height: 295px;
  }
  .billitemstr{
    font-size: 13px;
    min-height: 30px;
  }

  .emtycellstyle{
    border-bottom: 0px;
    border-top: 0px;
    height: 28px;
  }

  .logo-container {
    width: 20%;
    max-width: 150px;
    height: 80px;
    display: inline-block;
    vertical-align: top;
  }
  .logo-container img {
    max-width: 100%;
    max-height: 80px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
  }

  .currency-info {
    background-color: #f0f8ff;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #4682b4;
    font-weight: bold;
    color: #00008b;
  }

  @page {
    margin: 0px;
    padding: 0px;
  }
 </style>

 <?php
  if($purchasedet->rb_pagesize == 2)
  {
  ?>
  <style type="text/css">
  @page
   {
    size: A5;
  }
  </style>
  <?php
}else{
 ?>
 <style type="text/css">
  @page
   {
    size: A4;
  }
  </style>
<?php
}
 ?>
</head>

<body style="-webkit-print-color-adjust: exact;">

<?php
if($newprint == 1)
{
?>
<a href="<?= base_url() ?>sale/salehistory/<?= $type ?>"><button class="printButtonClass">Back</button></a>
<?php
}
?>

<?php
// Get currency information
$this->load->helper('currency');
$currencies = get_currencies();
$currency_code = isset($purchasedet->rb_currency) ? $purchasedet->rb_currency : 'INR';
$currency_info = isset($currencies[$currency_code]) ? $currencies[$currency_code] : null;
$currency_symbol = $currency_info ? $currency_info['symbol'] : $currency_code;
$currency_name = $currency_info ? $currency_info['name'] : $currency_code;
$conversion_rate = isset($purchasedet->rb_conversionrate) ? $purchasedet->rb_conversionrate : 1;

// Get country information
$country_name = '';
if(isset($purchasedet->rb_country) && $purchasedet->rb_country) {
    $this->load->model('Country_model', 'cuntry');
    $country_data = $this->cuntry->get($purchasedet->rb_country);
    if($country_data) {
        $country_name = $country_data->name;
    }
}
?>

<div class="page-header">
    <div style="width: 97%">
    <table border="0" width="98%" cellpadding="0" cellspacing="0">

        <tr>
            <td width="20%" class="logo-container">
                <?php
                if($this->bulogo != "")
                {
                    ?>
                <img src="<?= base_url() ?>uploads/business/<?= $this->bulogo ?>" alt="Company Logo">
                <?php
                }
                ?>
            </td>
            <td align="center"><u>
                <?php
                if($type == 2){
                    echo "INTERNATIONAL PROFORMA INVOICE";
                }else{
                    echo "INTERNATIONAL INVOICE";
                }
                ?>
            </u></td>
            <td width="20%" class="logo-container" align="right">
                <?php
                if($businessdet->bu_franchiselogo != "")
                {
                    ?>
                <img src="<?= base_url() ?>uploads/business/<?= $businessdet->bu_franchiselogo ?>" alt="Franchise Logo" style="margin-left: auto;">
                <?php
                }
                ?>
            </td>
        </tr>
    </table>

    <table border="0" width="98%" cellpadding="0" cellspacing="0" style="margin-top: 5px;">
        <tr>
            <td>Invoice No: <?= $purchasedet->rb_billprefix ?><?= $purchasedet->rb_billno ?></td>
            <td align="right">DATE: <?php echo date('d/m/Y', strtotime($purchasedet->rb_date)) ?> <?php echo date('H:i', strtotime($purchasedet->rb_time)) ?></td>
        </tr>
    </table>

    <!-- Currency Information Bar -->
    <div class="currency-info">
        Currency: <?= $currency_name ?> (<?= $currency_symbol ?>)
        <?php if($country_name): ?>
         | Country: <?= $country_name ?>
        <?php endif; ?>
         | Exchange Rate: 1 <?= $currency_code ?> = <?= number_format($conversion_rate, 6) ?> INR
    </div>

    <table border="1" width="98%" style="border-collapse: collapse; margin-top: 5px;" cellpadding="5" cellspacing="0">
      <tbody>
        <tr valign="top">
            <td width="50%" align="left" valign="top">
                <h4 style="margin: 0px;"><?= $businessdet->bu_unitname ?> </h4>
                <?= $businessdet->bu_address ?><br/>
                <?php
                $phnarr = array();
                if($businessdet->bu_phone != "")
                {
                    $phnarr[] = $businessdet->bu_phone;
                }
                if($businessdet->bu_mobile != "")
                {
                    $phnarr[] = $businessdet->bu_mobile;
                }
                ?>
                Ph: <?= implode(', ', $phnarr) ?><br/>
                Email: <?= $businessdet->bu_email ?><br/>
                Registration No: <?= $businessdet->bu_gstnumber ?>
            </td>
            <td>
                <?php
                if($purchasedet->rb_existcustomer == 1)
                {
                    $custdet = $this->cstmr->getcustomerdetailsbyid($purchasedet->rb_customerid);

                    // Get customer country name
                    $cust_country_name = '';
                    if($custdet->ct_country) {
                        $cust_country_data = $this->cuntry->get($custdet->ct_country);
                        if($cust_country_data) {
                            $cust_country_name = $cust_country_data->name;
                        }
                    }
                    ?>
                    <h4 style="margin: 0px;"> <?php
                    echo $custdet->ct_name;
                    ?></h4>
                    <?= $custdet->ct_address ?><br/>
                    <?php if($cust_country_name): ?>
                    Country: <?= $cust_country_name ?><br/>
                    <?php endif; ?>
                    Ph: <?= $custdet->ct_phone ?>
                    <?php
                }else{
                ?>
               <h4 style="margin: 0px;"> <?php
                echo $purchasedet->rb_customername;
                ?></h4>
                <?= $purchasedet->rb_address ?><br/>
                Ph: <?= $purchasedet->rb_phone ?>
                <?php
                }
                ?>
            </td>
        </tr>
    </tbody>
    </table>

    <table width="100%" style="margin-top: 5px; margin-bottom: 25px;">
        <tr>
            <td width="50%">Sale Person: <?= isset($purchasedet->at_name) ? $purchasedet->at_name : '' ?></td>
        </tr>
    </table>

    </div>
</div>

<div class="page-footer">
    <div align="center" style="font-size: 12px; "><i>This is a computer generated invoice</i></div>
</div>


<table width="100%">

    <thead>
      <tr>
        <td>
          <!--place holder for the fixed-position header-->
          <div class="page-header-space"></div>
        </td>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>
          <!--*** CONTENT GOES HERE ***-->

          <div class="page" >


<table border="1" width="100%" style="border-collapse: collapse;" cellpadding="5" cellspacing="0">
    <thead class="bg-gray-300">
        <tr>
            <th class="wd-5p-f">Sl No</th>
            <th align="left">Item Name</th>
            <th>Unit Price<br/>(<?= $currency_symbol ?>)</th>
            <th>Qty</th>
            <th>Discount<br/>(<?= $currency_symbol ?>)</th>
            <th>Total Price<br/>(<?= $currency_symbol ?>)</th>
        </tr>
    </thead>
    <?php
    $kn=1;
    if (!empty($purchaseprodcts)) {
        foreach ($purchaseprodcts as $prvl) {
            // Convert amounts to foreign currency
            $unit_price_converted = $prvl->rbs_netamount / $conversion_rate;
            $discount_converted = $prvl->rbs_totaldiscount / $conversion_rate;
            $total_converted = $prvl->rbs_nettotal / $conversion_rate; // Use net total (without tax)
            ?>
            <tr class="billitemstr">
                <td align="center"><?= $kn ?></td>

                <td><?php echo $prvl->pd_productname . ' ' . $prvl->pd_productcode; ?>
                <?php
                if($remarkfield == 1 && $prvl->rbs_remarks != "")
                {
                   echo '- ' . $prvl->rbs_remarks;
                }
                ?>
                </td>
                <td align="center"><?= number_format($unit_price_converted, $this->decimalpoints) ?></td>
                <td align="center"><?php echo $prvl->rbs_qty ?></td>
                <td align="center"><?= number_format($discount_converted, $this->decimalpoints) ?></td>
                <td align="center"><?= number_format($total_converted, $this->decimalpoints) ?></td>
        </tr>
        <?php
        $kn++;
    }
}
// Reduce empty rows to prevent page breaks - only add 2 empty rows max
$emptycell = min(2, max(0, 5-$kn));
for($n=0; $n<$emptycell; $n++)
{
    ?>
    <tr>
        <td class="emtycellstyle"></td>
        <td class="emtycellstyle"></td>
        <td class="emtycellstyle"></td>
        <td class="emtycellstyle"></td>
        <td class="emtycellstyle"></td>
        <td class="emtycellstyle"></td>
    </tr>
    <?php
}

// Calculate converted totals
$total_amount_converted = $purchasedet->rb_totalamount / $conversion_rate;
$discount_converted_total = $purchasedet->rb_discount / $conversion_rate;
$old_balance_converted = $purchasedet->rb_oldbalance / $conversion_rate;
$grand_total_converted = $purchasedet->rb_grandtotal / $conversion_rate;
$paid_amount_converted = $purchasedet->rb_paidamount / $conversion_rate;
$balance_amount_converted = $purchasedet->rb_balanceamount / $conversion_rate;
?>
    <tr>
        <td colspan="5" align="right"><b>Total</b></td>
        <th><?= $currency_symbol ?> <?= number_format($total_amount_converted, $this->decimalpoints) ?></th>
    </tr>

    <tr>
        <td rowspan="5" colspan="3">
            Company's Bank Details<br/>
            Bank: <b><?= $businessdet->bu_bankname ?></b><br/>
            Acc No: <b><?= $businessdet->bu_accountnumber ?></b><br/>
            IFSC/Swift: <b><?= $businessdet->bu_ifsccode ?></b><br/>
            Branch: <b><?= $businessdet->bu_bankbranch ?></b>
        </td>
        <td colspan="2" align="right">Discount</td>
        <th><?= $currency_symbol ?> <?= number_format($discount_converted_total, $this->decimalpoints) ?></th>
    </tr>
    <tr>
        <td colspan="2" align="right">Old Balance</td>
        <th><?= $currency_symbol ?> <?= number_format($old_balance_converted, $this->decimalpoints) ?></th>
    </tr>
    <tr>
        <td colspan="2" align="right">Grand Total</td>
        <th><?= $currency_symbol ?> <?= number_format($grand_total_converted, $this->decimalpoints) ?></th>
    </tr>
    <tr>
        <td colspan="2" align="right">Paid Amount</td>
        <th><?= $currency_symbol ?> <?= number_format($paid_amount_converted, $this->decimalpoints) ?></th>
    </tr>
    <tr>
        <td colspan="2" align="right">Balance Amount</td>
        <th><?= $currency_symbol ?> <?= number_format($balance_amount_converted, $this->decimalpoints) ?></th>
    </tr>
    <tr>
        <td colspan="6" align="right">Grand Total(in words): <b><?= $currency_symbol ?> <?php echo convert_numbertowords($grand_total_converted); ?> Only</b></td>
    </tr>
    <tr>
        <td colspan="6" align="left">Payment Method: <b><?php
        switch($purchasedet->rb_paymentmethod)
        {
            case 4:
                echo "Cash";
                break;
            case 3:
                echo "Bank";
                break;
        }
        ?></b></td>
    </tr>
    <tr>
        <td colspan="6" style="background-color: #fffacd; padding: 10px;">
            <b>Exchange Rate Information:</b><br/>
            All amounts in this invoice are in <b><?= $currency_name ?> (<?= $currency_code ?>)</b>.<br/>
            Conversion Rate: 1 <?= $currency_code ?> = <?= number_format($conversion_rate, 6) ?> INR (Indian Rupees)<br/>
            Grand Total in INR: ₹<?= number_format($purchasedet->rb_grandtotal, 2) ?>
        </td>
    </tr>
</table>

<table border="1" width="100%" style="border-collapse: collapse;" cellpadding="5" cellspacing="0">
    <tr>
        <td width="60%">
            <span style="text-decoration: underline;">Declaration</span><br/>
            We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.<br/>
            <b>Note:</b> This invoice is for international billing and does not include local taxes (GST/VAT).
        </td>
        <td align="right">
            for <b><?= $businessdet->bu_unitname ?></b>
            <br/>
            <?php
                if($businessdet->bu_companyseal != "")
                {
                    ?>
                <img src="<?= base_url() ?>uploads/business/<?= $businessdet->bu_companyseal ?>" width="50px"  alt="user-image">
                <?php
                }
                ?>
            <br/>
            Authorised Signatory
        </td>
    </tr>
</table>



</div>
</td>
</tr>
</tbody>

    <tfoot style="margin: 0px;">
      <tr>
        <td>
          <!--place holder for the fixed-position footer-->
          <div class="page-footer-space">

          </div>
        </td>
      </tr>
    </tfoot>
</table>

<script>
 window.print();
</script>
</body>
</html>
