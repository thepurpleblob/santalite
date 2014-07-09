<?php

namespace model;

use core\coreModel;

class installModel extends coreModel {

    function install_tables() {
        $sql =  'CREATE TABLE IF NOT EXISTS traintime (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            time TEXT
            )';
        $this->Exec($sql);
    }
}
