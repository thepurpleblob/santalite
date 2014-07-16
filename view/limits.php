<h2>Limits</h2>

<form role="form" method="post" action="<?php echo $this->Url('time/edit/'.$time->id) ?>">
    <?php foreach($dates as $date) { ?>
        <h4><?php echo date('l d/m/Y', $date->date); ?></h4>
        <table class="table">
        <?php foreach ($times as $time) { ?>
            <tr>
                <td>
                    <?php echo date('H:i', $time->time); ?>
                </td>
            </tr>
        <?php } ?>
        </table>
    <?php } ?>
    <?php $form->buttons(); ?>
</form>

