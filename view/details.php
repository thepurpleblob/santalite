<h2>Limits</h2>

<table class="table">
    <tbody>
        <tr>
            <th>Number of bookings</th><td><?php echo $details->count; ?></td>
        </tr>
        <tr>
            <th>Number of adults</th><td><?php echo $details->sumadult; ?></td>
        </tr>
        <tr>
            <th>Number of children</th><td><?php echo $details->sumchild; ?></td>
        </tr>
        <tr>
            <th>Total seats booked</th><td><?php echo $details->total; ?></td>
        </tr>
        <tr>
            <th>Booking limit</th><td><?php echo $details->limit; ?></td>
        </tr>
        <tr>
            <th>Seats remaining</th><td><?php echo $details->remaining; ?></td>
        </tr>
    </tbody>
</table>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('limit/index') ?>" class="btn btn-info">Back</a>
</div>
