<?php

namespace model;

use core\coreModel;

class installModel extends coreModel {

    function install_tables() {
        
        // traintime
        $sql =  'CREATE TABLE IF NOT EXISTS traintime (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            time INTEGER
            )';
        $this->Exec($sql);
        
        // traindate
        $sql =  'CREATE TABLE IF NOT EXISTS traindate (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date INTEGER
            )';
        $this->Exec($sql);
    }
}
