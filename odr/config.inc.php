<?php

/////////////////////
// Open Demo Reset //
/////////////////////

define('TIME_INTERVAL', 60*2); // seconds
define('DEMO_FOLDER', '../');
define('BACKUP_FOLDER', 'backup/');
define('MYSQL_DUMP', 'backup.sql');

$ignore_files_n_folders = array('../README.md', '../.git/*');

$db = new mysqli('localhost', 'root', '1708', 'opendemoreset');