<?php

namespace lib;

class calendar {

    private function isoDays() {
        return array(
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
            7 => 'Sun',
        );
    }

    public function render($daysinmonth, $firstday, $bookingdays, $url, $month, $year) {
        $days = $this->isoDays();
        $html = '<table class="table table-bordered santa-table">';
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
                $now = mktime(null, null, null, $month, $dom, $year);
                if (!$dom or ($dom > $daysinmonth)) {
                	$html .= '<td class="santa-cell">&nbsp;</td>';
                } else if (isset($bookingdays[$dom])) {
                    $tooltip = date('l jS F Y', $now);
                    $html .= '<td class="santa-cell available" >';
                    $html .= '<a class="santa-tooltip" data-toggle="tooltip" title="'.$tooltip.'" href="'.$url.$bookingdays[$dom].'"><b>'.$dom.'</b></a></td>';
                    $dom++;
                } else {
                	$html .= '<td class="santa-cell dimmed">'.$dom.'</td>';
                    $dom++;
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
    }

    public function showMonth($month, $year, $bookingdays, $url) {

        $dateObj   = \DateTime::createFromFormat('!m', $month);
        $monthName = $dateObj->format('F');

    	// work out what day the first is
    	$firstday = mktime(0, 0, 0, $month, 1, $year);
    	$day = date('N', $firstday);

    	// how many days in that month
    	$dim = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    	return "<div class=\"santa-cal\"><h2><span class=\"label label-primary\">$monthName $year</span></h2>" .
    	    $this->render($dim, $day, $bookingdays, $url, $month, $year) . '</div>';
    }
}

