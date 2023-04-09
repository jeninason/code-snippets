<?php
//throwaway script to pull nada info for a set list of applications from existing system
//handles both new and old formats for results

$base = dirname(dirname(__FILE__));  
set_include_path(get_include_path() . ':' . $base);
include_once('include/clisession.inc');

$fh = fopen("/path/to/output/nadaInfo-byAppid-".date("Ymd")."-full.csv", "w");

$ids = getIds();

$headerArr = array('appid','VIN', 'MSRP', 'LowRetail','AverageRetail','LowTrade','HighTrade', 'Excellent', 'VeryGood', 'Good', 'Fair', 'Poor', 'rawData');
fputcsv($fh, $headerArr);

foreach ($ids as $appid) {

	$app = Utility::getObject($dbh, 'NewApp', $appid);
	$vin = trim($app->getVehicle()->get('vin'));
	$i = Utility::lookupNadaInfoByVIN($dbh, $vin);
	if (isset($i['Values']->Excellent)) {
		$out = [$appid, $vin, $i['MSRP'], 0, 0, 0, 0, $i['Values']->Excellent, $i['Values']->VeryGood, $i['Values']->Good, $i['Values']->Fair, $i['Values']->Poor, json_encode($i) ];
	} else {	
		$out = array($appid,$vin, $i['MSRP'], $i['LowRetail'],$i['AverageRetail'],$i['LowTrade'],$i['HighTrade'], 0, 0, 0, 0, 0, json_encode($i));
	}
	fputcsv($fh, $out);

}


function getIds() {
$in = '123456
789012';


	return explode("\n", $in);
}