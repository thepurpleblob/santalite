<?php if ($time->id) { ?>
    <h2>Edit service time</h2> 
<?php } else { ?>
    <h2>New service time</h2>
<?php } ?>

<form role="form" method="post" action="<?php echo $this->Url('time/edit/'.$time->id) ?>">
    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php $form->text('time', 'Service time', date('H:i', $time->time)); ?>
    <?php $form->buttons('Next', 'Back'); ?>
</form>

