
<p class="alert alert-info">
    You will now be passed to our secure payment partner. Please check your booking details
    carefully before pressing 'Pay now'.
</p>

<div class="santa-confirm">
    <dl class="dl-horizontal">
        <dt>Title & Name</dt><dd><?php echo $br->getTitle().' '.$br->getFirstname().' '.$br->getLastname(); ?>
        <dt>Address</dt>
        <dd>
            <address class="santa-address">
            <?php echo $br->getAddress1(); ?><br />
            <?php if ($br->getAddress2()) { echo $br->getAddress2() . '<br />'; } ?>
            <?php echo $br->getCity(); ?><br />
            <?php echo $br->getPostcode(); ?><br />
            <?php echo $country; ?>
            </address>
        </dd>
        <dt>Email</dt><dd><?php echo $br->getEmail(); ?>
        <dt>Phone</dt><dd><?php echo $br->getPhone(); ?>
        <dt>Number of adults</dt>
        <dd><?php echo $br->getAdults() . ' at £' . number_format($fares->adult/100,2) . ' each'; ?></dd>
        <dt>Number of children</dt>
        <dd><?php echo $br->getChildren() . ' at £' . number_format($fares->child/100,2) . ' each'; ?></dd>
        <dt>Number of infants</dt><dd><?php echo $br->getInfants(); ?>
        <dt>&nbsp;</dt><dd></dd>
        <dt>&nbsp;</dt><dd><b>Total price to pay £<?php echo number_format($price_total, 2);?></b></dd>
    </dl>
</div>

<form action="<?php echo $CFG->sage_url; ?>" method="post">
    <input type="hidden" name="VPSProtocol" value="2.23" />
    <input type="hidden" name="TxType" value="PAYMENT" />
    <input type="hidden" name="Vendor" value="<?php echo $CFG->sage_vendor; ?>" />
    <input type="hidden" name="Crypt" value="<?php echo $crypt; ?>" />
    <button type="submit" class="btn btn-default">Pay now</button>
</form>






