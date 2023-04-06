<?php
/**
 * Created by PhpStorm.
 * User: jnason
 *
 * input data should contain: firstName, lastName, address, city, state, zip, dob, ssn
 *
 */

ini_set('error_log', '/var/log/httpd/creditParser.log');


$script = array_shift($argv);
$inputFile = array_shift($argv);
$step = array_shift($argv);

if (!$step) {
	$step = 0;
}

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

/****************************************************************
 *
 *  You can strip out all the "step" stuff for prod.
 *  It was just so I could skip to the part I was testing.
 *
 * The three you need to run are getXML, parseXML, and scoreJSON
 * Step == 0 is the default
 *
 *****************************************************************/

if ($step == 0) { //this is just for testing, so we don't have to keep requesting the file from TU
	//get the XML from TU
	$xmlString = getXML($inputData);
	//process the XML to get data to write to json for producing the PDF
	$parsedOutput = parseXML($xmlString, $inputData);
	//score the output to create the json scorecard
	$scorecardData = scoreJson($parsedOutput, $inputData);

} else {
	
	if ($step == 1) { //skipping to the first step

		//process the XML to get data to write to json for producing the PDF
		$parsedOutput = parseXML($xmlString = '', $inputData, true);

		//score the output to create the json scorecard
		$scorecardData = scoreJson($parsedOutput, $inputData);
		
	} else { //skipping to the last step
		
		//this loads the json
		//score the output to create the json scorecard
		$scorecardData = scoreJson($parsedOutput = '', $inputData, true);

	}
	
}




function getXML($inputData) {
	//this class takes the array and requests the XML from TransUnion
	include('include/TransUnion.php');

    $tu = new TransUnion();
    $xml = $tu->submit($inputData);

    if (! $xml) {
    	//ERROR, no xml returned, this is unlikely
        error_log('XML is empty');
		return false;
    } elseif (!empty($xml->product->error)) {
        error_log('Credit XML is valid, but xml->product->error is not empty');
	    return false;
	}
    $xmlString = $xml->asXML();
    //save the file
    file_put_contents("response/".$inputData['lastName']."-".$inputData['ssn'].".xml", $xmlString);
	
	return $xmlString;

}


function parseXML($xmlString, $inputData, $loadFile = false) {
	include('include/TransUnionXmlParser.php');
	
    $xmlParser = new TransUnionXmlParser();

	if ($loadFile) {
	    $xmlFile = "response/".$inputData['lastName']."-".$inputData['ssn'].".xml";
	    $processedData = $xmlParser->loadFile($xmlFile);
	    
	} else {
	    $processedData = $xmlParser->process($xmlString);
	}
    //now add the input data to the processedData
	$processedData['input'] = $inputData;
	
    $jsonData = json_encode($processedData);
    
    //save the file
    file_put_contents("response/".$inputData['lastName']."-".$inputData['ssn'].".json", $jsonData);
	
	return $processedData;

}



function scoreJson($processedData, $inputData, $loadFile = false) {
	include('include/ScorecardParser.php');
	
    $scorecardParser = new ScorecardParser();

	if ($loadFile) {
	    $jsonFile = "response/".$inputData['lastName']."-".$inputData['ssn'].".json";
	    $scorecardData = $scorecardParser->loadFile($jsonFile);
	    
	} else {
	    $scorecardData = $scorecardParser->process($processedData);
	}
    //now add the input data to the processedData
	$scorecardData['input'] = $inputData;
	
    $jsonData = json_encode($scorecardData);
    
    //save the file
    file_put_contents("response/".$inputData['lastName']."-".$inputData['ssn']."-scorecard.json", $jsonData);
	
	return $scorecardData;

}


function usage($err) {
        echo "\n\nUSAGE: creditParser.php <path/to/input/json/file> <optional step number>\n";
		echo "Contents json array with: firstName, lastName, address, city, state, zip, dob, ssn\n";
        echo "\nERROR: $err\n\n";
        exit;
}

