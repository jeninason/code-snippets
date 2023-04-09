<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use CommonBundle\Helper\ResponseBody;
use \Firebase\JWT\JWT;


/**
 * Endpoint for notifications from MailGun for the various GPS reports to be imported
 */
class GpsAnalyticsImportController extends Controller {
	
	
	/**
	 * Endpoint for MailGun to send the position plus GPS report
	 *
	 * @Route("/api/partner/gpsanalyticsimportpositionplus", name="gpsanalyticsimportpositionplus_webhook")
	 * @Method("POST")
	 * @Auth("")
	 * @Validation("GpsAnalyticsImport/PositionPlus")
	 */
	public function positionPlusWebhookAction() {
		
		$response = new ResponseBody();
		
		$fileLocation = '/home/documents/analytics';
		$args = $this->get('validator')->getArgs();
		$environment = $this->getParameter('environment');
		$completeStoredFile = $fileLocation . '/PositionPlus-' . $environment . '.csv';
		
		$numAttachment = $args['attachment-count'];
		if (! $numAttachment) {
			return new JsonResponse($response->ok("No file found"), $response->code);
		}

		$attachment = "";
		$attachFileName = "";
		$file = array_shift($args['files']);
		$attachment = $file['path'];
		$attachFileName = $file['filename'];
		
		$today = date("Y-m-d");

		$timerStart = microtime(TRUE);
		$counter = 0;
		$insertSql = "INSERT INTO `AnalysisGpsPositionPlus` set Vehicle_Name = ? , `Group` = ? , `Status` = ? , `Duration_Status` = ? , `Address` = ? , `latitude` = ? , `longitude` = ? , `Last_Event` = ? , `Date_Time` = ? , `Mileage` = ?  ";
		$insertHdl = $this->get('mgr')->dbh->prepare($insertSql);
		$delSqlHdl = $this->get('mgr')->dbh->prepare("DELETE FROM `AnalysisGpsPositionPlus` WHERE createdTimestamp < ?");

		if (($attachFile = fopen($attachment, 'r')) !== FALSE) {
			$delSqlHdl->execute([$today]);
			while (($insertArr = fgetcsv($attachFile, 1000, ",")) !== FALSE) {
				if ($counter == 0) { //skip header row
					$counter++;
					continue;
				}
				$insertArr[8] = date("Y-m-d H:i:s", strtotime($insertArr[8]));
				$insertHdl->execute($insertArr);
			}


			if (rename($attachment, $completeStoredFile)) {
				$this->get('logger')->info('File copied, unlinking original');
				unlink($attachment);
			} else {
				$this->get('logger')->info('File copy FAILED');
			}
			
			$timerEnd = microtime(TRUE);
			$finalTime = $timerEnd - $timerStart;
			$this->get('logger')->info("Time running: $finalTime ");

		} else {
			$this->get('logger')->critical('No attachment');
		}
		
		/* reply with the user information and jwt token */
		return new JsonResponse($response->ok("hi"), $response->code);
		
		
	}
	/**
	 * Endpoint for MailGun to send the track solid GPS report
	 *
	 * @Route("/api/partner/gpsanalyticsimporttracksolid", name="gpsanalyticsimporttracksolid_webhook")
	 * @Method("POST")
	 * @Auth("")
	 * @Validation("GpsAnalyticsImport/TrackSolid")
	 */
	public function trackSolidWebhookAction() {

		$response = new ResponseBody();

		$fileLocation = '/home/documents/analytics';
		$args = $this->get('validator')->getArgs();
		$environment = $this->getParameter('environment');

		$numAttachment = $args['attachment-count'];
		if (! $numAttachment) {
			return new JsonResponse($response->ok("No file found"), $response->code);
		}

		$attachment = "";
		$attachFileName = "";
		$fileCounter = 0;

		$file = array_shift($args['files']);
		$attachment = $file['path'];
		$attachFileName = $file['filename'];

		$today = date("Y-m-d");

		$timerStart = microtime(TRUE);
		$counter = 0;
		$inputFileType = 'Xls';
		$inputFileName = $attachment;

		/*
		 * try {
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
			} catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
			die('Error loading file: '.$e->getMessage());
		   }
		 */
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($inputFileName);
		//$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
		//$spreadsheet = $reader->load($inputFileName);

		$worksheet = $spreadsheet->getActiveSheet();

/*		$insertSql = "INSERT INTO `AnalysisGpsTrackSolid` set serialNumber = ? , imei = ? , model = ? , offlineDays = ? , offlineTime = ? , latitude = ? , longitude = ? , latlon = ? ";
		$insertHdl = $this->get('mgr')->dbh->prepare($insertSql);
		$delSqlHdl = $this->get('mgr')->dbh->prepare("DELETE FROM `AnalysisGpsTrackSolid` WHERE createdTimestamp < ?");
		$delSqlHdl->execute([$today]);
*/
		$insertSql = "INSERT INTO `AnalysisGpsTrackSolid` set serialNumber = ? , imei = ? , model = ? , offlineDays = ? , offlineTime = ? , latitude = ? , longitude = ? , latlon = ? , appid = ? ";
		$insertHdl = $this->get('mgr')->dbh->prepare($insertSql);
		$delSqlHdl = $this->get('mgr')->dbh->prepare("DELETE FROM `AnalysisGpsTrackSolid`  WHERE createdTimestamp < ?");
		$delSqlHdl->execute([$today]);
		$appidHdl = $this->get('mgr')->dbh->prepare("SELECT appid FROM  `GpsUnit`  WHERE serialNumber = ? order by modified desc limit 1");

		$sheetType = $worksheet->getCell('A1')->getValue();
		if (stristr($sheetType, 'Offline') === FALSE) {
			//it's online
			$latlonColumn = 6;
			$completeStoredFile = $fileLocation . '/TrackSolid-Online-' . $environment . '.xls';
		} else {
			$latlonColumn = 8;
			$completeStoredFile = $fileLocation . '/TrackSolid-Offline-' . $environment . '.xls';
		}

		foreach ($worksheet->getRowIterator() as $row) {
			if ($counter <= 1) { //skip header rowS
				$counter++;
				continue;
			}
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];
			foreach ($cellIterator as $cell) {
				$data[] = $cell->getValue();
			}
			//only insert some columns
			//=HYPERLINK("http://www.tracksolid.com/offlinereportcontroller/showaddressmark?language=en&lat=34.39012&lng=-79.33181","-79.33181/34.39012")
			    $striplink = explode(",", $data[$latlonColumn]);
			    $latlon = str_replace('"', '', $striplink[1]);
			    $latlon = str_replace(")", '', $latlon);
			    $latlonSplit = explode("/", $latlon);

			$appid = 0;
			if ($appidHdl->execute([$data[1]])) {
				if ($row = $appidHdl->fetch()) {
					$appid = $row['appid'];
	            		}
			}
			if ($latlonColumn == 8) {
				$offlineDate = date("Y-m-d H:i:s", strtotime($data[7]));
	            		$outRow = [$data[1], $data[2], $data[3], $data[6], $offlineDate, $latlonSplit[0], $latlonSplit[1], $latlon, $appid];
			} else {
	            		$outRow = [$data[1], $data[2], $data[3], '', '0000-00-00 00:00:00', $latlonSplit[0], $latlonSplit[1], $latlon, $appid];
			}

			$insertHdl->execute($outRow);
		}
		if (rename($attachment, $completeStoredFile)) {
			$this->get('logger')->info('File copied, unlinking original');
			unlink($attachment);
		} else {
			$this->get('logger')->info('File copy FAILED');
		}

		$timerEnd = microtime(TRUE);
		$finalTime = $timerEnd - $timerStart;
		$this->get('logger')->info("Time running: $finalTime ");



		/* reply with the user information and jwt token */
		return new JsonResponse($response->ok("hi"), $response->code);


	}
	/**
	 * Endpoint for MailGun to send the position plus GPS report
	 *
	 * @Route("/api/partner/gpsanalyticsimportpeek", name="gpsanalyticsimportpeek_webhook")
	 * @Method("POST")
	 * @Auth("")
	 * @Validation("GpsAnalyticsImport/Peek")
	 */
	public function peekWebhookAction() {
		
		$response = new ResponseBody();
		
		$fileLocation = '/home/documents/analytics';
		$args = $this->get('validator')->getArgs();
		$environment = $this->getParameter('environment');
		$completeStoredFile = $fileLocation . '/Peek-' . $environment . '.csv';
		
		$numAttachment = $args['attachment-count'];
		if (! $numAttachment) {
			return new JsonResponse($response->ok("No file found"), $response->code);
		}

		$attachment = "";
		$attachFileName = "";
		$fileCounter = 0;

		$file = array_shift($args['files']);
		$attachment = $file['path'];
		$attachFileName = $file['filename'];
		
		$today = date("Y-m-d");

		$timerStart = microtime(TRUE);
		$counter = 0;
		$outputNewArr = [];

		$insertSql = "INSERT INTO `AnalysisGpsPeek` set serialNumber = ? , VIN = ? , `group` = ? , `status` = ? , `offlineTime` = ? , appid = ? ";
		$insertHdl = $this->get('mgr')->dbh->prepare($insertSql);
		$delSqlHdl = $this->get('mgr')->dbh->prepare("DELETE FROM `AnalysisGpsPeek` WHERE createdTimestamp < ?");
		$appidHdl = $this->get('mgr')->dbh->prepare("SELECT appid FROM  `GpsUnit`  WHERE serialNumber = ? order by modified desc limit 1");

		if (($attachFile = fopen($attachment, 'r')) !== FALSE) {
			$delSqlHdl->execute([$today]);
			while (($insertArr = fgetcsv($attachFile, 1000, ",")) !== FALSE) {
				if ($counter == 0) { //skip header row
					$counter++;
					continue;
				}

				//TODO find a way for other servers to access analytics, so this can be imported directly.
				//for now, put this in motolease, and dump/load into analytics when about to run email/texting campaign
				if (strlen($insertArr[4]) > 1) {
					$insertArr[4] = date("Y-m-d H:i:s", strtotime($insertArr[4]));
				}
				$insertArr[5] = 0;
	            $serial = str_replace('"', '', $insertArr[0]);
                $serial = str_replace("=", '', $serial);
                $insertArr[0] = $serial;
				
				if ($appidHdl->execute([$serial])) {
					if ($row = $appidHdl->fetch()) {
						$insertArr[5] = $row['appid'];
			            }
				}

				$insertHdl->execute($insertArr);
			}


			if (rename($attachment, $completeStoredFile)) {
				$this->get('logger')->info('File copied, unlinking original');
				unlink($attachment);
			} else {
				$this->get('logger')->info('File copy FAILED');
			}
			
			$timerEnd = microtime(TRUE);
			$finalTime = $timerEnd - $timerStart;
			$this->get('logger')->info("Time running: $finalTime ");
			

		} else {
			$this->get('logger')->critical('No attachment');
		}
		
		/* reply with the user information and jwt token */
		return new JsonResponse($response->ok("hi"), $response->code);
		
		
	}
	/**
	 * Endpoint for MailGun to send the GoldStar Daily GPS report
	 *
	 * @Route("/api/partner/gpsanalyticsimportgoldstardaily", name="gpsanalyticsimportgoldstardaily_webhook")
	 * @Method("POST")
	 * @Auth("")
	 * @Validation("GpsAnalyticsImport/PositionPlus")
	 */
	public function goldstarDailyWebhookAction() {
		
		$response = new ResponseBody();
		
		$fileLocation = '/home/documents/analytics';
		$args = $this->get('validator')->getArgs();
		$environment = $this->getParameter('environment');
		$completeStoredFile = $fileLocation . '/GoldstarDaily-' . $environment . '.csv';
		
		$numAttachment = $args['attachment-count'];
		if (! $numAttachment) {
			return new JsonResponse($response->ok("No file found"), $response->code);
		}

		$attachment = "";
		$attachFileName = "";
		$fileCounter = 0;

		$file = array_shift($args['files']);
		$attachment = $file['path'];
		$attachFileName = $file['filename'];
		$tableName = "AnalysisGpsGoldStarDaily";
		$dateFields = [4];
		$today = date("Y-m-d");
		
		$timerStart = microtime(TRUE);
		$counter = 0;
		$outputNewArr = [];
		$insertSql = "INSERT INTO $tableName set DisplayName = ? , `Status` = ? , `Serial` = ? , Response = ? , `DateTime` = ? , `Type` = ? , `Event` = ? , Address = ? , latitude = ? , longitude = ? , appid = ? ";
		$insertHdl = $this->get('mgr')->dbh->prepare($insertSql);
		$delSqlHdl = $this->get('mgr')->dbh->prepare("DELETE FROM $tableName WHERE createdTimestamp < ?");
		$appidHdl = $this->get('mgr')->dbh->prepare("SELECT appid FROM  `GpsUnit`  WHERE serialNumber = ? order by modified desc limit 1");

		if (($attachFile = fopen($attachment, 'r')) !== FALSE) {
			$delSqlHdl->execute([$today]);
			while (($insertArr = fgetcsv($attachFile, 1000, ",")) !== FALSE) {
				if ($counter == 0) { //skip header row
					$counter++;
					continue;
				}
				//TODO find a way for other servers to access analytics, so this can be imported directly.
				$insertArr[4] = date("Y-m-d H:i:s", strtotime($insertArr[4]));
				$insertArr[10] = 0;
				if ($appidHdl->execute([$insertArr[2]])) {
					if ($row = $appidHdl->fetch()) {
						$insertArr[10] = $row['appid'];
		            }
				}
				$insertHdl->execute($insertArr);
			}


			if (rename($attachment, $completeStoredFile)) {
				$this->get('logger')->info('File copied, unlinking original');
				unlink($attachment);
			} else {
				$this->get('logger')->info('File copy FAILED');
			}
			
			$timerEnd = microtime(TRUE);
			$finalTime = $timerEnd - $timerStart;
			$this->get('logger')->info("Time running: $finalTime ");
			

		} else {
			$this->get('logger')->critical('No attachment');
		}
		
		/* reply with the user information and jwt token */
		return new JsonResponse($response->ok("hi"), $response->code);
		
		
	}
	/**
	 * Endpoint for MailGun to send the GoldStar Exception GPS report
	 *
	 * @Route("/api/partner/gpsanalyticsimportgoldstarexception", name="gpsanalyticsimportgoldstarexception_webhook")
	 * @Method("POST")
	 * @Auth("")
	 * @Validation("GpsAnalyticsImport/PositionPlus")
	 */
	public function goldstarExceptionWebhookAction() {
		
		$response = new ResponseBody();
		
		$fileLocation = '/home/documents/analytics';
		$args = $this->get('validator')->getArgs();
		$environment = $this->getParameter('environment');
		$completeStoredFile = $fileLocation . '/GoldstarException-' . $environment . '.csv';
		
		$numAttachment = $args['attachment-count'];
		if (! $numAttachment) {
			return new JsonResponse($response->ok("No file found"), $response->code);
		}
		$file = array_shift($args['files']);
		$attachment = $file['path'];
		$tableName = "AnalysisGpsGoldStarException";
		$today = date("Y-m-d");
		
		$counter = 0;
		$insertSql = "INSERT INTO $tableName set DisplayName = ? , `Serial` = ? , LastAddress = ? , LastEvent = ? , LastEventTime = ? , appid = ? ";
		$insertHdl = $this->get('mgr')->dbh->prepare($insertSql);
		$delSqlHdl = $this->get('mgr')->dbh->prepare("DELETE FROM $tableName WHERE createdTimestamp < ?");
		$appidHdl = $this->get('mgr')->dbh->prepare("SELECT appid FROM  `GpsUnit`  WHERE serialNumber = ? order by modified desc limit 1");

		if (($attachFile = fopen($attachment, 'r')) !== FALSE) {
			$delSqlHdl->execute([$today]);
			while (($insertArr = fgetcsv($attachFile, 1000, ",")) !== FALSE) {
				if ($counter == 0) { //skip header row
					$counter++;
					continue;
				}
				$insertArr[4] = date("Y-m-d H:i:s", strtotime($insertArr[4]));
				$insertArr[5] = 0;
				if ($appidHdl->execute([$insertArr[1]])) {
					if ($row = $appidHdl->fetch()) {
						$insertArr[5] = $row['appid'];
		            }
				}
				$insertHdl->execute($insertArr);
			}
			if (rename($attachment, $completeStoredFile)) {
				$this->get('logger')->info('File copied, unlinking original');
				unlink($attachment);
			} else {
				$this->get('logger')->info('File copy FAILED');
			}
	
		} else {
			$this->get('logger')->critical('No attachment');
		}
		
		return new JsonResponse($response->ok("hi"), $response->code);
		
	}

	/**
	 * Endpoint for MailGun to send the GoldStar Inventory GPS report
	 *
	 * @Route("/api/partner/gpsanalyticsimportgoldstarinventory", name="gpsanalyticsimportgoldstarinventory_webhook")
	 * @Method("POST")
	 * @Auth("")
	 * @Validation("GpsAnalyticsImport/PositionPlus")
	 */
	public function goldstarInventoryWebhookAction() {
		
		$response = new ResponseBody();
		
		$fileLocation = '/home/documents/analytics';
		$args = $this->get('validator')->getArgs();
		$environment = $this->getParameter('environment');
		$completeStoredFile = $fileLocation . '/GoldstarInventory-' . $environment . '.csv';
		
		$numAttachment = $args['attachment-count'];
		if (! $numAttachment) {
			return new JsonResponse($response->ok("No file found"), $response->code);
		}
		$file = array_shift($args['files']);
		$attachment = $file['path'];
		$tableName = "AnalysisGpsGoldStarInventory";
		$today = date("Y-m-d");
		
		$counter = 0;
		$insertSql = "INSERT INTO $tableName set `Serial` = ? , `Inventory` = ? , `Status` = ? , LastAddress = ? , LastEvent = ? , LastEventTime = ? , appid = ? ";
		$insertHdl = $this->get('mgr')->dbh->prepare($insertSql);
		$delSqlHdl = $this->get('mgr')->dbh->prepare("DELETE FROM $tableName WHERE createdTimestamp < ?");
		$appidHdl = $this->get('mgr')->dbh->prepare("SELECT appid FROM  `GpsUnit`  WHERE serialNumber = ? order by modified desc limit 1");

		if (($attachFile = fopen($attachment, 'r')) !== FALSE) {
			$delSqlHdl->execute([$today]);
			while (($insertArr = fgetcsv($attachFile, 1000, ",")) !== FALSE) {
				if ($counter == 0) { //skip header row
					$counter++;
					continue;
				}
				if ($insertArr[5] != '') {
					$insertArr[5] = date("Y-m-d H:i:s", strtotime($insertArr[5]));
				}
				$insertArr[6] = 0;
				if ($appidHdl->execute([$insertArr[0]])) {
					if ($row = $appidHdl->fetch()) {
						$insertArr[6] = $row['appid'];
		            }
				}
				$insertHdl->execute($insertArr);
			}
			if (rename($attachment, $completeStoredFile)) {
				$this->get('logger')->info('File copied, unlinking original');
				unlink($attachment);
			} else {
				$this->get('logger')->info('File copy FAILED');
			}
	
		} else {
			$this->get('logger')->critical('No attachment');
		}
		
		return new JsonResponse($response->ok("hi"), $response->code);
		
	}
	

}