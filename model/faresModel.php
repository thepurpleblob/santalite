<?php

namespace model;

use core\coreModel;

class faresModel extends coreModel {

    public function getFares() {
        $sql = "SELECT * FROM fares WHERE id=1";
        return $this->Query($sql, QUERY_SINGLE);
    }
    
    public function updateFares($fares) {
        $sql = "UPDATE fares SET adult={$fares->adult}, child={$fares->child} WHERE id=1";
        $this->Exec($sql);
    }
}
