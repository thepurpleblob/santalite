<h2>Users</h2>

<table class="table">
    <thead>
        <tr>
            <th>Username</th>
            <th>Full name</th>
            <th>Role</th>
            <th>&nbsp;</th>
        </tr>    
    </thead> 
    <tbody>
        <?php
        foreach($users as $user) { ?>
        <tr>
            <td><b><?php echo $user->username; ?></b></td>
            <td><?php echo $user->fullname; ?></td>
            <td><?php echo $user->role; ?></td>
            <td>
                <a href="<?php echo $this->Url('user/edit/'.$user->id); ?>" class="btn btn-info">
                    <i class="fa fa-cogs"></i> Edit</a>
                <?php if ($user->username != 'admin') { ?>    
                    <a href="<?php echo $this->Url('date/delete/'.$date->id); ?>" class="btn btn-danger">
                        <i class="fa fa-undo"></i> Delete</a>
                <?php } ?>    
            </td>
        </tr>
        <?php } ?>
    </tbody>
    
</table>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('user/edit/0') ?>" class="btn btn-info">New user</a>
    <a href="<?php echo $this->Url('admin/index') ?>" class="btn btn-warning">Cancel</a>
</div>
