<?php if (!$purchases) { ?>
    <div class="alert alert-warning">
        There are no recorded purchases
    </div>
<?php } else { ?>
    <table class="table table-condensed">
        <thead>
            <th>Reference</th>
            <th>Date</th>
            <th>Status</th>
            <th>Name</th>
            <th>Day #</th>
            <th>Train #</th>
            <th>Amount</th>
            <th>&nbsp;</th>
        </thead>
        <tbody>
        <?php foreach ($purchases as $purchase) { 
            $class = $purchase->status == 'OK' ? '' : 'santa-fail';
            ?>
            <tr class="<?php echo $class; ?>">
                <td><?php echo $purchase->bkgref; ?></td>
                <td><?php echo $purchase->bkgdate; ?>
                <td><?php echo $purchase->status ? $purchase->status : '-'; ?>
                <td><?php echo $purchase->firstname . ' ' . $purchase->surname; ?>
                <td><?php echo $purchase->day; ?></td>
                <td><?php echo $purchase->train; ?></td>
                <td><?php echo '&pound; ' . number_format($purchase->payment / 100, 2); ?></td>
                <td><a href="<?php echo $this->url('report/purchase/'.$purchase->id); ?>" class="btn btn-primary btn-sm">View</a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('admin/index'); ?>" class="btn btn-primary"><i class="fa fa-undo"></i> Done </a>
</div>