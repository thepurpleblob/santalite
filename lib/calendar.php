<?php

namespace lib;

class calendar {
    
    private function isoDays() {
        return array(
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        );
    }
    
    public function render($daysinmonth, $firstmonday) {
        $days = $this->isoDays();
        $html = '<table class="table">';
        $html .= '<thead>';
        $html .= '<tr>';
        foreach ($days as $day) {
            $html .= '<th>' . $day . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';
        
        $html .= '</table>';
        
        return $html;
    }    
}

