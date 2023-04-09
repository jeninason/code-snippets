<?
//usage for autoTaskRunner, php /path/to/autoTaskRunner.php <AutoTaskTaskName>
$common		= dirname(__DIR__);
$include 	= array(
                   	$common,
                   	$common . '/thirdParty',
                   	$common . '/thirdParty/phpseclib'
		);
set_include_path(implode(':',$include));
// set some limits just in case, likely no longer needed
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 1200);

$APP_ENV  = 'production';
include_once('include/clisession.inc');
include_once('include/AutoTask.class');

$name		= array_shift($argv);
$taskName	= array_shift($argv);
if (! $taskName) {
	echo("\nInvalid Task Name!\n");
	exit;
} 

$totalTasks   = 0;
$totalSuccess	= 0;
$totalError  	= 0;
$start        = time();
$startTime    = Date("m/d/Y H:i:s",$start);

$taskClass 	= 'AutoTask_' . $taskName;
$taskObj 		= Utility::getObject($dbh, $taskClass);
if (count($argv)) {
	if (! $taskObj->loadVars($argv)) {
		exit("\n\nFailed to load vars\n\n");
	}
}
$taskObj->run();