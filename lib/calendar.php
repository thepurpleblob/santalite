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

    public function render($daysinmonth, $firstday) {
        $days = $this->isoDays();
        $html = '<table class="table">';
        $html .= '<thead>';
        $html .= '<tr>';
        foreach ($days as $day) {
            $html .= '<th>' . $day . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';

        // dom starts as zero until we hit the first day
        $dom = 0;
        $html .= '<tbody>';
        for ($row=1; $row<=5; $row++) {
            $html .= '<tr>';
            foreach ($days as $daynum => $day) {
                if (!$dom and ($daynum == $firstday)) {
                	$dom = 1;
                }
                if (!$dom or ($dom > $daysinmonth)) {
                	$html .= '<td>&nbsp;</td>';
                } else {
                	$html .= '<td>'.$dom.'</td>';
                }
                $dom++;
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
    }

    public function showMonth($month, $year) {

    	// work out what day the first is
    	$firstday = mktime(0, 0, 0, $month, 1, $year);
    	$day = date('N', $firstday);

    	// how many days in that month
    	$dim = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    	return $this->render($dim, $firstday);
    }
}

