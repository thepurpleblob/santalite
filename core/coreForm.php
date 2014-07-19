<?php

namespace core;

class coreForm {
    
    /*
     * Fill arrays for drop-downs
     */
    private function fill($low, $high) {
        $a = array();
        for ($i=$low; $i<=$high; $i++) {
            $a[$i] = $i;
        }
        return $a;
    }
    
    public function text($name, $label, $value) {
        $id = $name . 'Text';
        echo '<div class="form-group">';
        echo '    <label for="'.$id.'">'.$label.'</label>';
        echo '    <input type="text" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" />';
        echo '</div>';
    }
    
    public function password($name, $label, $value) {
        $id = $name . 'Password';
        echo '<div class="form-group">';
        echo '    <label for="'.$id.'">'.$label.'</label>';
        echo '    <input type="password" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" />';
        echo '</div>';
    }   
    
    public function select($name, $label, $selected, $options) {
        $id = $name . 'Select';
        echo '<div class="form-group">';
        echo '    <label for="'.$id.'">'.$label.'</label>';
        echo '    <select class="form-control" name="'.$name.'">';
        foreach ($options as $value => $option) {
            if ($value == $selected) {
                $strsel = 'selected';
            } else {
                $strsel = '';
            }
            echo '<option value="'.$value.'" '.$strsel.'>'.$option.'</option>';            
        }
        echo '    </select>';
        echo "</div>";
    }
    
    public function hidden($name, $value) {
        echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
    }
    
    public function buttons($save='Save', $cancel='Cancel') {
        echo '<div class="form-group">';
        echo '    <button type="submit" name="save" value="save" class="btn btn-primary">'.$save.'</button>';
        echo '    <button type="submit" name="cancel" value="cancel" class="btn btn-warning">'.$cancel.'</button>';        
        echo '</div>';
    }
}

