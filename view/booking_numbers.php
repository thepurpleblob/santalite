
<p class="alert alert-info">
    Who is traveling in your party. Please tell us how many adults, children and infants.
</p>

<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/numbers'); ?>">
    <?php $form->select('adults',
    		'Number of adults - £'.number_format($fares->adult/100, 2).' each',
    		1,
    		$adultchoices)?>
    <?php $form->select('children',
    		'Number of children - £'.number_format($fares->child/100, 2).' each<br /><p class="santa-subtext">(18 months to 15 years)</p>',
    		1,
    		$childrenchoices)?>
    <?php $form->select('infants',
    		'Number of infants, <br /><p class="santa-subtext">(up to 17 months on day of travel)</p>',
    		0,
    		$infantchoices)?>
  <div class="alert alert-warning">
    Infants travel free but are not allocated a seat.
</div>

    <?php $form->buttons('Next', 'Back', true); ?>
</form>





