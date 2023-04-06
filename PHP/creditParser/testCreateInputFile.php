<?php
/**
 * Created by PhpStorm.
 * User: jnason
 *
 * input data should contain: firstName, lastName, address, city, state, zip, dob, ssn
 *
 */

ini_set('error_log', '/var/log/httpd/testCreateInputFile.log');

/*
$inputdata = ["firstName" => "",
			"lastName" => "",
			"address" => "",
			"city" => "",
			"state" => "",
			"zip" => "",
			"dob" => "",
			"ssn" => "",
			];
*/

$inputData = ["firstName" => "MARGARET", "lastName" => "DAVIS","address" => "2525 E 104th Ave 426",
			"city" => "Thornton",
			"state" => "CO",
			"zip" => "80233",
			"dob" => "01/01/1970",
			"ssn" => "666466167",];
    $jsonData = json_encode($inputData);
    //save the file
    file_put_contents("input/".$inputData['lastName']."-".$inputData['ssn'].".json", $jsonData);

    
    
