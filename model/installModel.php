<?php

namespace model;


class installModel {

    function install_tables() {
        
        // traintime
        $sql =  'CREATE TABLE IF NOT EXISTS traintime (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            time INTEGER
            )';
        \ORM::for_table('traintime')->raw_execute($sql);
        
        // traindate
        $sql =  'CREATE TABLE IF NOT EXISTS traindate (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date INTEGER
            )';
        \ORM::for_table('traindate')->raw_execute($sql);
        
        // limits
        $sql = 'CREATE TABLE IF NOT EXISTS trainlimit (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timeid INTEGER,
            dateid INTEGER,
            maxlimit INTEGER,
            partysize INTEGER
            )';
        \ORM::for_table('trainlimit')->raw_execute($sql);
        
        // fares
        // this will only be a single record, so we'll create it now
        $sql = 'CREATE TABLE IF NOT EXISTS fares (
            id INTEGER PRIMARY KEY,
            adult INTEGER,
            child INTEGER
            )';
        \ORM::for_table('fares')->raw_execute($sql);      
        if (!\ORM::for_table('fares')->find_one(1)) {
            $fare = \ORM::for_table('fares')->create();
            $fare->id = 1;
            $fare->adult = 900;
            $fare->child = 900;
            $fare->save();
        }
        
        // security
        $sql = 'CREATE TABLE IF NOT EXISTS user (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            fullname TEXT,
            password TEXT,
            role TEXT
            )';
        \ORM::for_table('traintime')->raw_execute($sql);
        if (!\ORM::for_table('user')->where(array('username'=>'admin'))->find_one()) {
            $user = \ORM::for_table('user')->create();
            $user->username ='admin';
            $user->fullname = 'Administrator';
            $user->password = md5('admin');
            $user->role = 'admin';
            $user->save();
        }
        
        // Purchase record
        $sql = 'CREATE TABLE IF NOT EXISTS purchase (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT,
            day INTEGER,
            train INTEGER,
            bkgref TEXT,
            surname TEXT,
            title TEXT,
            firstname TEXT,
            address1 TEXT,
            address2 TEXT,
            address3 TEXT,
            address4 TEXT,
            postcode TEXT,
            phone TEXT,
            email TEXT,
            adult INTEGER,
            child INTEGER,
            infant INTEGER,
            oap INTEGER,
            childages TEXT,
            comment TEXT,
            payment INTEGER,
            bkgdate INTEGER,
            card INTEGER,
            action INTEGER,
            season INTEGER
            )';
        \ORM::for_table('purchase')->raw_execute($sql);
    }
}
