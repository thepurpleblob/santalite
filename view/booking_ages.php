
<p class="alert alert-info">
    Please tell us the sex and ages of the children.
    <br /><i>This information is used to help us choose appropriate gifts</i>
</p>


<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/ages'); ?>">
    <?php for ($i=1; $i<=$children; $i++) { ?>
     <div class="row">
         <div class="col-md-2 santa-child">
             <b>Child <?php echo $i?></b>
         </div>
         <div class="col-md-4">
             <?php $form->select('sex'.$i, 'Girl/Boy?', '', array('girl'=>'Girl', 'boy'=>'Boy'), 'Choose...')?>
         </div>
         <div class="col-md-4">
             <?php $form->select('age'.$i, 'Age?', '', $ages, 'Choose...')?>
         </div>
         <div class="col-md-2">
         </div>
     </div>
    <?php } ?>
    <?php $form->buttons('Next', 'Back', true); ?>
</form>





