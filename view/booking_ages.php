
<p class="alert alert-info">
    Please tell us the sex and ages of the children.
    <br /><i>This information is used to help us choose appropriate gifts</i>
</p>


<form role="form" class="form-horizontal" method="post" action="<?php echo $this->Url('booking/ages'); ?>">
<table class="table table-striped">
    <tbody>
    <?php for ($i=1; $i<=$children; $i++) {?>
     <tr>
         <td class="santa-child">
             <b>Child <?php echo $i?></b>
         </td>
         <td>
             <?php $form->select(
                     'sex'.$i, 
                     'Girl/Boy?',
                     isset($sexes[$i]) ? $sexes[$i] : '',
                     array('girl'=>'Girl', 'boy'=>'Boy'),
                     'Choose...');
             ?>
         </td>
         <td>
             <?php $form->select(
                     'age'.$i,
                     'Age?',
                     isset($ages[$i]) ? $ages[$i] : '',
                     $chooseages,
                     'Choose...');
             ?>
         </td>
     </tr>
    <?php } ?>
    </tbody>
    </table>
    <?php $form->buttons('Next', 'Back', true); ?>
</form>





