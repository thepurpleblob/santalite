<?php if ($available) { ?>

<p class="alert alert-info">Santa trains leave Bo'ness at the following times. Choose the train
    you would like to travel on.</p>

<p class="lead">Booking date <?php echo date('jS F Y', $date->date); ?></p>

<table class="table table-striped">
    <thead>
        <th>Depart</th>
        <th>From</th>
        <th>&nbsp;</th>
    </thead>
    <tbody>
    <?php foreach ($times as $time) { ?>

        <tr>
            <td class="lead"><b><?php echo date('H:i', $time->time) ?></b></td>
            <td>Bo'ness</td>
            <?php if ($seatsavailable[$time->id()] >= $seatsneeded) { ?>
            <td><a class="btn btn-primary" href="<?php echo $this->Url('booking/time/'.$time->id) ?>">Select</a></td>
            <?php } else { ?>
            <td><button class="btn btn-warning" disabled="disabled">Sorry - not available</button></td>
            <?php } ?>
        </tr>
    <?php } ?>
    </tbody>
</table>

<?php } else { ?>
<div class="alert alert-danger">
    There are no remaining services that can accomodate your party on the chosen day. You
    can click 'Go back' and try a different day.
</div>
<?php }?>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('booking/date') ?>" class="btn btn-warning">Go back</a>
</div>


