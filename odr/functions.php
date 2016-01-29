<?php
session_start();
include 'config.inc.php';

$now = time();

if(!file_exists('time.txt')) {
	file_put_contents('time.txt', time());
}

$last = file_get_contents('time.txt');

if($now-$last>TIME_INTERVAL) {
	// will reset
	if(!file_exists('lock.txt')) {
		// create lock file
		file_put_contents('lock.txt', '1');

		// delete sessions
		$_SESSION = array();
		@ini_set('session.gc_max_lifetime', 0);
		@ini_set('session.gc_probability', 1);
		@ini_set('session.gc_divisor', 1);
		session_destroy();

		// delete cookies
	    foreach($_COOKIE as $cookie) {
	        setcookie($cookie, '', 1);
	        setcookie($cookie, '', 1, '/');
	    }

		// delete database
		delete_all_tables($db);

		// run queries
		$db->multi_query(file_get_contents(MYSQL_DUMP));

		// restore files
		$demofolder = array();
		$demofolder_ = rglob(DEMO_FOLDER."{,.}*", GLOB_MARK+GLOB_BRACE);
		$ignore_files_n_folders[] = '../'.basename(dirname(__FILE__)).'/*';
		$ignore_files_n_folders[] = '../'.basename(dirname(__FILE__)).'/';

		foreach($demofolder_ as $file) {
			if(basename($file)=='.' || basename($file)=='..') continue;
			foreach($ignore_files_n_folders as $ignore) {
				if($file==$ignore OR fnmatch($ignore, $file)) {
					continue 2;
				}
			}
			$demofolder[] = $file;
		}

		$backupfolder = array();
		$backupfolder_ = rglob(BACKUP_FOLDER."{,.}*", GLOB_MARK+GLOB_BRACE);
		foreach($backupfolder_ as $file) {
			if(!basename($file)!='.' AND basename($file)!='..') {
				$backupfolder[] = $file;
			}
		}

		$diff1 = array_map(function($i) {
			return str_replace(DEMO_FOLDER, null, $i);
		}, $demofolder);

		$diff2 = array_map(function($i) {
			return str_replace(BACKUP_FOLDER, null, $i);
		}, $backupfolder);

		$diff = array_diff($diff1, $diff2);
		$intersect = array_intersect($diff1, $diff2);

		// delete newly created files
		foreach($diff as $delme) {
			if(is_dir(DEMO_FOLDER.$delme)) {
				delTree(DEMO_FOLDER.$delme);
			} else {
				unlink(DEMO_FOLDER.$delme);
			}
		}

		// check if some file has been deleted
		// then restore it
		$rev_diff = array_diff($diff2, $diff1);
		foreach($rev_diff as $check) {
			if(!file_exists(DEMO_FOLDER.$check)) {
				if(is_dir(BACKUP_FOLDER.$check)) {
					mkdir(DEMO_FOLDER.$check, 0777, true);
				} else {
					copy(BACKUP_FOLDER.$check, DEMO_FOLDER.$check);
				}
			}
		}

		// check if some file has been changed
		// then restore it
		foreach($intersect as $check) {
			if(is_dir(BACKUP_FOLDER.$check)) {
				if(!is_dir(DEMO_FOLDER.$check)) {
					mkdir(DEMO_FOLDER.$check, 0777, true);
				}
			} else {
				$md5_demo = md5_file(DEMO_FOLDER.$check);
				$md5_backup = md5_file(BACKUP_FOLDER.$check);
				if($md5_demo!=$md5_backup) {
					unlink(DEMO_FOLDER.$check);
					copy(BACKUP_FOLDER.$check, DEMO_FOLDER.$check);
				}
			}
		}

		// save time
		file_put_contents('time.txt', time());
		// delete lock file
		unlink('lock.txt');
	}
}


echo TIME_INTERVAL-($now-$last);


function delete_all_tables($db) {
	$db->query("DROP PROCEDURE IF EXISTS `drop_all_tables`");
	$db->query("CREATE PROCEDURE `drop_all_tables`()
		BEGIN
		    DECLARE _done INT DEFAULT FALSE;
		    DECLARE _tableName VARCHAR(255);
		    DECLARE _cursor CURSOR FOR
		        SELECT table_name 
		        FROM information_schema.TABLES
		        WHERE table_schema = SCHEMA();
		    DECLARE CONTINUE HANDLER FOR NOT FOUND SET _done = TRUE;

		    SET FOREIGN_KEY_CHECKS = 0;

		    OPEN _cursor;

		    REPEAT FETCH _cursor INTO _tableName;

		    IF NOT _done THEN
		        SET @stmt_sql = CONCAT('DROP TABLE ', _tableName);
		        PREPARE stmt1 FROM @stmt_sql;
		        EXECUTE stmt1;
		        DEALLOCATE PREPARE stmt1;
		    END IF;

		    UNTIL _done END REPEAT;

		    CLOSE _cursor;
		    SET FOREIGN_KEY_CHECKS = 1;
		END");
	$db->query("call drop_all_tables()");
	$db->query("DROP PROCEDURE IF EXISTS `drop_all_tables`");
}

function multiquery($db, $queries) {
	$queries = explode(";", $queries);
	$success = true;
	foreach($queries as $query) {
		$success = $success AND $db->query($query);
	}
	return $success;
}

// Does not support flag GLOB_BRACE
function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags); 
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

function delTree($dir) { 
   $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
  }