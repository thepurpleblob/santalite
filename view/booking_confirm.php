
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
            <?php echo $br->getPostcode(); ?><br />
            <?php echo $br->getCounty(); ?>
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
          <h1>Lorem ipsum dolor sit amet consectetuer adipiscing 
elit</h1>


<p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
elit. Aenean commodo ligula eget dolor. Aenean massa 
<strong>strong</strong>. Cum sociis natoque penatibus 
et magnis dis parturient montes, nascetur ridiculus 
mus. Donec quam felis, ultricies nec, pellentesque 
eu, pretium quis, sem. Nulla consequat massa quis 
enim. Donec pede justo, fringilla vel, aliquet nec, 
vulputate eget, arcu. In enim justo, rhoncus ut, 
imperdiet a, venenatis vitae, justo. Nullam dictum 
felis eu pede <a class="external ext" href="#">link</a> 
mollis pretium. Integer tincidunt. Cras dapibus. 
Vivamus elementum semper nisi. Aenean vulputate 
eleifend tellus. Aenean leo ligula, porttitor eu, 
consequat vitae, eleifend ac, enim. Aliquam lorem ante, 
dapibus in, viverra quis, feugiat a, tellus. Phasellus 
viverra nulla ut metus varius laoreet. Quisque rutrum. 
Aenean imperdiet. Etiam ultricies nisi vel augue. 
Curabitur ullamcorper ultricies nisi.</p>


<h1>Lorem ipsum dolor sit amet consectetuer adipiscing 
elit</h1>


<h2>Aenean commodo ligula eget dolor aenean massa</h2>


<p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
elit. Aenean commodo ligula eget dolor. Aenean massa. 
Cum sociis natoque penatibus et magnis dis parturient 
montes, nascetur ridiculus mus. Donec quam felis, 
ultricies nec, pellentesque eu, pretium quis, sem.</p>


<h2>Aenean commodo ligula eget dolor aenean massa</h2>


<p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
elit. Aenean commodo ligula eget dolor. Aenean massa. 
Cum sociis natoque penatibus et magnis dis parturient 
montes, nascetur ridiculus mus. Donec quam felis, 
ultricies nec, pellentesque eu, pretium quis, sem.</p>


<ul>
  <li>Lorem ipsum dolor sit amet consectetuer.</li>
  <li>Aenean commodo ligula eget dolor.</li>
  <li>Aenean massa cum sociis natoque penatibus.</li>
</ul>


<p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
elit. Aenean commodo ligula eget dolor. Aenean massa. 
Cum sociis natoque penatibus et magnis dis parturient 
montes, nascetur ridiculus mus. Donec quam felis, 
ultricies nec, pellentesque eu, pretium quis, sem.</p>



<p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
elit. Aenean commodo ligula eget dolor. Aenean massa. 
Cum sociis natoque penatibus et magnis dis parturient 
montes, nascetur ridiculus mus. Donec quam felis, 
ultricies nec, pellentesque eu, pretium quis, sem.</p>


<p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
elit. Aenean commodo ligula eget dolor. Aenean massa. 
Cum sociis natoque penatibus et magnis dis parturient 
montes, nascetur ridiculus mus. Donec quam felis, 
ultricies nec, pellentesque eu, pretium quis, sem.</p>
          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>






