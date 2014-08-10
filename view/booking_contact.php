
<p class="alert alert-info">
    Please tell us your address for ticket delivery (and if we need to contact you).
</p>


<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/contact'); ?>">

    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php $form->text('title', 'Title', $br->getTitle()); ?>
    <?php $form->text('firstname', 'First name(s)', $br->getFirstname(), true); ?>
    <?php $form->text('lastname', 'Last name', $br->getLastname(), true); ?>
    <?php $form->text('email', 'Email', $br->getEmail(), true); ?>
    <?php $form->text('address1', 'Address line 1', $br->getAddress1(), true); ?>
    <?php $form->text('address2', 'Address line 2', $br->getAddress2()); ?>
    <?php $form->text('city', 'Town/city', $br->getCity(), true); ?>
    <?php $form->text('county', 'County', $br->getCounty()); ?>
    <?php $form->text('postcode', 'Postcode', $br->getPostcode(), true); ?>
    <?php $form->text('phone', 'Phone', $br->getPhone()); ?>
    <?php $form->buttons('Next', 'Back', true); ?>
</form>





