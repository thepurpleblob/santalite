
<p class="alert alert-info">
    Who is traveling in your party. Please tell us how many adults, children and infants.
</p>

<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/numbers'); ?>">
    <?php $form->select('adults',
    		'Number of adults - £'.number_format($fares->adult/100, 2).' each',
    		$br->getAdults(),
    		$adultchoices,
            '',
            8);
    ?>
    <?php $form->select('children',
    		'Number of children - £'.number_format($fares->child/100, 2).' each <small class="santa-subtext">(2 years to 15 years)</small>',
    		$br->getChildren(),
    		$childrenchoices,
            '',
            8)?>
    <?php $form->select('infants',
    		'Number of infants, <small class="santa-subtext">(younger than 2 years on day of travel)</small>',
    		$br->getInfants(),
    		$infantchoices,
            '',
            8)?>
  <div class="alert alert-warning">
    Infants travel free but are not allocated a seat.
</div>

    <?php $form->buttons('Next', 'Back', true); ?>
</form>





