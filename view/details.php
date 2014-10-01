<h2>Service detail</h2>

<table class="table">
    <tbody>
        <tr>
            <th>Service date</th><td><?php echo date('d/M/Y', $details->date->date); ?></td>
        </tr>
        <tr>
            <th>Service time</th><td><?php echo date('H:i', $details->time->time); ?></td>
        </tr>
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
