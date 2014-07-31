
<p class="alert alert-info">
    Please tell us your billing address and contact details.
</p>


<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/contact'); ?>">

    <?php $form->text('title', 'Title', ''); ?>
    <?php $form->text('firstname', 'First name(s)', ''); ?>
    <?php $form->text('lastname', 'Last name', ''); ?>
    <?php $form->buttons('Next', 'Back', true); ?>
</form>





