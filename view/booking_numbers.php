
<p class="alert alert-info">
    Who is traveling in your party. Please tell us how many adults, children and infants.
    <br /><b>Note: Online booking for this train is limited to <?php echo $limit->partysize;?></b>
</p>

<p class="santa-traintime">Selected train departs at <?php echo date('H:i', $time->time)?> on
    <?php echo date('jS F Y', $date->date); ?></p>

<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/numbers'); ?>">
    <?php if ($errors) { ?>
    <div class="alert alert-danger">
        Please check that your total party size does not exceed <?php echo $limit->partysize; ?>
    </div>
    <?php } ?>
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





