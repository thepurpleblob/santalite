<h2>Service dates</h2>

<?php if (!$dates) { ?>
<div class="alert alert-warning">No service dates specified</div>
<?php } else { ?>
<table class="table">
    <thead>
        <tr>
            <th>Number</th>
            <th>Service date</th>
            <th>&nbsp;</th>
        </tr>    
    </thead> 
    <tbody>
        <?php
        $count = 1;
        foreach($dates as $date) { ?>
        <tr>
            <td class="lead"><b><?php echo $count++; ?></b></td>
            <td class="lead"><?php echo date('d/m/Y', $date->date) ?></td>
            <td>
                <a href="<?php echo $this->Url('date/edit/'.$date->id); ?>" class="btn btn-info">Edit</a>
                <a href="<?php echo $this->Url('date/delete/'.$date->id); ?>" class="btn btn-danger">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
    
</table>
<?php } ?>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('date/edit/0') ?>" class="btn btn-info">New service date </a>
</div>
