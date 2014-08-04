
<p class="alert alert-info">
    Please tell us your billing address and contact details.
</p>


<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/contact'); ?>">

    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php $form->text('title', 'Title', $br->getTitle()); ?>
    <?php $form->text('firstname', 'First name(s)', $br->getFirstname()); ?>
    <?php $form->text('lastname', 'Last name', $br->getLastname()); ?>
    <?php $form->text('email', 'Email', $br->getEmail()); ?>
    <?php $form->text('address1', 'Address line 1', $br->getAddress1()); ?>
    <?php $form->text('address2', 'Address line 2', $br->getAddress2()); ?>
    <?php $form->text('city', 'Town/city', $br->getCity()); ?>
    <?php $form->text('postcode', 'Postcode', $br->getPostcode()); ?>
    <?php $form->text('county', 'County', $br->getCounty()); ?>
    <?php $form->text('phone', 'Phone', $br->getPhone()); ?>
    <?php $form->buttons('Next', 'Back', true); ?>
</form>





