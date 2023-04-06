<?php
/**
 * Created by PhpStorm.
 * User: jnason
 *
 */

ini_set('error_log', '/var/log/httpd/viewJson.log');


$script = array_shift($argv);
$inputFile = array_shift($argv);
$inputData = [];

if (! is_file($inputFile)) {
        usage("Missing input json file");
} else {
	$inputData = [];
	$in = file_get_contents($inputFile);
	$inputData = json_decode($in, true);

	//make sure inputdata has something
	if (! count($inputData)) {
		usage("Input file is empty ");
	}
		
}

print_r($inputData);

function usage($err) {
        echo "\n\nUSAGE: creditParser.php <path/to/input/json/file> <optional step number>\n";
		echo "Contents json array with: firstName, lastName, address, city, state, zip, dob, ssn\n";
        echo "\nERROR: $err\n\n";
        exit;
}

