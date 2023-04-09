<?php
//super basic update for list of phone numbers from Twilio report to add to blocklist
$base = dirname(dirname(__FILE__));  /* grab path to top level include dir */
set_include_path(get_include_path() . ':' . $base);
include_once('include/clisession.inc');

$ids = getIds();

$ctr = 0;
//$out = array();
$today = date("Y-m-d-H-m-s");
$endList = [];

foreach ($ids as $number) {
	$number = trim($number);
	$ctr++;
	if ($ctr > 3) {
		//exit();
	}
	
	if (isset($number) && strlen($number) >= 10) {
		$checkNumber = Utility::checkPhoneBlocklist($dbh, $number);
		$cell = "+" . $number;
		echo "\nCheck Number: $checkNumber and phone: $number";
		if ($checkNumber == '') {
			Utility::blocklistPhone($dbh, $cell, "Bulk Blocklist Script");
			echo "\nCustomer reply: $cell";
		} else {
			echo "\nFound in blocklist, not adding: $number";
		}
	}
	
}

function getIds() {

	$in = '11234567890
	12345678901';



	return explode("\n", $in);
}