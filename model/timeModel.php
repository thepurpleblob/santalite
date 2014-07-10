<?php

namespace model;

use core\coreModel;

class timeModel extends coreModel {

    function getAllTimes() {
        $sql = "SELECT * FROM traintime ORDER BY time";
        return $this->Query($sql);
    }
}
