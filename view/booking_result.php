<h2>Booking complete</h2>

<?php if ($result=='success') {?>
<div class="alert alert-success">
    <p><b>Your booking reference is <?php echo $purchase->bkgref; ?></b></p>
    <p>Your booking is now complete. Thank you.</p>
    <p>You will receive and email with your booking details shortly.</p>
</div>

<?php } else { ?>
<div class="alert alert-danger">
    <p><b>Your booking reference is <?php echo $purchase->bkgref; ?></b></p>
    
    <p>Our booking partner has reported a problem with your booking. No tickets have been reserved.</p>
    <p>The status message from our payment partner is <?php echo $purchase->statusdetail; ?>.</p>
    <p>You should contact your card issuer if you require further information. 
</div>
<?php } ?>

<div class="santa-buttons">
    <a href="http://www.bkrailway.co.uk" class="btn btn-info">Finish</a>
</div>

