<?php if ($date->id) { ?>
    <h2>Edit service date</h2> 
<?php } else { ?>
    <h2>New service date</h2>
<?php } ?>

<form role="form" method="post" action="<?php echo $this->Url('date/edit/'.$date->id) ?>">
    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php $form->text('date', 'Service time', date('d/m/Y', $date->date)); ?>
    <?php $form->buttons(); ?>
</form>

