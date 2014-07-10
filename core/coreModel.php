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
        } catch (PDOException $e) {
            die( 'Database Exec error ' . $this->DB->errorInfo());
        }

        return $rowcount;
    }
    
    /**
     * Wrapper around PDO::query
     */
    public function Query($sql) {
        try {
            $result = $this->DB->query($sql);   
        } catch (PDOException $e) {
            die( 'Database Query error ' . $this->DB->errorInfo());
        }
        $data = $result->fetchAll(\PDO::FETCH_CLASS, 'stdClass');
        return $data;
    }

}
