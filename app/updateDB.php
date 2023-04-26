<?php
	// check this file's MD5 to make sure it wasn't called before
	$prevMD5=@implode('', @file(dirname(__FILE__).'/setup.md5'));
	$thisMD5=md5(@implode('', @file("./updateDB.php")));
	if($thisMD5==$prevMD5){
		$setupAlreadyRun=true;
	}else{
		// set up tables
		if(!isset($silent)){
			$silent=true;
		}

		// set up tables
		setupTable('invoices', "create table if not exists `invoices` (   `id` INT unsigned not null auto_increment , primary key (`id`), `code` VARCHAR(20) , unique `code_unique` (`code`), `status` VARCHAR(20) not null default 'Unpaid' , `date_due` DATE , `client` INT unsigned , `client_contact` INT unsigned , `client_address` INT unsigned , `client_phone` INT unsigned , `client_email` INT unsigned , `client_website` INT unsigned , `client_comments` INT unsigned , `subtotal` DECIMAL(9,2) , `discount` DECIMAL(4,2) default '0' , `tax` DECIMAL(9,2) default '0' , `total` DECIMAL(9,2) default '0' , `comments` TEXT , `invoice_template` VARCHAR(100) ) CHARSET utf8", $silent);
		setupIndexes('invoices', array('client'));
		setupTable('clients', "create table if not exists `clients` (   `id` INT unsigned not null auto_increment , primary key (`id`), `name` VARCHAR(200) , unique `name_unique` (`name`), `contact` VARCHAR(255) , `title` VARCHAR(40) , `address` TEXT , `city` VARCHAR(40) , `country` VARCHAR(40) , `phone` VARCHAR(100) , `email` VARCHAR(80) , `website` VARCHAR(200) , `comments` TEXT ) CHARSET utf8", $silent);
		setupTable('item_prices', "create table if not exists `item_prices` (   `id` INT unsigned not null auto_increment , primary key (`id`), `item` INT unsigned , `price` DECIMAL(10,2) default '0.00' , `date` DATE ) CHARSET utf8", $silent);
		setupIndexes('item_prices', array('item'));
		setupTable('invoice_items', "create table if not exists `invoice_items` (   `id` INT unsigned not null auto_increment , primary key (`id`), `invoice` INT unsigned , `item` INT unsigned , `unit_price` DECIMAL(10,2) unsigned default '0.00' , `qty` DECIMAL(9,3) default '1' , `price` DECIMAL(9,2) ) CHARSET utf8", $silent);
		setupIndexes('invoice_items', array('invoice','item'));
		setupTable('items', "create table if not exists `items` (   `id` INT unsigned not null auto_increment , primary key (`id`), `item_description` TEXT , `unit_price` DECIMAL(10,2) default '0.00' ) CHARSET utf8", $silent);


		// save MD5
		if($fp=@fopen(dirname(__FILE__).'/setup.md5', 'w')){
			fwrite($fp, $thisMD5);
			fclose($fp);
		}
	}


	function setupIndexes($tableName, $arrFields){
		if(!is_array($arrFields)){
			return false;
		}

		foreach($arrFields as $fieldName){
			if(!$res=@db_query("SHOW COLUMNS FROM `$tableName` like '$fieldName'")){
				continue;
			}
			if(!$row=@db_fetch_assoc($res)){
				continue;
			}
			if($row['Key']==''){
				@db_query("ALTER TABLE `$tableName` ADD INDEX `$fieldName` (`$fieldName`)");
			}
		}
	}


	function setupTable($tableName, $createSQL='', $silent=true, $arrAlter=''){
		global $Translation;
		ob_start();

		echo '<div style="padding: 5px; border-bottom:solid 1px silver; font-family: verdana, arial; font-size: 10px;">';

		// is there a table rename query?
		if(is_array($arrAlter)){
			$matches=array();
			if(preg_match("/ALTER TABLE `(.*)` RENAME `$tableName`/", $arrAlter[0], $matches)){
				$oldTableName=$matches[1];
			}
		}

		if($res=@db_query("select count(1) from `$tableName`")){ // table already exists
			if($row = @db_fetch_array($res)){
				echo str_replace("<TableName>", $tableName, str_replace("<NumRecords>", $row[0],$Translation["table exists"]));
				if(is_array($arrAlter)){
					echo '<br>';
					foreach($arrAlter as $alter){
						if($alter!=''){
							echo "$alter ... ";
							if(!@db_query($alter)){
								echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
								echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
							}else{
								echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
							}
						}
					}
				}else{
					echo $Translation["table uptodate"];
				}
			}else{
				echo str_replace("<TableName>", $tableName, $Translation["couldnt count"]);
			}
		}else{ // given tableName doesn't exist

			if($oldTableName!=''){ // if we have a table rename query
				if($ro=@db_query("select count(1) from `$oldTableName`")){ // if old table exists, rename it.
					$renameQuery=array_shift($arrAlter); // get and remove rename query

					echo "$renameQuery ... ";
					if(!@db_query($renameQuery)){
						echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
						echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
					}else{
						echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
					}

					if(is_array($arrAlter)) setupTable($tableName, $createSQL, false, $arrAlter); // execute Alter queries on renamed table ...
				}else{ // if old tableName doesn't exist (nor the new one since we're here), then just create the table.
					setupTable($tableName, $createSQL, false); // no Alter queries passed ...
				}
			}else{ // tableName doesn't exist and no rename, so just create the table
				echo str_replace("<TableName>", $tableName, $Translation["creating table"]);
				if(!@db_query($createSQL)){
					echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
					echo '<div class="text-danger">' . $Translation['mysql said'] . db_error(db_link()) . '</div>';
				}else{
					echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
				}
			}
		}

		echo "</div>";

		$out=ob_get_contents();
		ob_end_clean();
		if(!$silent){
			echo $out;
		}
	}
?>