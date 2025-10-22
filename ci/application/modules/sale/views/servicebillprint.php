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
    height: 170px;
    width: 98%;
    margin-left: 5px;
  }
  .page-header-space{
    height: 170px;
  }


 </style>

 <?php 
  if($billedet->sb_pagesize == 2)
  {
  ?>
  <style type="text/css">
  @page
   {
    size: A5;
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
<a href="<?= base_url() ?>sale/servicebillhistory"><button class="printButtonClass">Back</button></a>
<?php 
}
?>

<div class="page-header">
    <div style="width: 96%; margin-top: 20px;">
    <table border="0" width="98%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center"><u>Service Bill</u></td>
        </tr>
    </table>

    <table border="0" width="98%" cellpadding="0" cellspacing="0">
        <tr>
            <td>Bill No: <?= $billedet->sb_billno ?></td>
            <td align="right">DATE: <?php echo date('d/m/Y', strtotime($billedet->sb_date)) ?> <?php echo date('H:i', strtotime($billedet->sb_time)) ?></td>
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
                echo $billedet->sb_customername;
                ?></h4>
                <?= $billedet->sb_place ?><br/>
                Ph: <?= $billedet->sb_phone ?><br/>
                GSTIN : <?= $billedet->sb_customergst ?>
                
            </td>
        </tr>
    </tbody>
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

          <div class="page" >


<table border="1" width="100%" style="border-collapse: collapse;" cellpadding="5" cellspacing="0">
    <thead class="bg-gray-300">
        <tr>
            <th class="wd-5p-f">Sl No</th>
            <th align="left">Product Name</th>
            <th align="left">Complaint</th>
            <th>Total Price</th>
        </tr>
        
    </thead>
    <?php
    $kn=1;
    if (!empty($billprodcts)) {
        foreach ($billprodcts as $prvl) {
            ?>
            <tr>
                <td align="center"><?= $kn ?></td>

                <td><?php echo $prvl->sbs_productname; ?></td>
                <td><?php echo $prvl->sbs_complaint ?></td>
                
                <td align="center"><?php echo $prvl->sbs_price ?></td>
        </tr>
        <?php
        $kn++;
    }
}
?>
    <tr>
        <td colspan="3" align="right">Total</td>
        <th><?= $billedet->sb_totalamount ?></th>
    </tr>
    <tr>
        <td colspan="3" align="right">Freight</td>
        <th><?= $billedet->sb_freight ?></th>
    </tr>
    
    <tr>
        <td colspan="3" align="right">Grand Total</td>
        <th><?= $billedet->sb_grandtotal ?></th>
    </tr>
    
    <tr>
        <td colspan="10" align="right">Grand Total(in words): <b>Rs <?php echo convert_numbertowords($billedet->sb_grandtotal); ?> Only</b></td>
    </tr>
    <tr>
        <td colspan="10" align="left">Payment Method: <b><?php 
        switch($billedet->sb_paymethod)
        {
            case 1: 
                echo "Cash";
                break;
            case 2: 
                echo "Bank";
                break;
            case 3: 
                echo "UPI";
                break;
        }
        ?></b></td>
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