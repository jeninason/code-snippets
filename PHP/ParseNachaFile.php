<?php

namespace CommonBundle\Command\Utility;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ParseNachaFile extends ContainerAwareCommand {
	
	protected $TYPE = 'ParseNachaFile';
	protected $DOCS = array();
	protected $ERRORS = array();
	protected $FILES = array();
	
	protected function configure() {
		$this->setName('ParseNachaFile')
			->setDescription('Runs daily to after bash script downloads Nacha returns, processes and emails csv copies')
			->setHelp('Use this command to parse downloaded Nacha returns, no args needed');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		
		$output->writeln("Start");
		$container = $this->getContainer();
		$dbh = $container->get('dbh');
		$dbh->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
		$dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		
		$mgr = $container->get('mgr');
		$logger = $container->get('logger');
		$mailgun = $container->get('mailgun');
		$sender = $mgr->getObject('SysUser');
		$sender->set('email', '<EMAIL ADDRESS>');
		
		// EDIT RECIPIENT HERE
		$internalNotifier = $mgr->getObject('ExternalUser');
		$internalNotifier->set('name', 'Notifications');
		$internalNotifier->set('email', '<EMAIL ADDRESS>');
		
		$logger->info('STARTING ...');
		
		// EDIT FILE LOCATIONS HERE
		$inputDir = "/data/documents/auto-report/Nacha/downloaded";
		$processedDir = "/data/documents/auto-report/Nacha/processed";
		$outputDir = "/data/documents/auto-report/Nacha/output";
		$outputFilename = "Nacha-Returns-" . date("Y-m-d") . ".csv";
		
		$inputArr = scandir($inputDir);
		$filenameArr = [];
		//print_r($inputArr);
		$existingArr = scandir($processedDir);
		$processedOld = $processedDir . "/" . date("Ym");
		if (!is_dir($processedOld)) {
			$logger->info('MKDIR');
			mkdir($processedOld, 0777, TRUE);
		}
		
		//added 2022-09-28. Check to see if file exists in processed folder first
		foreach ($inputArr as $item) {
			//checking processed files
			if (!in_array($item, $existingArr)) {
				$filenameParts = explode(".", $item);
				if (isset($filenameParts[1]) && $filenameParts[1] == "txt") {
					$filenameArr[] = $item;
				}
			} else {
				//already processed, just move it
				if (substr($item, -3) == "txt") {
					rename($inputDir . "/" . $item, $processedDir . "/" . $item);
				}
			}
		}
		
		$fh = fopen($outputDir . "/" . $outputFilename, "w");
		$headerArr = ["effectiveDate", "taxid", "code", "acct", "orig", "routingNum", "acctNum", "amount", "appid", "name", "rejectcode"];
		fputcsv($fh, $headerArr);
		
		foreach ($filenameArr as $filepath) {
			
			$in = file_get_contents($inputDir . "/" . $filepath);
			$lines = explode("\n", $in);
			$outputLines = [];
			
			foreach ($lines as $line) {
				$type = substr($line, 0, 1);
				switch ($type) {
					case '5':
						$year = substr($line, 69, 2);
						$month = substr($line, 71, 2);
						$day = substr($line, 73, 2);
						
						$outputLines['effectiveDate'] = $month . "-" . $day . "-20" . $year;
						$outputLines['taxid'] = substr($line, 40, 10);
						
						break;
					
					case '6':
						$outputLines['code'] = substr($line, 1, 2);
						$outputLines['acct'] = substr($line, 3, 8) + 0;
						$outputLines['orig'] = substr($line, 79, 8);
						$outputLines['routingNum'] = substr($line, 3, 10);
						$outputLines['acctNum'] = trim(substr($line, 12, 17));
						
						$amt = substr($line, 29, 10) + 0;
						$outputLines['amount'] = round($amt / 100, 2);
						
						$appAndName = str_replace("  ", " ", trim(substr($line, 39, 53)));
						$appAndNameArr = explode(" ", $appAndName);
						
						$outputLines['appid'] = $appAndNameArr[0];
						$ctr = 0;
						$name = "";
						foreach ($appAndNameArr as $value) {
							$ctr++;
							if ($ctr == 1) { //skip the appid
								continue;
							}
							if ($value == "") {
								//nothing, skip
								continue;
							}
							if (!is_numeric($value)) {
								$name .= $value . " ";
							}
						}
						$outputLines['name'] = rtrim($name);
						break;
					
					case '7':
						$outputLines['rejectcode'] = substr($line, 3, 3);
						break;
					case '8':
						//save this record, it's done
						fputcsv($fh, $outputLines);
				}
			}
			
			
		}
		
		//put them all in a single file?
		fclose($fh);
		
		if (count($filenameArr) > 0) {
			//email the output file
			$subject = "Parsed Nacha Return Files";
			$message = "<p>Nacha returns attached</p>";
			
			$msg = $mgr->getObject('AppMessage');
			$msg->set('domain', '<DOMAIN>');
			$msg->setSender($sender);
			$msg->setTarget($internalNotifier);
			$msg->set('subject', $subject);
			$msg->set('message', $message);
			
			$attachments[] = [
				'filepath' => $outputDir . "/" . $outputFilename,
				'filename' => $outputFilename,
			];
			
			if (!$mailgun->send($msg, $attachments)) {
				$output->writeln("ERROR SENDING MESSAGE ");
				$output->writeln("ERROR: ");
			}
			
		}
		
		foreach ($filenameArr as $filepath) {
			//now move the file to processed
			rename($inputDir . "/" . $filepath, $processedDir . "/" . $filepath);
			
		}
		// do a little housekeeping? move older files into a subdir?
		foreach ($existingArr as $item2) {
			$filenameParts2 = explode(".", $item2);
			if (isset($filenameParts2[1]) && $filenameParts2[1] == "txt") {
				//it's a text file, check the date and move
				$dateStr = substr($filenameParts2[0], -8);
				if (strtotime($dateStr) < strtotime("-1 week")) {
					rename($processedDir . "/" . $item2, $processedOld . "/" . $item2);
				}
			}
		}
		
	}
	
	
}
    
