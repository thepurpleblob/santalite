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
        
        // limits
        $sql = 'CREATE TABLE IF NOT EXISTS trainlimit (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timeid INTEGER,
            dateid INTEGER,
            maxlimit INTEGER,
            partysize INTEGER
            )';
        $this->Exec($sql);
        
        // fares
        // this will only be a single record, so we'll create it now
        $sql = 'CREATE TABLE IF NOT EXISTS fares (
            id INTEGER PRIMARY KEY,
            adult INTEGER,
            child INTEGER
            )';
        $this->Exec($sql);      
        $sql = 'SELECT * FROM fares WHERE id=1';
        if (!$this->Query($sql, QUERY_SINGLE)) {
            $sql = 'INSERT INTO fares (id, adult, child) VALUES (1, 900, 900)';
            $this->Exec($sql);
        }
        
        // security
        $sql = 'CREATE TABLE IF NOT EXISTS user (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            fullname TEXT,
            password TEXT,
            role TEXT
            )';
        $this->Exec($sql);
        $sql = "SELECT * FROM user WHERE username='admin'";
        if (!$this->Query($sql, QUERY_SINGLE)) {
            $password = md5('admin');
            $sql = "INSERT INTO user (username, fullname, password, role)
                VALUES ('admin', 'Administrator', '$password', 'admin')";
            $this->Exec($sql);
        }
    }
}
