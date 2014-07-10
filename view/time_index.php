<h2>Service times</h2>

<?php if (!$times) { ?>
<div class="alert alert-warning">No service times specified</div>
<?php } else { ?>

<?php } ?>

<div class="santa-buttons">
    <a href="<?php echo $this->Url('time/edit/0') ?>" class="btn btn-info">New service time</a>
</div>
