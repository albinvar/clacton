<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <link rel="icon" href="<?= assets_url() ?>assets/img/favicon.png" type="image/x-icon"/>
 <link rel="stylesheet" type="text/css" href="<?= assets_url() ?>components/css/reportstyle.css">
 <style type="text/css">

   .page-footer, .page-footer-space {
    height: 0px;
  }
  .page-footer {
    position: fixed;
    bottom: 0;
    width: 97%;
    /*background: yellow; /* for demo */
  }

  .page-header {
    height: 250px;
    width: 98%;
    margin-left: 5px;
  }
  .page-header-space{
    height: 250px;
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
  @page
   {
    size: A4;
  }
  @media print {
    html, body {
        height: 98%;    
    }
}
 </style>

</head>

<body style="-webkit-print-color-adjust: exact;">



<div class="page-header">

    <?php 
if($newprint == 1)
{
    if($type == 1)
    {
        
        ?>
        <a href="<?= base_url() ?>purchase/purchasehistory/1"><button class="printButtonClass">Back</button></a>
        <?php
    }else{
        ?>
        <a href="<?= base_url() ?>purchase/purchasehistory"><button class="printButtonClass">Back</button></a>
        <?php
    }
}
?>

    <div style="width: 97%">
    <table border="0" width="98%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="20%" class="logo-container">
                <?php
                if(isset($businessdet[0]->bu_logo) && $businessdet[0]->bu_logo != "")
                {
                    ?>
                <img src="<?= base_url() ?>uploads/business/<?= $businessdet[0]->bu_logo ?>" alt="Company Logo">
                <?php
                }
                ?>
            </td>
            <td align="center"><u><?= $title ?></u></td>
            <td width="20%" class="logo-container" align="right">
                <?php
                if(isset($businessdet[0]->bu_franchiselogo) && $businessdet[0]->bu_franchiselogo != "")
                {
                    ?>
                <img src="<?= base_url() ?>uploads/business/<?= $businessdet[0]->bu_franchiselogo ?>" alt="Franchise Logo" style="margin-left: auto;">
                <?php
                }
                ?>
            </td>
        </tr>
    </table>

    <table border="0" width="98%" cellpadding="0" cellspacing="0" style="margin-top: 5px;">
        <tr>
            <td>Bill No: <?= $purchasedet->pm_purchaseprefix ?><?= $purchasedet->pm_purchaseno ?></td>
            <td align="right">DATE: <?php echo date('d/m/Y', strtotime($purchasedet->pm_date)) ?> <?php echo date('H:i', strtotime($purchasedet->pm_time)) ?></td>
        </tr>
    </table>
    <table border="1" width="98%" style="border-collapse: collapse; margin-top: 5px;" cellpadding="5" cellspacing="0">
      <tbody>
        <tr valign="top">
            <td width="50%" align="left" valign="top">
                <h4 style="margin: 0px;"><?= $businessdet[0]->bu_unitname ?> </h4>
                <?= $businessdet[0]->bu_address ?><br/>
                Ph: <?= $businessdet[0]->bu_phone ?><br/>
                Email: <?= $businessdet[0]->bu_email ?>
            </td>
            <td>
               <h4 style="margin: 0px;"> <?php 
                echo $purchasedet->sp_name;
                ?></h4>
                <?= $purchasedet->sp_contactperson ?>, 
                <?= $purchasedet->sp_address ?><br/>
                Ph: <?= $purchasedet->sp_mobile ?><br/>
                <!--Email: <?= $purchasedet->sp_email ?> <br/>-->
                <?= $this->isvatgstname ?> No : <?= $purchasedet->sp_gstno ?>
            </td>
        </tr>
    </tbody>
    </table>

    <table width="100%" style="margin-top: 5px;">
        <tr>
            <td width="50%">Invoice No: <?= $purchasedet->pm_invoiceno ?></td>
            <!-- <td>Vehicle No: <?= $purchasedet->pm_vehicleno ?></td> -->
        </tr>
    </table>
    
    </div>
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

          <div class="pagesection" >


<table border="1" width="100%" style="border-collapse: collapse;" cellpadding="5" cellspacing="0">
    <thead class="bg-gray-300">
        <tr>
            <th class="wd-5p-f" rowspan="2">Sl No</th>
            <th rowspan="2" align="left">Item Name</th>
            <th rowspan="2">Unit Price</th>
            <th rowspan="2">Total Qty</th>
            <?php
            if($this->isvatgst == 1) {
                // VAT Business - always show VAT
            ?>
            <th colspan="2">VAT</th>
            <?php
            } else {
                // GST Business - check state
                if(($purchasedet->sp_state == $businessdet[0]->bu_state || $purchasedet->sp_state == '') || $businessdet[0]->bu_country != '101')
                {
            ?>
            <th colspan="2">CGST</th>
            <th colspan="2">SGST</th>
            <?php
                }else{
            ?>
            <th colspan="2">IGST</th>
            <?php
                }
            }
            ?>
            <th rowspan="2">Discount</th>
            <th rowspan="2">Total Price</th>
        </tr>
        <tr>
            <?php
            if($this->isvatgst == 1) {
                // VAT Business
            ?>
            <th>%</th>
            <th>Amt</th>
            <?php
            } else {
                // GST Business
                if(($purchasedet->sp_state == $businessdet[0]->bu_state || $purchasedet->sp_state == '') || $businessdet[0]->bu_country != '101')
                {
            ?>
            <th>%</th>
            <th>Amt</th>
            <th>%</th>
            <th>Amt</th>
            <?php
                }else{
            ?>
            <th>%</th>
            <th>Amt</th>
            <?php
                }
            }
            ?>
        </tr>

    </thead>
    <?php
    $kn=1;
    $totaltax = 0;
    $totalcgst = 0;
    $totalsgst = 0;
    if (!empty($purchaseprodcts)) {
        foreach ($purchaseprodcts as $prvl) {
            $totaltax += $prvl->ps_totalgst;
            ?>
            <tr>
                <td align="center"><?= $kn ?></td>

                <td><?php echo $prvl->pd_productname . ' ' . $prvl->pd_productcode; ?></td>
                <td align="center"><?php echo $prvl->ps_purchaseprice ?></td>
                <td align="center"><?php echo $prvl->ps_qty ?></td>
                <?php
                if($this->isvatgst == 1) {
                    // VAT Business
                    $colspnno = '7';
                ?>
                <td align="center"><?php echo $prvl->ps_gstpercent ?></td>
                <td align="center"><?php echo $prvl->ps_totalgst ?></td>
                <?php
                } else {
                    // GST Business
                    if(($purchasedet->sp_state == $businessdet[0]->bu_state || $purchasedet->sp_state == '') || $businessdet[0]->bu_country != '101')
                    {
                        $colspnno = '9';
                        $totalcgst += $prvl->ps_totalgst/2;
                        $totalsgst += $prvl->ps_totalgst/2;
                ?>
                <td align="center"><?php echo $prvl->ps_gstpercent/2 ?></td>
                <td align="center"><?php echo $prvl->ps_totalgst/2 ?></td>
                <td align="center"><?php echo $prvl->ps_gstpercent/2 ?></td>
                <td align="center"><?php echo $prvl->ps_totalgst/2 ?></td>
                <?php
                    }else{
                        $colspnno = '7';
                ?>
                <td align="center"><?php echo $prvl->ps_gstpercent ?></td>
                <td align="center"><?php echo $prvl->ps_totalgst ?></td>
                <?php
                    }
                }
                ?>
                <td align="center"><?php echo $prvl->ps_discountamnt * $prvl->ps_qty; ?></td>
                <td align="center"><?php echo $prvl->ps_totalamount ?></td>
        </tr>
        <?php
        $kn++;
    }
}
?>
    <tr>
        <td colspan="4" align="right"><b>Total</b></td>
        <?php
        if($this->isvatgst == 1) {
            // VAT Business
        ?>
        <td align="center"></td>
        <td align="center"><b><?php echo number_format($totaltax, 2) ?></b></td>
        <?php
        } else {
            // GST Business
            if(($purchasedet->sp_state == $businessdet[0]->bu_state || $purchasedet->sp_state == '') || $businessdet[0]->bu_country != '101')
            {
        ?>
        <td align="center"></td>
        <td align="center"><b><?php echo number_format($totalcgst, 2) ?></b></td>
        <td align="center"></td>
        <td align="center"><b><?php echo number_format($totalsgst, 2) ?></b></td>
        <?php
            }else{
        ?>
        <td align="center"></td>
        <td align="center"><b><?php echo number_format($totaltax, 2) ?></b></td>
        <?php
            }
        }
        ?>
        <td align="center"></td>
        <th><?= $purchasedet->pm_totalamount ?></th>
    </tr>
    <?php 
    if($type != 1)
    {
    ?>
    <tr>
        <td colspan="<?= $colspnno ?>" align="right">Discount</td>
        <th><?= $purchasedet->pm_discount ?></th>
    </tr>
    <tr>
        <td colspan="<?= $colspnno ?>" align="right">Packing and Forwarding</td>
        <th><?= $purchasedet->pm_freight ?></th>
    </tr>
    <?php 
    }
    ?>
    <tr>
        <td colspan="<?= $colspnno ?>" align="right">Round Off</td>
        <th><?= $purchasedet->pm_roundoffvalue ?></th>
    </tr>
    <tr>
        <td colspan="<?= $colspnno ?>" align="right">Grand Total</td>
        <th><?= $purchasedet->pm_grandtotal ?></th>
    </tr>
    <!--<tr>
        <td colspan="<?= $colspnno ?>" align="right">Old Balance</td>
        <th><?= $purchasedet->pm_oldbalance ?></th>
    </tr>
    <tr>
        <td colspan="<?= $colspnno ?>" align="right">Paid Amount</td>
        <th><?= $purchasedet->pm_paidamount ?></th>
    </tr>
    <tr>
        <td colspan="<?= $colspnno ?>" align="right">Balance Amount</td>
        <th><?= $purchasedet->pm_balanceamount ?></th>
    </tr>-->
    <tr>
        <td colspan="10" align="right">Grand Total(in words): <b>Rs <?php echo convert_numbertowords($purchasedet->pm_grandtotal); ?> Only</b></td>
    </tr>
    <tr>
        <td colspan="10" align="left">Purchase Note: <b><?= $purchasedet->pm_purchasenote ?></b></td>
    </tr>
</table>

</div>
</td>
</tr>
</tbody>

<script>
 window.print();
</script>
</body>

</html>