<h2>Service times</h2>

<?php if (!$times) { ?>
<div class="alert alert-warning">No service times specified</div>
<?php } else { ?>
<table class="table">
    <thead>
        <tr>
            <th>Number</th>
            <th>Service time</th>
            <th>&nbsp;</th>
        </tr>    
    </thead> 
    <tbody>
        <?php
        $count = 1;
        foreach($times as $time) { ?>
        <tr>
            <td class="lead"><b><?php echo $count++; ?></b></td>
            <td class="lead"><?php echo date('H:i', $time->time) ?></td>
            <td>
                <a href="<?php echo $this->Url('time/edit/'.$time->id); ?>" class="btn btn-info">Edit</a>
                <a href="<?php echo $this->Url('time/delete/'.$time->id); ?>" class="btn btn-danger">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
    
</table>
<?php } ?>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('time/edit/0') ?>" class="btn btn-info">New service time</a>
    <a href="<?php echo $this->Url('admin/index') ?>" class="btn btn-warning">Cancel </a>
</div>
