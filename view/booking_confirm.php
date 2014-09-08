
<p class="alert alert-info">
    You will now be passed to our secure payment partner. Please check your booking details
    carefully before pressing 'Pay now'.
</p>

<div class="santa-confirm">
    <dl class="dl-horizontal">
        <dt>Title &amp; Name</dt><dd><?php echo $br->getTitle().' '.$br->getFirstname().' '.$br->getLastname(); ?>
        <dt>Address</dt>
        <dd>
            <address class="santa-address">
            <?php echo $br->getAddress1(); ?><br />
            <?php if ($br->getAddress2()) { echo $br->getAddress2() . '<br />'; } ?>
            <?php echo $br->getCity(); ?><br />
            <?php if ($br->getCounty()) { echo $br->getCounty() . '<br />'; } ?>
            <?php echo $br->getPostcode(); ?><br />
            </address>
        </dd>
        <dt>Email</dt><dd><?php echo $br->getEmail(); ?>
        <dt>Phone</dt><dd><?php echo $br->getPhone(); ?>
        <dt>Number of adults</dt>
        <dd><?php echo $br->getAdults() . ' at &pound;' . number_format($fares->adult/100,2) . ' each'; ?></dd>
        <dt>Number of children</dt>
        <dd><?php echo $br->getChildren() . ' at &pound;' . number_format($fares->child/100,2) . ' each'; ?></dd>
        <dt>Number of infants</dt><dd><?php echo $br->getInfants(); ?>
        <dt>&nbsp;</dt><dd></dd>
        <dt>&nbsp;</dt><dd><b>Total price to pay &pound;<?php echo number_format($price_total, 2);?></b></dd>
    </dl>
</div>

<p class="alert alert-warning">
    Note: payments are made to <b>"SRPS Railtours</b>", the trading subsidiary of the <b>SRPS</b>.<br />
    By clicking <b>Pay now</b> you agree to our 
    <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#santa-terms">Terms & Conditions</button>
</p>

<form action="<?php echo $CFG->sage_url; ?>" method="post">
    <input type="hidden" name="VPSProtocol" value="2.23" />
    <input type="hidden" name="TxType" value="PAYMENT" />
    <input type="hidden" name="Vendor" value="<?php echo $CFG->sage_vendor; ?>" />
    <input type="hidden" name="Crypt" value="<?php echo $crypt; ?>" />
    <dl class="dl-horizontal">
        <dt>&nbsp;</dt>
        <dd><button type="submit" class="btn btn-primary">Pay now</button></dd>
    </dl>
</form>


<!-- Modal -->
<div class="modal fade" id="santa-terms" tabindex="-1" role="dialog" aria-labelledby="santaTermsLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="santaTermsLabel">Terms and Conditions</h4>
      </div>
      <div class="modal-body">
          <?php include('http://www.srps.org.uk/srps/santa-conditions.inc.php'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>






