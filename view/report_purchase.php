<?php if (!$statusok) {?>
    <div class="alert alert-danger">
        WARNING: This purchase did not complete. Do not issue tickets.
    </div>
<?php } ?>

<table class="table table-condensed">
    <tbody>
        <?php echo $body; ?>
    </tbody>
</table>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('report/purchases'); ?>" class="btn btn-primary"><i class="fa fa-undo"></i> Back </a>
</div>