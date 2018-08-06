<h2>Login</h2>

<form class="form-horizontal" role="form" method="post" action="<?php echo $this->Url('user/login') ?>">
    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php $form->text('username', 'Username', ''); ?>
    <?php $form->password('password', 'Password'); ?>
    <?php $form->buttons('Login'); ?>
</form>

