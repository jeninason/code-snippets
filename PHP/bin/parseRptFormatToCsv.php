<?php
/**
 * This is to take a dot matrix printer formatted file and turn it into usable data in a csv
 * The matching for cities to counties is fuzzy, as it has to be looked up and rematched separately, but DOES NOT always match
 * There will be errors printed that need to be manually checked
 */
/**********************************************************************************************/
//comment this first part out if the file already exists, or has been edited.
$base = dirname(dirname(__FILE__));  
set_include_path(get_include_path() . ':' . $base);

include_once('include/clisession.inc');

$portfolioCount = 36;

/* the database hasn't been kept up to date! either check this number above is correct, or turn on the DB check below
$msql = "SELECT max(portfolioid) as maxPortfolio FROM InvestmentPool";
$mhdl = $dbh->prepare($msql);
$mrs = $mhdl->execute([]);
$admin = $mrs->fetchRow();
$portfolioCount = $admin['maxPortfolio'];
*/
//**************************************************//
 
$include = array(
            dirname(__DIR__));
set_include_path(implode(':',$include));

ini_set('error_log', '/path/to/logs/'.date("Ymd").'-rpt-covert.log');

$fileDate = date("Ym01");
$counties = "/path/to/rpt/files/".$fileDate."/eop/Counties-".$fileDate.".csv";

include("dbConns.php");

$sql = "SELECT fields FROM tables "; //obv not actual request
$rs = oci_parse($oracle, $sql);
if (! $rs) {
       $m = oci_error();
       echo "\nERROR WITH QUERY: " . $m['message'] . "\n";
        exit;
}
oci_execute($rs);
//write to file
$outFile = fopen($counties, "w");
$lastAppid = 0;

while ($row = oci_fetch_array($rs, OCI_ASSOC)) {
        array_map('trim', $row);
        $appid = trim($row['LSE_S']);
        if (! $appid) {
                continue;
        }
        if ($lastAppid == $appid) {
                $lastAppid = $appid;
                continue;
        }
        $lastAppid = $appid;
        $outArr = array($appid,$row['ST_S'],$row['COUNTY'],$row['CITY'],$row['ADDR'],$row['ZIP'],$row['ADD_S'],$row['CTY_S'],$row['ST_S'],$row['ZIP_S'],$row['ACT_S']);
        fputcsv($outFile, $outArr);

}
fclose($outFile);


$previouslyMissing = array();
$manualList = array();

$previousList = [];

$input = "/path/to/rpt/files/".$fileDate."/eop/";
$output = "/path/to/csv/files/" . $fileDate. "/eop/";

$existingCitiesStream = fopen($counties, "r");
$existingLeases = array();

$countyCity = array();
$appCounty = array();

$i = 0;
while (($data = fgetcsv($existingCitiesStream)) !== FALSE) {
        $appCounty[$data[0]] = $data[2];
        $countyCity[$data[1]][substr($data[3], 0, 11)] = $data[2];
        //don't use the appid as the key
        $cityTrimmed = substr($data[3],0,13);
        $appStateCityStr = preg_replace('/\s+/','', $data[0].substr($data[3],0,13));
        $existingLeases[$appStateCityStr] = array("appid"=>$data[0], "state"=>$data[1],"county"=>$data[2], "city"=>$data[3]);
        //echo "\n" . $appStateCityStr;
}
//print_r($existingLeases);
//exit();
$fileArr = array();
for ($i = 1; $i <= $portfolioCount; $i++) {
	if ($i == 23 || $i == 26) {
		continue;
	} else {
    		$fileArr['P'.$i] = "p".$i."_lpu0449a";
	}
}

$files = array();
foreach ($fileArr as $portfolioId => $filename) {
        $totalsFound = false;
        $file = file($input . $filename . ".rpt");
        $file = array_reverse($file);
        $map = array();
        $CountyTotals = array();
        $count = 0;
        $foundString = false;

        $out = fopen($output . $filename . ".csv", "w");
        fputcsv($out, array("Portfolio ID", "Appid", "StateFull", "State", "County", "City", "Taxable", "Non-Taxable", "State Sales", "County Sales",
                "City Sales", "Sales Total", "State Transit", "County Transit", "City Transit", "Transit Total"));
        echo "\nOpened: " . $output . $filename . ".csv";
        while ($count < count($file) && !$foundString) {
                $line = $file[$count];

                //main logic
                if (strpos($line, "  : ") !== false) {
                        $toWorkWith = substr($line, 0, 37);
                        list($area, $name) = explode("  : ", $toWorkWith);
                        $area = trim($area);
                        $name = trim($name);

                        if ($area == "STATE") {
                                $stateName = $name;
                                $map[$name] = array();
                                $CountyTotals[$stateName] = array();
                        } elseif ($area == "COUNTY") {
                                $countyName = $name;
                                $CountyTotals[$stateName][$countyName] = array("compare" => array("taxable" => 0, "nontaxable" => 0, "stateTax" => 0,
                                        "countyTax" => 0, "cityTax" => 0, "countyTransit" => 0, "cityTransit" => 0), "actual" => array("taxable" => 0,
                                       "nontaxable" => 0, "stateTax" => 0, "countyTax" => 0, "cityTax" => 0, "countyTransit" => 0, "cityTransit" => 0));
                                $CountyTotals[$stateName][$countyName]["actual"]["nontaxable"] = (float)trim(substr($line, 49, 13));
                                $CountyTotals[$stateName][$countyName]["actual"]["taxable"] = (float)trim(substr($line, 63, 13));
                                $CountyTotals[$stateName][$countyName]["actual"]["stateTax"] = (float)trim(substr($line, 77, 13));
                                $CountyTotals[$stateName][$countyName]["actual"]["countyTax"] = (float)trim(substr($line, 91, 13));
                                $CountyTotals[$stateName][$countyName]["actual"]["cityTax"] = (float)trim(substr($line, 105, 13));

                                $nextLine = $file[$count - 1];
                                if (strpos($nextLine, "---") !== false || strpos($nextLine, "===") !== false) {
                                        $totalCountyTransitByCounty = (float)0;
                                        $totalCityTransitByCounty = (float)0;
                                } elseif (strpos($nextLine, "Transit Tax") !== false) {
                                        //FOUND TOTAL TRANSIT TAX
                                        $CountyTotals[$stateName][$countyName]["actual"]["countyTransit"] = (float)trim(substr($nextLine, 91, 13));
                                        $CountyTotals[$stateName][$countyName]["actual"]["cityTransit"] = (float)trim(substr($nextLine, 105, 13));
                                } else {
                                        //SCREWED UP LINE MAYBE
                                        $nextLine = $file[$count - 2];
                                        if (strpos($nextLine, "Transit Tax") !== false) {
                                                //FOUND TOTAL TRANSIT TAX
                                                $CountyTotals[$stateName][$countyName]["actual"]["countyTransit"] = (float)trim(substr($nextLine, 91, 13));
                                                $CountyTotals[$stateName][$countyName]["actual"]["cityTransit"] = (float)trim(substr($nextLine, 105, 13));
                                        } else {
                                                $totalCountyTransitByCounty = (float)0;
                                                $totalCityTransitByCounty = (float)0;
                                        }
                                }

                        } elseif ($area == "CITY") {
                                $map[$stateName][trim(substr($name, 0, 13))] = $countyName;
                        }
                }

                //var_dump($map);
                if (strpos($line, "RATE%") !== false) {
                        $foundString = true;
                }
                $count++;
        }

        $count = 0;
        $file = array_reverse($file);
        $foundString = false;
        while ($count < count($file) && !$foundString) {
                $line = $file[$count];

                //First Let's find a STATE
                if (trim(substr($line, 0, 6)) == "STATE") {
                        list($stateName, $stateNameFull) = explode(":", substr($line, 10, 50));
                        $stateName = trim($stateName);
                        $stateNameFull = trim($stateNameFull);
                }

                if (isLease($line)) {
                        //CHECK IF IT HAS CITY
                        $potentialCityName = trim(substr($line, 0, 13));
                        if ($potentialCityName !== "") {
                                $cityName = $potentialCityName;
                                $cityName = trim(str_replace("*", "", $cityName));
                        }

                        //Process Lease
                        $appid = trim(substr($line, 46, 7));
                        $taxable = trim(substr($line, 77, 11));
                        $taxable == "" ? ((float)0) : $taxable;
                        $nonTaxable = trim(substr($line, 65, 11));
                        $nonTaxable == "" ? ((float)0) : $nonTaxable;
                        $stateTax = trim(substr($line, 89, 10));
                        $stateTax == "" ? ((float)0) : $stateTax;
                        $countyTax = trim(substr($line, 100, 10));
                        $countyTax == "" ? ((float)0) : $countyTax;
                        $cityTax = trim(substr($line, 111, 10));
                        $cityTax == "" ? ((float)0) : $cityTax;

                        $taxTotal = $stateTax + $countyTax + $cityTax;

                        //NEXT LOOK NEXT LINE
                        $nextLine = $file[$count + 1];
                        //CASE ## FOUND ANOTHER LEASE
                        if (isLease($nextLine)) {
                                //Don't Do A thing Just Assign Transit Taxes to 0.00
                                $stateTransit = (float)0;
                                $countyTransit = (float)0;
                                $cityTransit = (float)0;


                        } elseif (strpos($nextLine, "---") !== false || trim($nextLine) === "") {
                                //echo  $appid . " " . ($count + 1) . "\n";
                                $stateTransit = (float)0;
                                $countyTransit = (float)0;
                                $cityTransit = (float)0;
                               //CASE ## FOUND TRANSIT TAX
                        } elseif (trim(substr($nextLine, 0, 42)) == "" && trim(substr($nextLine, 89, 38)) !== "") {
                                //TRANSIT TAX EXISTS
                                $stateTransit = trim(substr($nextLine, 89, 10));
                                $countyTransit = trim(substr($nextLine, 100, 10));
                                $cityTransit = trim(substr($nextLine, 111, 10));
                        } elseif (trim(substr($nextLine, 0, 42)) != "") {
                                //THIS MEANS LINE IS BAAAD
                                $nextLine = $file[$count + 2];
                                if (!isLease($nextLine) && strpos($nextLine, "---") == false && trim(substr($nextLine, 0, 42)) == "") {
                                        //MEANS NEXT LINE WAS A TRANSIT
                                        $stateTransit = trim(substr($nextLine, 89, 10));
                                        $countyTransit = trim(substr($nextLine, 100, 10));
                                        $cityTransit = trim(substr($nextLine, 111, 10));
                                } else {
                                        $stateTransit = (float)0;
                                        $countyTransit = (float)0;
                                        $cityTransit = (float)0;
                                }
                        } else {
                                throw new \Exception("Something Went Wrong on Line: " . $count);
                        }

                        $taxTotal = $stateTax + $countyTax + $cityTax;
                        $transitTotal = $stateTransit + $countyTransit + $cityTransit;

                        //PUT TO CSV
                        //Since the appid might NOT be unique, concat of app and city
                        $appStateCityStr = preg_replace('/\s+/','', $appid.substr($cityName,0,13));
			$thisCounty = '';

                        $thisCounty = $existingLeases[$appStateCityStr]['county']; 
                        if ($thisCounty == '') {
                                echo "\nError: " . $appStateCityStr;
                                $thisCounty = $countyCity[$stateName][substr($cityName, 0, 13)];
                                if ($thisCounty == '') {
                                        echo "\nStill Error: " . $stateName . " : " . $cityName;
                                        $thisCounty = "ERROR";
                                }
                        }
		    
                    $a = array($portfolioId, $appid, $stateNameFull, $stateName, $thisCounty, $cityName, $taxable, $nonTaxable, $stateTax, $countyTax,
//                      $a = array($portfolioId, $appid, $stateNameFull, $stateName, $existingLeases[$appStateCityStr]["county"], $cityName, $taxable, $nonTaxable, $stateTax, $countyTax,
                                $cityTax, $taxTotal, $stateTransit, $countyTransit, $cityTransit, $transitTotal);
                        try {
                                        if (isset($CountyTotals[$stateNameFull][$thisCounty])) {
                                                $CountyTotals[$stateNameFull][$thisCounty]["compare"]["taxable"] += (float)$taxable;
                                                $CountyTotals[$stateNameFull][$thisCounty]["compare"]["nontaxable"] += (float)$nonTaxable;
                                                $CountyTotals[$stateNameFull][$thisCounty]["compare"]["stateTax"] += (float)$stateTax;
                                                $CountyTotals[$stateNameFull][$thisCounty]["compare"]["countyTax"] += (float)$countyTax;
                                                $CountyTotals[$stateNameFull][$thisCounty]["compare"]["cityTax"] += (float)$cityTax;
                                                $CountyTotals[$stateNameFull][$thisCounty]["compare"]["countyTransit"] += $countyTransit;
                                                $CountyTotals[$stateNameFull][$thisCounty]["compare"]["cityTransit"] += $cityTransit;

                                        } else {
						if (array_key_exists($appid, $previousList)) {
	                                            echo "\nPREVIOUS LIST: $appid: ['$stateName']['$cityName']  --> ". $previousList[$appid] ." \n\r";
						} else {
	                                            echo "\n$appid: ['$stateName']['$cityName'] --> ThisCounty: $thisCounty --> StateName: $stateName --> Substr CityName: " . substr($cityName, 0, 13) . "\n\r";
						}
                                        }

                        } catch (Exception $e) {
                                echo $e->getMessage() . "appid: " . $appid;

                        }

                        fputcsv($out, $a);
                }

                // MAIN LOGIC IS ABOVE


                if (strpos($line, "GRAND TOTALS") !== false) {
                        $foundString = true;
                }
                $count++;
        }

        fclose($out);
}

echo "\n";

function isLease(&$line) {
        if (preg_match("/^[0-9]{5,6}/", substr($line, 47, 7))) {
                return true;
        } else {
                return false;
        }
}