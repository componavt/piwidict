<?php

/*******************************************************
 * Init constants and variables (user names, passwords)
 *******************************************************/

## DB connection, MySQL 
## mysql> 
## mysql> GRANT SELECT, INSERT, UPDATE, CREATE, DROP, INDEX ON %.* TO pw_admin@'%' identified by '';
## mysql> FLUSH PRIVILEGES;
##
// DROP user 'pw_user'@'localhost';
// DROP user 'pw_admin'@'localhost'; 
// CREATE USER 'pw_user'@'localhost' IDENTIFIED BY 'pass1';
// CREATE USER 'pw_admin'@'localhost' IDENTIFIED BY 'pass2';
//
// GRANT SELECT ON ruwikt20140904_parsed.* TO pw_user@'%' identified by 'pass1';
// GRANT SELECT, INSERT, UPDATE, CREATE, DROP, INDEX ON ruwikt20140904_parsed.* To 'pw_admin'@'localhost' IDENTIFIED BY 'pass2';
// FLUSH PRIVILEGES;
//
// ## test created tables, e.g. pw_reverse_dict, pw_vocabulary, pw_frequency
// use ruwikt20140904_parsed
// show tables like 'pw%';

define ('NAME_DB','ruwikt20140904_parsed');
$config['hostname']   = 'localhost';
$config['dbname']     = NAME_DB;
$config['user_login']      = 'pw_user';
$config['user_password']   = 'pass1';
$config['admin_login']      = 'pw_admin';
$config['admin_password']   = 'pass2';

?>