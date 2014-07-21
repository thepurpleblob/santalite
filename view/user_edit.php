<?php if ($user->id) { ?>
    <h2>Edit user</h2> 
<?php } else { ?>
    <h2>New user</h2>
<?php } ?>

<form role="form" method="post" action="<?php echo $this->Url('user/edit/'.$user->id) ?>">
    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php $form->text('username', 'Username', $user->username); ?>
    <?php $form->text('fullname', 'Full name', $user->fullname); ?>
    <?php $form->password('password', 'Password'); ?>
    <?php $form->select('role', 'User role', $user->role, $roles); ?>
    <?php $form->buttons(); ?>
</form>

