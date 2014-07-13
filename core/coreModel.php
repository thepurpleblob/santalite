<?php

namespace core;

define('QUERY_SINGLE', 1);
define('QUERY_ARRAY', 2);

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
        $rowcount = $this->DB->exec($sql);
        $error = $this->DB->errorInfo();
        if ($error[1]) {
            echo "Database exec error - "; var_dump($error);
            die;
        }

        return $rowcount;
    }
    
    /**
     * Wrapper around PDO::query
     */
    public function Query($sql, $rt=QUERY_ARRAY) {
        $result = $this->DB->query($sql);  
        $error = $this->DB->errorInfo();
        if ($error[1]) {
            echo "Database query error - "; var_dump($error);
            die;
        }
        $data = $result->fetchAll(\PDO::FETCH_CLASS, 'stdClass');
        
        // return single record if required
        if ($rt==QUERY_SINGLE) {
            if (count($data) > 1) {
                echo "Database query error - more than one records found";
                die;
            } else {
                if (count($data)==1) {
                    return $data[0];
                } else {
                    return null;
                }
            }
        } else {
            return $data;
        }    
    }

}
