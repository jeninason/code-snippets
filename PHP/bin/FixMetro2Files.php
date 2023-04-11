<?php
//the Metro2 files out of LeasePak do not correct flag items out for collection, or some of the statuses comment codes
//this takes the file, then line by line looks up the current app status for that month, and corrects the comment code
include("binVars.php");

$script = array_shift($argv);
$rlsDate = array_shift($argv);

if (strtolower($rlsDate) == "current") {
	$lastDayMonth = date("Y-m-d", strtotime("-1 day"));
	$rlsDate = date("Y-m", strtotime("-1 month"));
	$rlsDate .= "-01";
} else {
	$rlsDate .= "-01";
	$lastDayMonth = date("Y-m-d", strtotime($rlsDate . "-1 day"));
}
$metro2 = [];

$pathMonth = date("m", strtotime($rlsDate));
$pathYear = date("Y", strtotime($rlsDate));
$fileDate = date("ym", strtotime($rlsDate));

$basePath = "/path/to/files/Metro2/";

/* ==================== */
/* load our metro2 file */
/* ==================== */

$metro2Files = [];

$metro2Files[] = $basePath . $pathYear . "/". $pathMonth ."/metro2_file_1". $fileDate .".ext";
$metro2Files[] = $basePath . $pathYear . "/". $pathMonth ."/metro2_file_2". $fileDate .".ext";

//echo "\nRLS Date $rlsDate";
//echo "\nLast Day $lastDayMonth";
//print_r($metro2Files);

foreach ($metro2Files as $metro2File) {
	
	if (! is_file($metro2File)) { 
		usage("Missing Metro2 file");
	} else { 
		$fh = fopen($metro2File, 'r');
		while (($line = fgets($fh)) !== false) {
			$metro2[$metro2File][] = $line;
		}
		fclose($fh);

		/* make sure the metro2 file really is a metro2 file ... */
		$line1parts = explode(' ', $metro2[$metro2File][0]);
		$test = substr($metro2[$metro2File][0], 0, 10);
		$test2 = substr($metro2[$metro2File][0], 0, 20);
		$test = $line1parts[0];
		echo "\nTEST";
		echo "\n$test";
		echo "\n0656HEADER\n"; 
		if ($test !== '0656HEADER') {
			usage("Metro2 file looks fishy ..." . $metro2File);
		}
	}
}


/* ==================== */
/* load our csc data    */
/* ==================== */
//pdo is configured in binVars

$rs1 = $pdo->prepare("SELECT appid FROM tablename where snapshotDate = '".$lastDayMonth."' and code in ('CSC', 'TSI') ");
$rs1->execute([]);
$csc = [];

while ($row1 = $rs1->fetch()) {
	$csc[] = $row1['appid'];
}

/* ========================== */
/* load our internal data     */
/* ========================== */

$rs = $pdo->prepare("SELECT appid, status FROM tablename2 where snapshotDate = '".$lastDayMonth."'");
$rs->execute([]);
$rls = [];

while ($row = $rs->fetch()) {
	$rls[$row['appid']] = $row['status'];
}

/* ==================== */
/* create status arr    */
/* ==================== */
$statuses = array(
	'NCRP'	=> 'BG',
	'NCSK'	=> 'BG',
	'NP04'	=> 'BG',
	'NCVL'	=> 'BF',
	'NPRC'	=> 'BC',
	'NPST'	=> 'BD',
	'NP02'	=> 'BF',
	'NP03'	=> 'BC',
	'NREP'	=> 'BE',
	'NSET'	=> 'BE',
	'NVOL'	=> 'BE',
);

/* ==================== */
/* create output file   */
/* and audit file       */
/* ==================== */

// NOW LOOP THROUGH BOTH METRO2 FILES

foreach ($metro2Files as $metro2File) {
	$parts = explode('.', $metro2File);
	$ext = array_pop($parts);
	$parts[] = 'modified';
	$targetPath = implode('-', $parts) . '.' . $ext;
	$fh = fopen($targetPath, 'w');
	if (! $fh) {
		usage("Cannot create target file $targetPath");
	}
	$parts[] = 'LOGFILE';
	$auditPath = implode('-', $parts) . '.txt';
	$audit = fopen($auditPath, 'w');
	if (! $audit) {
		usage("Cannot create audit file $auditPath");
	}
	fwrite($audit, "APPID\tLP STATUS\tIN CSC?\tCODE\n");

	$ctr = 0;
	$recordCount = count($metro2[$metro2File]) - 2;

	foreach ($metro2[$metro2File] as $line) { //while this is technically On^2 it's there's only ever 2 metro2 files
		
		$ctr++;
		if ($ctr == 1 || $ctr > $recordCount) {
			// output the header and trailing lines directly
			fwrite($fh, $line); 
			continue;
		}

		if ($ctr % 100 == 0) {
			echo("\n$ctr ");
		}

		/* includes leading # */
		$raw = trim(substr($line, 41, 9));

		$appid = $raw;

		$status = (isset($rls[$appid])) ? $rls[$appid] : '';

		/* 
		RULES: 
			1. IF app is in CSC file, comment code is "O"
			2. ELSEIF status is in status array, use comment code from status array
			3. ELSE output line as-is
		*/
		if (in_array($appid + 0, $csc)) {
			fwrite($fh, substr($line, 0, 150) . 'O ' . substr($line, 152)); 
			fwrite($audit, "$appid\t$status\t1\tO " . PHP_EOL);
			echo('c');
		} elseif ($status == '') {
			fwrite($fh, $line); 
			fwrite($audit, "$appid\tUNKNOWN\t0\t" . PHP_EOL);
			echo('?');
		} elseif (isset($statuses[$status])) {
			fwrite($fh, substr($line, 0, 150) . $statuses[$status] . substr($line, 152));
			fwrite($audit, "$appid\t$status\t0\t" . $statuses[$status] . PHP_EOL);
			echo('r');
		} else {
			fwrite($fh, $line); 
			fwrite($audit, "$appid\t$status\t0\t" . PHP_EOL);
			echo('.');
		}
	}
	fclose($fh);
	fclose($audit);
	echo "\n\nNew File: $targetPath\nAudit File: $auditPath\n";
}

function usage($err) {
	echo "\n\nUSAGE: FixMetro2Files.php <path/to/metro2/file> <yyyy-mm> (OR current)\n";
	echo "\nERROR: $err\n\n";
	exit;
	}