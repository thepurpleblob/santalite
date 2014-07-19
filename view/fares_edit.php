<h2>Update fares</h2>

<form role="form" method="post" action="<?php echo $this->Url('fares/index/') ?>">
    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php $form->text('adult', 'Adult fare (£)', number_format($fares->adult / 100, 2)); ?>
    <?php $form->text('child', 'Child fare (£)', number_format($fares->child / 100, 2)); ?>
    <?php $form->buttons(); ?>
</form>

