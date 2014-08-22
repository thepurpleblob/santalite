<?php if (!$purchases) { ?>
    <div class="alert alert-warning">
        There are no recorded purchases
    </div>
<?php } else { ?>
    <table class="table table-striped">
        <thead>
            <th>Reference</th>
            <th>Date</th>
            <th>Status</th>
            <th>Name</th>
            <th>Amount</th>
        </thead>
        <tbody>
        <?php foreach ($purchases as $purchase) { ?>
            <tr>
                <td><?php echo $purchase->reference; ?></td>
                <td><?php echo $purchase->bkgdate; ?>
                <td><?php echo $purchase->status; ?>
                <td><?php echo $purchase->firstname . ' ' . $purchase->surname; ?>
                <td><?php echo $purchase->payment; ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>