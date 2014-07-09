<?php

namespace core;

class coreModel {

    protected $DB;

    /**
     * Constructor
     */
    public function __construct() {
        global $DB;

        $this->DB = $DB;
    }

    /**
     * Wrapper around PDO::exec
     */
    public function Exec($sql) {
        try {
            $rowcount = $this->DB->exec($sql);
        } catch (Exception $e) {
            die( 'Database Exec error ' . $this->db->errorInfo());
        }

        return $rowcount;
    }

}
