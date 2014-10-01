<h2>Limits</h2>

<form role="form" method="post" action="<?php echo $this->Url('limit/index/') ?>">
    <?php if ($errors) {$this->formErrors($errors);} ?>
    <?php foreach($dates as $date) { ?>
        <h3><?php echo date('l d/m/Y', $date->date); ?></h3>
        <table class="table">
        <?php foreach ($times as $time) { 
            $formid = "{$date->id}_{$time->id}";
            ?>
            <tr>
                <td class="lead">
                    <?php echo date('H:i', $time->time); ?>
                </td>
                <td>
                    <?php $form->text('limit'.$formid, 'Limit', $limits[$date->id][$time->id]->maxlimit); ?>
                </td>
                <td>
                    <?php $form->text('party'.$formid, 'Party size', $limits[$date->id][$time->id]->partysize); ?>
                </td>
                <td nowrap>
                    <b>Remaining:</b>&nbsp;
                    <span class="lead">
                        <?php echo $limits[$date->id][$time->id]->maxlimit - $limits[$date->id][$time->id]->count; ?>
                    </span>
                </td>
                <td>
                    <a class="btn btn-primary btn-sm" href="<?php echo $this->Url("limit/detail/{$date->id}/{$time->id}"); ?>">Detail</a>
                </td>
            </tr>
        <?php } ?>
        </table>
    <?php } ?>
    <?php $form->buttons(); ?>
</form>

