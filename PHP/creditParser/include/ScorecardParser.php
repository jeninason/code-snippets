<?php
/**
 * Created by PhpStorm.
 * User: jnason
 */


class ScorecardParser {
	protected $environment;
	protected $ERRORS = array();
	
	public function __construct($env = 'dev') {
		$this->environment = $env;

		error_log('ScoreCardParser: START');
		
	}

	public function loadFile($jsonFile) {
		//in case this is run independently
		$processedData = [];
		$in = file_get_contents($jsonFile);
		$processedData = json_decode($in, true);

		//make sure inputdata has something
		if (! count($processedData)) {
			usage("Input file is empty ");
		}

		return $this->process($processedData);
		
	}
	
	public function process($processedData) {
		
		//this is just scoring now
		$creditReportStatus = $this->scoreCredit($processedData);
		
		return $creditReportStatus;
		
	}
	public function scoreCredit($processedData) {

		$finalStatus = 'ok';
		$output = '';
		
		$inputData = $processedData['input'];
		$mainData = $processedData['main'];
		$addOnData = $processedData['addOns'];
		$collectionData = $processedData['collection'];
		$summaryData = $processedData['summary'];
		$inquiryData = $processedData['inquiry'];
		$publicRecordData = $processedData['publicRecord'];
		$tradeData = $processedData['trade'];
		
		$riskFactorMessage = $this->riskFactors();
		
		//scoring each section
		$mainCodes = $this->scoreMainData($mainData, $inputData); //scoreCard, mlog
		$summaryCodes = $this->scoreSummary($mainData, $summaryData); //scoreCard, mlog
		$addOnCodes = $this->scoreAddOns($mainData, $addOnData); //scoreCard, mlog, deceased
		$inquiryCodes = $this->scoreInquiry($mainData, $inquiryData); //scoreCard
		$tradeCodes = $this->scoreTrades($mainData, $tradeData); //scoreCard, mlog, countRepos
		$collectionCodes = $this->scoreCollections($mainData, $collectionData); //scoreCard, mlog, countRepos
		$publicRecordCodes = $this->scorePublicRecords($mainData, $publicRecordData); //scoreCard
		
		$errorComponents = [];
		$motoleaseScore = [];
		
		//summarizing all the scoring
		$scoreCard = array_merge($mainCodes[0], $summaryCodes[0], $addOnCodes[0], $inquiryCodes[0], $tradeCodes[0], $collectionCodes[0], $publicRecordCodes[0]);
		$scoreCardStr = implode(",", $scoreCard);
		$mlog = array_merge($mainCodes[1], $summaryCodes[1], $addOnCodes[1], $tradeCodes[1], $collectionCodes[1]);
		
		$countRepos = $tradeCodes[2] + $collectionCodes[2];
		
		$decisionText = $mainCodes[2];
		
		$deceased = $addOnCodes[2];
		
		error_log("Done merging all the scorecard info, scorecardstr: " . $scoreCardStr);
		// File Hit Matching
		$fileHitIndicator = $mainData['fileHitIndicator'];
		switch ($fileHitIndicator) {
			case "regularHit":
			case "califHit":
			case "subjectHit":
			case "clear":
			case "hit":
				$mlog[] = "File Hit: " . $fileHitIndicator;
				$hitMessage = "File Hit: " . $fileHitIndicator;
				break;
			case "califNoHit":
			case "fraudHit":
			case "fraudNoHit":
			case "privateNoHit":
			case "residentialNoHit":
			case "businessNoHit":
			case "noNameNoHit":
			case "nonMailableNoHit":
				$motoleaseScore[] = 0;
				$mlog[] = "Special No Hit: " . $fileHitIndicator;
				$errorComponents[] = "no_hit";
				$hitMessage = "Special No Hit: " . $fileHitIndicator;
				break;
			case "error":
				$motoleaseScore[] = 0;
				$errorComponents[] = "no_hit";
				$mlog[] = "File Hit Error";
				$hitMessage = "File Hit ERROR: " . $fileHitIndicator;
				break;
			case "regularNoHit":
			case "noHit":
				$motoleaseScore[] = 0;
				$errorComponents[] = "no_hit";
				$mlog[] = "No Hit";
				$hitMessage = "File NO Hit: " . $fileHitIndicator;
				break;
			default:
				$hitMessage = "";
		}
		$decisionText[] = $fileHitIndicator . ": " . $hitMessage;
//		$decisionText[] = $fileSummary->ssnMatchIndicator . ": " . $ssnMessage;
//		$decisionText[] = strtoupper($addressAddOn->type) ." ". strtoupper($addressAddOn->condition) . " " . $addressAddOn->addressStatus;
//		$decisionText[] = $nameMessage;

		if (in_array("SSN-0", $scoreCard)) {
			//it matched, everything else is an ssn error
		} else {
			$motoleaseScore[] = 0;
			$errorComponents[] = "ssn_error";
			$mlog[] = "SSN ERROR: " . $mainData['ssnMatchIndicator'];
		}
		
		if (in_array("BK-3", $scoreCard)) {
			$motoleaseScore[] = 4;
			$mlog[] = "Open BK";
		}
		if (in_array(array("BK-2", "BK-1"), $scoreCard)) {
			$motoleaseScore[] = 2;
			$mlog[] = "Past Resolved or Recent BK";
		}
		
		//No collections = 1 (CL-0), open collections = 2 (CL-3 -> CL-6)
		if (in_array(array("CL-3", "CL-4", "CL-5", "CL-6"), $scoreCard)) {
			$motoleaseScore[] = 2;
			$mlog[] = "Open Collection";
		}
		
		if (in_array("SC-1", $scoreCard)) {
			$motoleaseScore[] = 4;
			$mlog[] = "No Score Reason shows person deceased";
		}

		if (in_array("CS-1", $scoreCard)) {
			$motoleaseScore[] = 4;
			$mlog[] = "There is a consumer statement";
		}
		
		if ($countRepos > 1) {
			$scoreCard[] = "AUTO-2";
		}
		
		if (in_array(array("JU-2", "JU-3"), $scoreCard)) {
			$motoleaseScore[] = 2;
			$mlog[] = "Recent or Open JU";
		}
		
		if (!in_array("SSN-0", $scoreCard)) {
			$motoleaseScore[] = 0;
			$errorComponents[] = "ssn_error";
			$mlog[] = "SSN ERROR: " . $mainData['ssnMatchIndicator'];
		}
		
		if ($mainData['score'] >= 720) {
			$motoleaseScore[] = 1;
			$mlog[] = "Score greater than 720";
		} elseif ($mainData['score'] > 0) {
			$motoleaseScore[] = 2;
			$mlog[] = "Score less than 720 and greater than 0";
		}
		
		
		$collectionArr = preg_grep('/^CL-\s.*/', $scoreCard);
		if (empty($collectionArr)) {
			$collectionCodes[] = "CL-0";
			$motoleaseScore[] = 1;
		}
		$chargeOffArr = preg_grep('/^CO-\s.*/', $scoreCard);
		if (empty($chargeOffArr)) {
			$scoreCard[] = "CO-0";
			$motoleaseScore[] = 1;
		}
		$repoArr = preg_grep('/^RP-\s.*/', $scoreCard);
		if (empty($repoArr)) {
			$scoreCard[] = "RP-0";
			$motoleaseScore[] = 1;
		}
		
		//     Finalize Motolease Score
		$finalMotoScore = max($motoleaseScore);

		$failMessage = '';
		
		$cons = array("CONS-1", "CONS-2", "CONS-3", "CONS-4");
		if (in_array($cons, $scoreCard)) { //there was a flag on the CR
			$errorComponents[] = "condition_error";
			$consArr = array_intersect($scoreCard, $cons);
			$consList = implode(", ", $consArr);
			$mlog[] = 'Overriding final motolease score ' . $finalMotoScore . ' due to ' . $consList;
			$mlog[] = 'Credit Report set to error until the conditions are removed from the credit report';
			$finalMotoScore = 0;
			$finalStatus = "error";
			$failMessage = $consList;
		} else if (in_array('ssn_error', $errorComponents)) {
			$mlog[] = 'Overriding final motolease score ' . $finalMotoScore . ' due to SSN errors';
			$mlog[] = 'Credit Report set to SSN Error, and the SSN is locked out for credit lookup. SSN requested: ' . $inputData['ssn'] . ' SSN returned: ' . $mainData['ssnConfirmed'];
			$finalMotoScore = 0;
			$finalStatus = "ssn";
			$failMessage = $mainData['ssnMatchIndicator'] . ": " . $riskFactorMessage[ $mainData['ssnMatchIndicator'] ];
		} else if ($finalMotoScore && in_array(0, $motoleaseScore)) {
			$mlog[] = 'Overriding final motolease score ' . $finalMotoScore . ' due to errors';
			$finalMotoScore = 0;
			$finalStatus = "error";
			$failMessage = $mainData['fileHitIndicator'] . ": " . $riskFactorMessage[ $mainData['fileHitIndicator'] ];
		}
		if (!empty($deceased) OR in_array("SC-1", $scoreCard)) {
			// Hard override!
			$mlog[] = 'Credit report shows person is deceased';
			$finalMotoScore = 0;
			$finalStatus = "error";
			$failMessage = 'Subject Deceased';
		}
		$errorStr = implode(",", $errorComponents);
		$finalMotoStr = implode(",", $motoleaseScore);
		$mlogStr = implode(", ", $mlog);
		$scoreLogStr = $this->riskFactorString($scoreCard);
		
		$decisionTextStr = implode(", ", $decisionText);
		$scoreCardOutput['decisionText'] = $decisionTextStr;
		$scoreCardOutput['errorComponents'] =  $errorStr;
		$scoreCardOutput['failMessage'] =  $failMessage;
		$scoreCardOutput['riskFactors'] =  implode(",", $scoreCard);
		$scoreCardOutput['riskLog'] =  $scoreLogStr;
		$scoreCardOutput['scoreLog'] =  $mlogStr;
		$scoreCardOutput['scoreComponents'] =  $finalMotoStr;
		$scoreCardOutput['status'] = $finalStatus;
		$scoreCardOutput['motoleaseScore'] = $finalMotoScore;
		$scoreCardOutput['creditScore'] = $mainData['score'];
		
		return $scoreCardOutput;
		
	}
	
	public function scoreMainData($mainData, $inputData) {
		
		$scoreCard = [];
		$mlog = [];
		$consOverride = [];
		$decisionText = [];

		$crFirstName = $mainData['firstName'];
		$crLastName = $mainData['lastName'];
		//$person = $this->getPerson();
		$crName = $crFirstName . " " . $crLastName;
		$inputName = $inputData['firstName'] . " " . $inputData['lastName'];
		
		$creditDataStatus = $mainData['creditDataStatus'];
		
		if ($creditDataStatus['suppressed'] == 'true') {
			$scoreCard[] = 'CONS-1';
		}
		if ($creditDataStatus['disputed'] == 'true') {
			$scoreCard[] = 'CONS-2';
		}
		if ($creditDataStatus['minor'] == 'true') {
			$scoreCard[] = 'CONS-3';
		}
		if ($creditDataStatus['freeze']['indicator'] == 'true') {
			$scoreCard[] = 'CONS-4';
		}
		if (empty($scoreCard)) {
			$scoreCard[] = 'CONS-0';  //nothing added yet, so no cons, show as 0
		}
		$socialSecurity = $mainData['socialSecurity'];
		if (isset($socialSecurity['decease'])) {
			$decease = $socialSecurity['decease'];
			if ($decease['deceasedFileSearched'] == 'true' && $decease['name']) {
				$scoreCard[] = 'SSN-9';
			}
		}
		
		if (strcasecmp($crName, $inputName) == 0) {
			//direct name match
			$scoreCard[] = "NAME-0";
			$decisionText[] = "NAME MATCHED";
		} else {
			//not perfect matches, now just check the last name to flag for review
			if (strtoupper($crLastName) === strtoupper($inputData['lastName'])) {
				$scoreCard[] = "NAME-0";
				$decisionText[] = "NAME PARTIAL MISMATCH. Returned: " . strtoupper($crName) . " vs Submitted: " . strtoupper($inputName);
			} else {
				$scoreCard[] = "NAME-1";
				$decisionText[] = "NAME MISMATCH. Returned: " . strtoupper($crName) . " vs Submitted: " . strtoupper($inputName);
			}
		}
		
		// SSN Matching
		switch ($mainData['ssnMatchIndicator']) {
			case "noHit":
				$scoreCard[] = "SSN-3";
				$decisionText[] = "SSN-3: No-hit returned and no match processing was performed.";
				break;
			case "exact":
				$scoreCard[] = "SSN-0";
				$decisionText[] = "SSN-0: SSN MATCHED";
				break;
			case "oneDigitDiff":
				$scoreCard[] = "SSN-1";
				$decisionText[] = "SSN-1: Difference of one digit between SSNs";
				break;
			case "twoDigitDiff":
				$scoreCard[] = "SSN-2";
				$decisionText[] = "SSN-2: Difference of two digits between SSNs";
				break;
			case "noMatch":
				$scoreCard[] = "SSN-X";
				$decisionText[] = "SSN-X: NO MATCH on SSN.";
				break;
			case "noSSN":
				$scoreCard[] = "SSN-X";
				$decisionText[] = "SSN-X: NO SSN on input or on file.";
				break;
			case "inputOnly":
				$scoreCard[] = "SSN-X";
				$decisionText[] = "SSN-X: NO SSN on file but SSN appears on input.";
				break;
			case "fileOnly":
				$scoreCard[] = "SSN-X";
				$decisionText[] = "SSN-X: No SSN on input but SSN on file.";
				break;
			default:
				$scoreCard[] = "SSN-X";
				$decisionText[] = "SSN-X: Unrecognized code for SSN Match Indicator";
		}
		
		if ($mainData['scoreReason'] == "subjectDeceased") {
			$scoreCard[] = "SC-1";
		}
		
		//check the consumer file data
		if (strlen($mainData['consumerFileData']) > 3) {
			//consumer file data contains something!
			$scoreCard[] = "CS-1";
		}
		
		return [$scoreCard, $mlog, $decisionText];
		
	}
	
	
	/**
	 * @param $mainData
	 * @param $addOnData
	 * @return array
	 */
	public function scoreAddOns($mainData, $addOnData) {
		
		$mlog = [];
		$scoreCard = [];
		$deceased = array();
		$highRiskFraud = $addOnData['highRiskFraud'];
		$addressAddOn = $addOnData['addressAddOn'];
		$vantageCredit = $addOnData['vantage'];
		$ficoCredit = $addOnData['fico'];
		$fraudCodes = $this->getFraudCodes();
		$ofacNameScreen = $addOnData['ofacNameScreen'];

		error_log("What's in high risk fraud? " . json_encode($highRiskFraud));
		
		if (!empty($highRiskFraud)) {
			
			if (!empty($highRiskFraud['@attributes']['searchStatus']) && $highRiskFraud['@attributes']['searchStatus'] == "availableClear") {
				//original version, and clear
				if (!empty($highRiskFraud['message'])) {
					//  Page 118
					$scoreCard[] = "FA-1";
					$mlog[] = $highRiskFraud['message']['code'] . ": " . $highRiskFraud['message']['text'];
				} else {
					$scoreCard[] = "FA-0";
				}
			} else {
	            $fraudToProcess = [];
				if (!isset($highRiskFraud['message']['@attributes'])):
					if (count($highRiskFraud['message']) > 1): //means there's more than one in here
						foreach ($highRiskFraud['message'] as $inside):
							$fraudToProcess[] = $inside;
						endforeach;
					endif;
				elseif (isset($highRiskFraud['message'])):
					$fraudToProcess[] = $highRiskFraud['message'];
				endif;
				if (count($fraudToProcess) > 0) {
					$scoreCard[] = "FA-1";
				}
				if (isset($highRiskFraud['inquiryHistory'])):
					$itemToInsert= [];
					if (isset($highRiskFraud['inquiryHistory']['messageCode'])):
						$itemToInsert['code'] = $highRiskFraud['inquiryHistory']['messageCode'];
					endif;
					if (isset($highRiskFraud['inquiryHistory']['@attributes']['timeframe'])):
						$itemToInsert['custom']['timeframe'] = $highRiskFraud['inquiryHistory']['@attributes']['timeframe'];
					endif;
					if (isset($highRiskFraud['inquiryHistory']['addressMatch'])):
						$itemToInsert['custom']['addressMatch'] =  $highRiskFraud['inquiryHistory']['addressMatch'];
					endif;
					if (isset($highRiskFraud['inquiryHistory']['inquiryWithCurrentInputCount'])):
						$itemToInsert['custom']['inquiryWithCurrentInputCount'] =  $highRiskFraud['inquiryHistory']['inquiryWithCurrentInputCount'];
					endif;
					if (isset($highRiskFraud['inquiryHistory']['maxInquiryCount'])):
						$itemToInsert['custom']['maxInquiryCount'] =  $highRiskFraud['inquiryHistory']['maxInquiryCount'];
					endif;
					$fraudToProcess[] = $itemToInsert;
				endif;
				foreach ($fraudToProcess as $fm):
					foreach($fm as $k => $v):
					if ($k == 'code'):
						$mlog[] = $v . ': ' . $fraudCodes[$v];
					elseif ($k == "custom"):
						foreach ($v as $k2 => $v2):
							$mlog[] = $k2 .": ".$v2;
						endforeach;
					endif;
					endforeach;
				endforeach;
				
			}
			/* SPECIAL add in for deceased, format is different */
			if (array_key_exists('decease', $highRiskFraud) && $highRiskFraud['decease']) {
				//parse out the contents to show on CR, but really all we care is that they are deceased!!
				$deceased['name'] = $highRiskFraud['decease']['name']['person']['first'] . " " . $highRiskFraud['decease']['name']['person']['last'];
				$deceased['lastResidency'] = $highRiskFraud['decease']['lastResidency']['location']['city'] . " " . $highRiskFraud['decease']['lastResidency']['location']['state'] . " " . $highRiskFraud['decease']['lastResidency']['location']['zipCode'];
				$deceased['dateOfBirth'] = $highRiskFraud['decease']['dateOfBirth'];
				$deceased['dateOfDeath'] = $highRiskFraud['decease']['dateOfDeath'];
				$scoreCard[] = "FA-1";
				$scoreCard[] = "SC-1";
			}
		} else {
			$scoreCard[] = "FA-0";
		}
	
		if ($addressAddOn) {
			$decisionText[] = strtoupper($addressAddOn['type']) . " " . strtoupper($addressAddOn['condition']) . " " . $addressAddOn['addressStatus'];
			if ($addressAddOn['type'] == "address") {
				if ($addressAddOn['condition'] == "mismatch") {
					$scoreCard[] = "ADDR-2";
				} else {
					$scoreCard[] = "ADDR-1";
				}
			} else {
				$scoreCard[] = "ADDR-0";
			}
		} else {
			$scoreCard[] = "ADDR-0";
		}
		
	
        if (!empty($ofacNameScreen['@attributes']['searchStatus']) && $ofacNameScreen['@attributes']['searchStatus'] != "clear") {
                $scoreCard[] = "OF-1";
                $mlog[] = "Ofac Name Screen: " . $ofacNameScreen['@attributes']['searchStatus'] . " - " .$ofacNameScreen['message']['text'];
        } else {
                $scoreCard[] = "OF-0";
                $mlog[] = "Ofac Name Screen Clear ";
        }
		
		return [$scoreCard, $mlog, $deceased];
		
	}
	
	public function scoreSummary($mainData, $summaryData) {
		
		$mlog = [];
		$scoreCard = [];
		
		if ($summaryData['authorizedUserCount'] > 0) {
			$scoreCard[] = "AU-1";
			$mlog[] = "User has " . $summaryData['authorizedUserCount'] . " AuthorizedUser, TE or P trade line(s)";
		}
		
		return [$scoreCard, $mlog];
		
		
	}
	
	public function scoreInquiry($mainData, $inquiryData) {
		
		$inquiryCodes = [];
		$scoreCard = [];
		//They only care about IN-3, shelve the others
		$oneYearAgo = strtotime("-1 year");
		$sixMonthsAgo = strtotime("-6 months");
		
		if (count($inquiryData) > 0) {
			foreach ($inquiryData as $inquiry) {
				$thisInquiry = strtotime($inquiry['dateInquiry']);
				if ($thisInquiry < $oneYearAgo) {
					$inquiryCodes[] = "IN-1";
				} else if ($thisInquiry < $sixMonthsAgo) {
					$inquiryCodes[] = "IN-2";
				} else {
					$inquiryCodes[] = "IN-3";
					$scoreCard[] = "IN-3";
				}
			}
		}
		if (empty($inquiryCodes)) {
			$inquiryCodes[] = "IN-0";
			$scoreCard[] = "IN-0";
		}
		
		return [$scoreCard];

	}
	
	public function scorePublicRecords($mainData, $publicRecords) {
		$bankruptcyCodes = [];
		
		$judgementOpenList = $this->getJudgementOpenList();
		$judgementClosedList = $this->getJudgementClosedList();
		$bankruptcyList = $this->getBankruptcyList();
		$bankruptcyOpenList = $this->getBankruptcyOpenList();
		
		if (count($publicRecords) > 0) {
			foreach ($publicRecords as $pr) {
				$accountType = $pr['publicRecordType'];
				$dateFiled = $pr['dateFiled'];
				
				if (in_array($accountType, $bankruptcyList)) {
					$bankruptcyStatus = substr($accountType, 1);
					if (in_array($accountType, $bankruptcyOpenList)) {
						$bankruptcyCodes[] = "BK-3";
					} else {
						if (!empty($datePaid)) {
							$dateCompare = strtotime($datePaid);
						} else {
							$dateCompare = strtotime($dateFiled);
						}
						$oneYearPast = strtotime('-1 years');
						if ($oneYearPast > $dateCompare) {
							$bankruptcyCodes[] = "BK-1";
						} else {
							$bankruptcyCodes[] = "BK-2";
						}
					}
				} else {
					if ($pr['liabilities'] > 750 && $pr['datePaid'] == '1000-01-01') {
						$judgementCodes[] = "JU-4";
					}
				}
				
				if (in_array($accountType, $judgementOpenList)) {
					//an open judgement
					$judgementCodes[] = "JU-3";
				} elseif (in_array($accountType, $judgementClosedList)) {
					if (!empty($datePaid)) {
						$dateCompare = strtotime($datePaid);
					} else {
						$dateCompare = strtotime($dateFiled);
					}
					$twoYearsPast = strtotime('-2 years');
					if ($twoYearsPast > $dateCompare) {
						$judgementCodes[] = "JU-1";
					} else {
						$judgementCodes[] = "JU-2";
					}
				}
			}
		}
		if (empty($bankruptcyCodes)) {
			$bankruptcyCodes[] = "BK-0";
		}
		if (empty($judgementCodes)) {
			$judgementCodes[] = "JU-0";
		}
		$scoreCard = array_merge($judgementCodes, $bankruptcyCodes);
		
		return [$scoreCard];
	}
	
	public function scoreCollections($mainData, $collections) {
		
		$mlog = [];
		$chargeOffCodes = [];
		$collectionCodes = [];
		$repoCodes = [];
		$countRepos = 0;
                $openCollectionTotal = 0;
		
		if (count($collections) > 0) {
			foreach ($collections as $collection) {
				
				$closedIndicator = $collection['closedIndicator'];
				//Score Card
				if ($closedIndicator == "chargeOffRepo") {
					//there are charge offs
					if (isset($collection['dateClosed'])) {
						//in the past
						$dateCompare = strtotime($collection['dateClosed']);
						$twoYearsPast = strtotime('-2 years');
						if ($twoYearsPast > $dateCompare) {
							//more than two years ago
							$chargeOffCodes[] = "CO-1";
						} else {
							//recent
							$chargeOffCodes[] = "CO-2";
						}
					} else {
						//current/open
						$currentBalance = $collection['currentBalance'];
						//add all these up!
						$openCollectionTotal += $currentBalance;
						switch ($currentBalance) {
							case ($currentBalance < 50):
								$chargeOffCodes[] = "CO-3";
								break;
							case ($currentBalance < 249):
								$chargeOffCodes[] = "CO-4";
								break;
							case ($currentBalance < 499):
								$chargeOffCodes[] = "CO-5";
								break;
							case ($currentBalance > 500):
								$chargeOffCodes[] = "CO-6";
								break;
							default:
								$chargeOffCodes[] = "CO-6";
								break;
						}
					}
					
				} else {
					if (isset($collection['dateClosed'])) {
						//in the past
						$dateCompare = strtotime($collection['dateClosed']);
						$twoYearsPast = strtotime('-2 years');
						if ($twoYearsPast > $dateCompare) {
							//more than two years ago
							$collectionCodes[] = "CL-1";
						} else {
							//recent
							$collectionCodes[] = "CL-2";
						}
					} else {
						//current/open
						$currentBalance = $collection['currentBalance'];
						//add all these up!
						$openCollectionTotal += $currentBalance;
						switch ($currentBalance) {
							case ($currentBalance < 50):
								$collectionCodes[] = "CL-3";
								break;
							case ($currentBalance < 249):
								$collectionCodes[] = "CL-4";
								break;
							case ($currentBalance < 499):
								$collectionCodes[] = "CL-5";
								break;
							case ($currentBalance > 500):
								$collectionCodes[] = "CL-6";
								break;
							default:
								$collectionCodes[] = "CL-6";
								break;
						}
						$mlog[] = "Open collection with balance: " . $collection['account'];
					}
				}
				if ($collection['accountTypeStr'] == "Auto Lease" || $collection['accountTypeStr'] == "Automobile") {
					if (isset($collection['dateClosed'])) {
						//in the past
						$dateCompare = strtotime($collection['dateClosed']);
						$oneYearPast = strtotime('-2 years');
						if ($oneYearPast > $dateCompare) {
							//more than two years ago
							$repoCodes[] = "RP-1";
						} else {
							//recent
							$countRepos++;
							$repoCodes[] = "RP-2";
						}
					} else {
						//current/open
						$countRepos++;
						$repoCodes[] = "RP-3";
					}
					$mlog[] = "Auto Collection amount: " . $collection['account'];
				}
			}
			//new code added Jan 2022 to check total of all open collections
			if ($openCollectionTotal >= 500) {
				$collectionCodes[] = "CL-7";
				$mlog[] = "Total open collections amount: " . $openCollectionTotal;
			}
		}
		$scoreCard = array_merge($repoCodes, $collectionCodes, $chargeOffCodes);
		
		return [$scoreCard, $mlog, $countRepos];
		
		
	}
	
	public function scoreTrades($mainData, $trades) {
		
		$mlog = [];
		$scoreCard = [];
		$chargeOffCodes = [];
		$repoCodes = [];
		$countRepos = 0;
		$dateFlag = 0;
		$hasAu = 0;
		$portfolioTypesArr = ["revolving" => 1, "installment" => 2, "mortgage" => 3, "open" => 4, "lineOfCredit" => 5];
		
		foreach ($trades as $ch) {
			if ($ch['closedIndicator'] == "chargeOffRepo") {
				if (isset($ch['dateClosed'])) {
					$dateCompare = strtotime($ch['dateClosed']);
					$twoYearsPast = strtotime('-2 years');
					if ($twoYearsPast > $dateCompare) {
						$chargeOffCodes[] = "CO-1";
					} else {
						$chargeOffCodes[] = "CO-2";
					}
					$mlog[] = "Closed Charge off, non auto: " . $ch['accountNumber'];
				} else {
					$currentBalance = $ch['currentBalance'];
					switch ($currentBalance) {
						case ($currentBalance < 50):
							$chargeOffCodes[] = "CO-3";
							break;
						case ($currentBalance < 249):
							$chargeOffCodes[] = "CO-4";
							break;
						case ($currentBalance < 499):
							$chargeOffCodes[] = "CO-5";
							break;
						case ($currentBalance > 500):
							$chargeOffCodes[] = "CO-6";
							break;
						default:
							$chargeOffCodes[] = "CO-6";
							break;
					}
					$mlog[] = "Charge off, non auto, account number: " . $ch['accountNumber'];
				}
				if ($ch['accountType'] == "AL" || $ch['accountType'] == "AU") {
					if (isset($ch['dateClosed'])) {
						$dateCompare = strtotime($ch['dateClosed']);
						$oneYearPast = strtotime('-2 years');
						if ($oneYearPast > $dateCompare) {
							// more than a year old
							$repoCodes[] = "RP-1";
						} else {
							$countRepos++;
							$repoCodes[] = "RP-2";
						}
					} else {
						$countRepos++;
						$repoCodes[] = "RP-3";
					}
				}
			}
			
			if ($ch['accountType'] == "AL" || $ch['accountType'] == "AU") {
				$pastDue = $ch['pastDue'];
				if (!isset($ch['dateClosed'])) {
					//only count this if it's not closed
					if ((!empty($pastDue)) && ($pastDue != 0)) {
						$scoreCard[] = "AUTO-1";
						$mlog[] = "Current auto account with past due, account number: " . $ch['accountNumber'] . " amount: " . $pastDue;
					}
				}
			}
			
			//NEW SUB SECTION
			//$ratingsCheckArr = ["2", "8A", "8P", "9B", "9P", "X", "UR"];

			$acctRatingArr = [1, "01"];
			if (! in_array($ch['accountRating'], $acctRatingArr)  && $ch['pastDue'] > 100 && $ch['dateClosed'] < '1900-01-01' ) {
				$scoreCard[] = "TR-1";
				$mlog[] = "Open Trade with Past Due of: " . $ch['pastDue'];
			}
			
			
			//added 20200924 - per conversation with Brian & Roupen
			//Revolving 3%, everything else 1% including student loans. NO review check (but tagged)
			$scheduledMonthlyPayment = $ch['scheduledMonthlyPayment'];
			$portfolioType = $ch['portfolioType'];
			if ($scheduledMonthlyPayment == 0 && $ch['currentBalance'] > 0 && empty($ch['dateClosed'])) {
				if ($portfolioType == "revolving") {
					$scheduledMonthlyPayment = $ch['currentBalance'] * 0.03;
					if ($scheduledMonthlyPayment < 10) {
						$scheduledMonthlyPayment = 10;
					}
					$scoreCard[] = "PMT-".$portfolioTypesArr[$portfolioType];
					$sclog[] = "PMT-".$portfolioTypesArr[$portfolioType].": ".ucfirst($portfolioType)." Trade with balance (".$ch['currentBalance'].") and no monthly payment. Added monthly of " . $scheduledMonthlyPayment;
					$mlog[] = ucfirst($portfolioType)." Trade with balance (".$ch['currentBalance'].") and no monthly payment. Added monthly of " . $scheduledMonthlyPayment;
				} else if ($ch['nameUnparsed'] == "AMEX") {
					$scheduledMonthlyPayment = $ch['currentBalance'] * 0.03;
					if ($scheduledMonthlyPayment < 10) {
						$scheduledMonthlyPayment = 10;
					}
					$scoreCard[] = "PMT-".$portfolioTypesArr[$portfolioType];
					$sclog[] = "PMT-".$portfolioTypesArr[$portfolioType].": ".ucfirst($portfolioType)." Trade with balance (".$ch['currentBalance'].") and no monthly payment. Added monthly of " . $scheduledMonthlyPayment;
					$mlog[] = ucfirst($portfolioType)." Trade with balance (".$ch['currentBalance'].") and no monthly payment. Added monthly of " . $scheduledMonthlyPayment;
				} else { //everyone else, student loans included, get 1% AMEX ALWAYS GETS 3%
					$scheduledMonthlyPayment = $ch['currentBalance'] * 0.01;
					if ($scheduledMonthlyPayment < 10) {
						$scheduledMonthlyPayment = 10;
					}
					$scoreCard[] = "PMT-".$portfolioTypesArr[$portfolioType];
					$sclog[] = "PMT-".$portfolioTypesArr[$portfolioType].": ".ucfirst($portfolioType)." Trade with balance (".$ch['currentBalance'].") and no monthly payment";
					$mlog[] = ucfirst($portfolioType)." Trade with balance (".$ch['currentBalance'].") and no monthly payment. Added monthly of " . $scheduledMonthlyPayment;
				}
			}
			
			$compareDate = (isset($ch['dateOpened'])) ? strtotime($ch['dateOpened']) : "";
			if ($compareDate < $mainData['inFileSince']) {
				error_log("********** DATE FLAG ****************");
				error_log("Opened: " . date("Y/m/d", $compareDate) . " and In file date: " . date("Y/m/d", $mainData['inFileSince']));
				error_log("********** DATE FLAG ****************");
				$dateFlag++;
			}
			if ($ch['ecoaDesignator'] == 'authorizedUser') {
				$hasAu++;
			}
			if ($ch['ecoaDesignator'] == 'terminated') {
				$hasAu++;
			}
			if ($ch['ecoaDesignator'] == 'participant') {
				$hasAu++;
			}
			
		}
		if ($hasAu) {
			$scoreCard[] = "AU-1";
			$mlog[] = "User has $hasAu AuthorizedUser, TE or P trade line(s)";
		}

		if ($dateFlag) {
			$scoreCard[] = "DATE-1";
		}
		
		$scoreCard = array_merge($scoreCard, $repoCodes, $chargeOffCodes);
		
		return [$scoreCard, $mlog, $countRepos];
		
		
	}
	
	public function riskFactorString($sc) {
		$riskFactorArr = [];
		$riskFactors = $this->riskFactors();
		foreach ($sc as $code) {
			$riskFactorArr[] = $riskFactors[ $code ];
		}
		
		return implode(",", $riskFactorArr);
		
	}
	
	public function riskFactors() {
		return array(
			"ADDR-0" => "No Address Alerts",
			"ADDR-1" => "Address Possible Mismatch",
			"ADDR-2" => "Address Mismatch",
			"AU-1"   => "Authorized User, Terminated or Participant trade line(s)",
			"AUTO-1" => "Current Auto Trade Past Due",
			"AUTO-2" => "Multiple Auto Related ChargeOffRepo/Collection",
			"BK-0"   => "No Bankruptcies",
			"BK-1"   => "Bankruptcy dismissed or discharged more than one year ago",
			"BK-2"   => "Bankruptcy dismissed or discharged recently",
			"BK-3"   => "Filed bankruptcy, not updated to dismissed or discharged, considered open.",
			"CL-0"   => "No Collections",
			"CL-1"   => "Collection closed > 2 years",
			"CL-2"   => "Collection closed < 2 years",
			"CL-3"   => "Collection open < 50",
			"CL-4"   => "Collection open < 249",
			"CL-5"   => "Collection open < 499",
			"CL-6"   => "Collection open > 500 or no balance",
			"CL-7"   => "Open Collections total over 500",
			"CO-0"   => "No Collection chargeoffs",
			"CO-1"   => "Collection chargeoff closed > 2 years",
			"CO-2"   => "Collection chargeoff closed < 2 years",
			"CO-3"   => "Collection chargeoff < 50 ",
			"CO-4"   => "Collection chargeoff < 249",
			"CO-5"   => "Collection chargeoff < 499",
			"CO-6"   => "Collection chargeoff > 500 OR No current balance",
			"CONS-0" => "No Conditions",
			"CONS-1" => "Suppressed Information",
			"CONS-2" => "Consumer Disputed",
			"CONS-3" => "Subject Minor",
			"CONS-4" => "Consumer Freeze",
			"CONS-5" => "Do Not Promote",
			"CS-1"   => "There is a consumer statement",
			"DATE-1" => "Has trade line(s) older than credit report",
			"FA-0"   => "High Risk Fraud Alert Available and Clear",
			"FA-1"   => "Fraud Alert",
			"FA-2"   => "Fraud Alert Deceased",
			"FA-3"   => "Fraud Alert Unknown Fraud Format",
			"IN-0"	 => "No Inquiries",
			"IN-1"   => "Inquiry over a year old",
			"IN-2"   => "Inquiry over six months and less than 1 year",
			"IN-3"   => "Inquiry within the last six months",
			"JU-0"   => "No Judgements",
			"JU-1"   => "Closed judgement more than two years ago",
			"JU-2"   => "Closed judgement less than two years ago",
			"JU-3"   => "Current open judgement",
			"JU-4"   => "Open judgement with liabilities over $750 unpaid",
			"NAME-0" => "Name Matched",
			"NAME-1" => "Name MisMatch",
			"OF-0"   => "OFAC Name Screen Clear",
			"OF-1"   => "OFAC Name Screen returned a message",
			"PMT-1"  => "Revolving Trade with balance and no monthly payment",
			"PMT-2"  => "Installment Trade with balance and no monthly payment",
			"PMT-3"  => "Mortgage Trade with balance and no monthly payment",
			"PMT-4"  => "Open Trade with balance and no monthly payment",
			"PMT-5"  => "LineOfCredit Trade with balance and no monthly payment",
			"RP-0"   => "No Repos",
			"RP-1"   => "Auto Collection closed > 2 years",
			"RP-2"   => "Auto Collection Closed < 2 years",
			"RP-3"   => "Auto Collection Open",
			"SSN-0"  => "SSN MATCHED",
			"SSN-1"  => "Difference of one digit between SSNs",
			"SSN-2"  => "Difference of two digits between SSNs",
			"SSN-3"  => "No-hit returned and no match processing was performed",
			"SSN-9"  => "Deceased",
			"SSN-X"  => "No match, no SSN on input or file, or Unrecognized code for SSN Match Indicator",
			"TR-1"   => "Current Trade Past Due over 100 and not rating 01",
		);
		
	}
	
	
	
	/**
	 * @return array
	 */
	public function getFicoFactors() {
		return $ficoFactor = array(
			'001' => 'Amount owed on accounts too high',
			'002' => 'Level of delinquency on accounts',
			'003' => 'Proportion of loan balances to loan amounts is too high',
			'004' => 'Lack of recent installment loan information',
			'005' => 'Too many accounts with balances',
			'006' => 'Too many consumer finance company accounts',
			'007' => 'Account payment history is too new to rate',
			'008' => 'Too many inquiries last 12 months',
			'009' => 'Too many accounts recently opened',
			'010' => 'Proportion of balances to credit limits on bank/national revolving or other revolving accounts is too high',
			'011' => 'Amount owed on revolving accounts is too high',
			'012' => 'Length of time revolving accounts have been established',
			'013' => 'Time since delinquency is too recent or unknown',
			'014' => 'Length of time accounts have been established',
			'015' => 'Lack of recent bank/national revolving information',
			'016' => 'Lack of recent revolving account information',
			'017' => 'No recent non- mortgage balance information',
			'018' => 'Number of accounts with delinquency',
			'019' => 'Date of last inquiry too recent',
			'020' => 'Time since derogatory public record or collection is too short',
			'021' => 'Amount past due on accounts',
			'022T' => 'Serious delinquency, derogatory public record or collection filed',
			'024' => 'No recent revolving balances',
			'027' => 'Too few accounts currently paid as agreed',
			'028' => 'Number of established accounts',
			'029' => 'No recent bank/national revolving balances',
			'030' => 'Time since most recent account opening is too short',
			'038' => 'Serious delinquency, and public record or collection filled',
			'039' => 'Serious delinquency',
			'040' => 'Derogatory public record or collection filed',
			'041' => 'No recent retail balances',
			'042' => 'Length of time since most recent consumer finance company accounts established',
			'050' => 'Lack of recent retail account information',
			'056' => 'Amount owed on retail accounts',
		);
	}
	
	/**
	 * @return array
	 */
	public function getAccountType() {
		return $accountType = array('AF' => 'Applicant/Furniture',
			'AG' => 'Collection Agency/Attorney',
			'AL' => 'Auto Lease',
			'AU' => 'Automobile',
			'AX' => 'Agricultural Loan',
			'BC' => 'Business Credit Card',
			'BL' => 'Revolving Business Lines',
			'BU' => 'Business',
			'CB' => 'Combined Credit Plan',
			'CC' => 'Credit Card',
			'CE' => 'Commercial Line of Credit',
			'CH' => 'Charge Account',
			'CI' => 'Commercial Installment Loan',
			'CO' => 'Consolidation',
			'CP' => 'Child Support',
			'CR' => 'Cond. Sales Contract; Refinance',
			'CU' => 'Telecommunications/Cellular',
			'CV' => 'Conventional Rest Estate Mortgage',
			'CY' => 'Commercial Mortgage',
			'DC' => 'Debit Card',
			'DR' => 'Deposit Account with Overdraft Protection',
			'DS' => 'Debt Counseling Service',
			'EM' => 'Employment',
			'FC' => 'Factoring Company Account',
			'FD' => 'Fraud Identify Check',
			'FE' => 'Attorney Fees',
			'FI' => 'FHA Home Improvement',
			'FL' => 'FMHA Real Estate Mortgage',
			'FM' => 'Family Support',
			'FR' => 'FHA Real Estate Mortgage',
			'FT' => 'Collection Credit Report Inquiry',
			'FX' => 'Flexible Spending Credit Card',
			'GA' => 'Government Employee Advance',
			'GE' => 'Government Fee for Services',
			'GF' => 'Government Fines',
			'GG' => 'Government Grant',
			'GO' => 'Government Overpayment',
			'GS' => 'Government Secured',
			'GU' => 'Govt. Unsecured Guar/Dir Ln',
			'GV' => 'Government',
			'HE' => 'Home Equity Loan',
			'HG' => 'Household Goods',
			'HI' => 'Home Improvement',
			'IE' => 'ID Report for Employment',
			'IS' => 'Installment Sales Contract',
			'LC' => 'Line of Credit',
			'LE' => 'Lease',
			'LI' => 'Lender-placed Insurance',
			'LN' => 'Construction Loan',
			'LS' => 'Credit Line Secured',
			'MB' => 'Manufactured Housing',
			'MD' => 'Medical Debt',
			'MH' => 'Medical/Health Care',
			'NT' => 'Note Loan',
			'PS' => 'Partly Secured',
			'RA' => 'Rental Agreement',
			'RC' => 'Returned Check',
			'RD' => 'Recreational Merchandise',
			'RE' => 'Real Estate',
			'RL' => 'Real Estate . Junior Liens',
			'RM' => 'Real Estate Mortgage',
			'SA' => 'Summary of Accounts . Same Status',
			'SC' => 'Secured Credit Card',
			'SE' => 'Secured',
			'SF' => 'Secondary Use of a Credit Report for Auto Financing',
			'SH' => 'Secured by Household Goods',
			'SI' => 'Secured Home Improvement',
			'SM' => 'Second Mortgage',
			'SO' => 'Secured by Household Goods & Collateral',
			'SR' => 'Secondary Use of a Credit Report',
			'ST' => 'Student Loan',
			'SU' => 'Spouse Support',
			'SX' => 'Secondary Use of a Credit Report for Other Financing',
			'TS' => 'Time Shared Loan',
			'UC' => 'Utility Company',
			'UK' => 'Unknown',
			'US' => 'Unsecured',
			'VM' => 'V.A. Real Estate Mortgage',
			'WT' => 'Individual Monitoring Report Inquiry',
		);
		
	}

	/**
	 * @return array
	 */
	public function getIndustryCode() {
		return $industryCode = array('A' => 'Automotive',
			'B' => 'Banks',
			'C' => 'Clothing',
			'D' => 'Department / Variety and Other Retail',
			'E' => 'Education/Employment',
			'F' => 'Finance / Personal',
			'G' => 'Groceries',
			'H' => 'Home / Office Furnishings',
			'I' => 'Insurance',
			'J' => 'Jewelry, Cameras',
			'K' => 'Contractors ',
			'L' => 'Lumber / Building Materials / Hardware',
			'M' => 'Medical / Related Health',
			'N' => 'Travel / Entertainment',
			'O' => 'Oil Companies',
			'P' => 'Personal Services Other Than Medical',
			'Q' => 'Mail Order Houses',
			'R' => 'Real Estate and Public Accommodations',
			'S' => 'Sporting Goods',
			'T' => 'Farm and Garden Supplies',
			'U' => 'Utilities and Fuel',
			'V' => 'Government',
			'W' => 'Wholesale',
			'X' => 'Advertising',
			'Y' => 'Collection Services',
			'Z' => 'Miscellaneous',
		);
	}
	/**
	 * @return array
	 */
	public function getRemarkCodes() {
		return $remarkCodes = array('AAP' => 'Loan assumed by another party',
			'ACR' => 'Account closed due to refinance',
			'ACQ' => 'Acquired from another lender',
			'ACT' => 'Account closed due to transfer',
			'AFR' => 'Account acquired by RTC/FDIC/NCUA',
			'AID' => 'Account information disputed by consumer',
			'AJP' => 'Adjustment pending',
			'AMD' => 'Active military duty',
			'AND' => 'Affected by natural/declared disaster',
			'BAL' => 'Balloon payment',
			'BCD' => 'Bankruptcy/dispute of account information/account closed by consumer',
			'BKC' => 'Bankruptcy/account closed by consumer',
			'BKD' => 'Bankruptcy/dispute of account information',
			'BKL' => 'Included in bankruptcy',
			'BKW' => 'Bankruptcy withdrawn',
			'BRC' => 'Bankruptcy/dispute resolved/consumer disagrees/ account closed by consumer',
			'BRR' => 'Bankruptcy/dispute resolved/consumer disagrees',
			'CAD' => 'Dispute of account information/closed by consumer',
			'CBC' => 'Account closed by consumer',
			'CBD' => 'Dispute resolved; consumer disagrees/account closed by consumer',
			'CBG' => 'Account closed by credit grantor',
			'CBL' => 'Chapter 7 bankruptcy',
			'CBR' => 'Chapter 11 bankruptcy',
			'CBT' => 'Chapter 12 bankruptcy',
			'CCD' => 'Account closed by consumer/Chapter 7',
			'CDC' => 'Chap. 7/dispute of account information/account closed by consumer',
			'CDD' => 'Account closed by consumer/Chapter 11',
			'CDL' => 'Chap. 7/dispute of account information',
			'CDR' => 'Chap. 11/dispute of account information',
			'CDT' => 'Chap. 12/dispute of account information',
			'CED' => 'Account closed by consumer/Chapter 12',
			'CFD' => 'Account in dispute/closed by consumer',
			'CLA' => 'Placed for collection',
			'CLB' => 'Contingent liability.corporate defaults',
			'CLO' => 'Closed',
			'CLS' => 'Credit line suspended',
			'CPB' => 'Customer pays balance in full each month',
			'CRC' => 'Chap. 11/dispute of account information/account closed by consumer',
			'CRD' => 'Chap. 7/dispute resolved/consumer disagrees/account closed by consumer',
			'CRL' => 'Chap. 7/dispute resolved/consumer disagrees',
			'CRR' => 'Chap. 11/dispute resolved/consumer disagrees/account closed by consumer',
			'CRT' => 'Chap. 12/dispute resolved/consumer disagrees/account closed by consumer',
			'CRV' => 'Chap. 11/dispute resolved/consumer disagrees',
			'CTR' => 'Account closed.transfer or refinance',
			'CTS' => 'Contact subscriber',
			'CTC' => 'Chap. 12/dispute of account information/account closed by consumer',
			'CTV' => 'Chap. 12/dispute resolved/consumer disagrees',
			'DEC' => 'Deceased',
			'DLU' => 'Deed in lieu',
			'DM' => 'Bankruptcy dismissed',
			'DRC' => 'Dispute resolved.customer disagrees',
			'DRG' => 'Dispute resolved reported by grantor',
			'ER' => 'Election of remedy',
			'ETB' => 'Early termination/balance owing',
			'ETD' => 'Early termination by default',
			'ETI' => 'Early termination/insurance loss',
			'ETO' => 'Early termination/obligation satisfied',
			'ETS' => 'Early termination/status pending',
			'FCL' => 'Foreclosure',
			'FPD' => 'Account paid, foreclosure started',
			'FPI' => 'Foreclosure initiated',
			'FRD' => 'Foreclosure, collateral sold',
			'FTB' => 'Full termination/balance owing',
			'FTO' => 'Full termination/obligation satisfied',
			'FTS' => 'Full termination/status pending',
			'INA' => 'Inactive account',
			'INP' => 'Debt being paid through insurance',
			'INS' => 'Paid by insurance',
			'IRB' => 'Involuntary repossession/balance owing',
			'IRE' => 'Involuntary repossession',
			'IRO' => 'Involuntary repossession/obligation satisfied',
			'JUG' => 'Judgement granted',
			'LA' => 'Lease Assumption',
			'MCC' => 'Managed by debt counseling service',
			'MOV' => 'No forwarding address',
			'ND' => 'No dispute',
			'NIR' => 'Student loan not in repayment',
			'NPA' => 'Now paying',
			'PAL' => 'Purchased by another lender',
			'PCL' => 'Paid collection',
			'PDD' => 'Paid by dealer',
			'PDE' => 'Payment deferred',
			'PDI' => 'Principal deferred/interest payment only',
			'PFC' => 'Account paid from collateral',
			'PLL' => 'Prepaid lease',
			'PLP' => 'Profit and loss now paying',
			'PNR' => 'First payment never received',
			'PPA' => 'Paying partial payment agreement',
			'PPD' => 'Paid by comaker',
			'PPL' => 'Paid profit and loss',
			'PRD' => 'Payroll deduction',
			'PRL' => 'Profit and loss writeoff',
			'PWG' => 'Account payment, wage garnish',
			'REA' => 'Reaffirmation of debt',
			'REP' => 'Substitute/Replacement account',
			'RFN' => 'Refinanced',
			'RPD' => 'Paid repossession',
			'RPO' => 'Repossession',
			'RRE' => 'Repossession; redeemed',
			'RVN' => 'Voluntary surrender',
			'RVR' => 'Voluntary surrender redeemed',
			'SET' => 'Settled.less than full balance',
			'SGL' => 'Claim filed with government',
			'SIL' => 'Simple interest loan',
			'SLP' => 'Student loan perm assign government',
			'SPL' => 'Single payment loan',
			'STL' => 'Credit card lost or stolen',
			'TRF' => 'Transfer',
			'TRL' => 'Transferred to another lender',
			'TTR' => 'Transferred to recovery',
			'WCD' => 'Chap. 13/dispute of account information/account closed by consumer',
			'WEP' => 'Chap. 13 bankruptcy',
			'WPC' => 'Chap. 13/account closed by consumer',
			'WPD' => 'Chap. 13/dispute of account information',
			'WRC' => 'Chap. 13/dispute resolved/consumer disagrees/account closed by consumer',
			'WRR' => 'Chap. 13/dispute resolved/consumer disagrees',
		);
		
	}

	public function getBankruptcyList() {
		return $bankruptcyList = array(
			'Chapter 11 bankruptcy dismissed',
			'Chapter 11 bankruptcy dismissed',
			'Chapter 11 bankruptcy voluntary dismissal',
			'Chapter 11 bankruptcy discharged',
			'Chapter 12 bankruptcy dismissed',
			'Chapter 12 bankruptcy filing',
			'Chapter 12 bankruptcy voluntary dismissal',
			'Chapter 12 bankruptcy discharged',
			'Chapter 13 bankruptcy dismissed',
			'Chapter 13 bankruptcy filing',
			'Chapter 13 bankruptcy voluntary dismissal',
			'Chapter 13 bankruptcy discharged',
			'Chapter 7 bankruptcy dismissed',
			'Chapter 7 bankruptcy filing',
			'Chapter 7 bankruptcy voluntary dismissal',
			'Chapter 7 bankruptcy dischaged',
			'1D', '1F', '1V', '1X', '2D', '2F', '2V', '2X', '3D', '3F', '3V', '3X', '7D', '7F', '7V', '7X',
		);
	}
	public function getBankruptcyOpenList() {
		return $bankruptcyList = array(
			'Chapter 12 bankruptcy filing',
			'Chapter 13 bankruptcy filing',
			'Chapter 7 bankruptcy filing',
			'1F', '2F', '3F', '7F',
		);
	}
	public function getJudgementOpenList() {
		return $judgementOpenList = array(
			'Civil judgement in bankruptcy',
			'Civil judgement',
			'Forcible detainer',
			'CB', 'CJ', 'FD'
		);
	}
	public function getJudgementClosedList() {
		return $judgementClosedList = array(
			'Dismissed foreclosure',
			'Dismissal of court suit',
			'Forcible detainer dismissed',
			'Judgement dismissed',
			'Paid civil judgement',
			'Judgement paid, vacated',
			'DF', 'DS', 'FF', 'JM', 'PC', 'PV'
		);
	}
	
	/**
	 * @return array
	 */
	public function getPRTypes() {
		return $PRTypes = array('AM' => 'Attachment',
			'CB' => 'Civil judgement in bankruptcy',
			'CJ' => 'Civil judgement',
			'CP' => 'Child support',
			'CS' => 'Civil suit filed',
			'DF' => 'Dismissed foreclosure',
			'DS' => 'Dismissal of court suit',
			'FC' => 'Foreclosure',
			'FD' => 'Forcible detainer',
			'FF' => 'Forcible detainer dismissed',
			'FT' => 'Federal Tax Lien',
			'GN' => 'Garnishment',
			'HA' => 'Homeowner\'s association assesment lien',
			'HF' => 'Hospital lien satisfied',
			'HL' => 'Hospital lien',
			'JL' => 'Judicial lien',
			'JM' => 'Judgement dismissed',
			'LR' => 'A lien attached to a real property',
			'ML' => 'Mechanics lien',
			'PC' => 'Paid civil judgement',
			'PF' => 'Paid federal tax lien',
			'PG' => 'Paving assessment lien',
			'PL' => 'Paid tax lien',
			'PQ' => 'Paving assessment lien satisfied',
			'PT' => 'Puerto Rico tax lien',
			'PV' => 'Judgement paid, vacated',
			'RL' => 'Release of tax lien',
			'RM' => 'Release of mechanic\'s lien',
			'RS' => 'Real estate attachment satisfied',
			'SF' => 'Satisfied foreclosure',
			'SL' => 'State tax lien',
			'TB' => 'Tax lien included in bankruptcy',
			'TC' => 'Trusteeship canceled',
			'TL' => 'Tax lien included in bankruptcy',
			'TP' => 'Trusteeship paid/state amortization satisfied',
			'TR' => 'Trusteeship paid/state amortization ',
			'TX' => 'Tax lien revived',
			'WS' => 'Water and sewer lien',
			'1D' => 'Chapter 11 bankruptcy dismissed',
			'1F' => 'Chapter 11 bankruptcy dismissed',
			'1V' => 'Chapter 11 bankruptcy voluntary dismissal',
			'1X' => 'Chapter 11 bankruptcy discharged',
			'2D' => 'Chapter 12 bankruptcy dismissed',
			'2F' => 'Chapter 12 bankruptcy filing',
			'2V' => 'Chapter 12 bankruptcy voluntary dismissal',
			'2X' => 'Chapter 12 bankruptcy discharged',
			'3D' => 'Chapter 13 bankruptcy dismissed',
			'3F' => 'Chapter 13 bankruptcy filing',
			'3V' => 'Chapter 13 bankruptcy voluntary dismissal',
			'3X' => 'Chapter 13 bankruptcy discharged',
			'7D' => 'Chapter 7 bankruptcy dismissed',
			'7F' => 'Chapter 7 bankruptcy filing',
			'7V' => 'Chapter 7 bankruptcy voluntary dismissal',
			'7X' => 'Chapter 7 bankruptcy dischaged',
		);
		
	}
	
	
	/**
	 * @return array
	 */
	public function getFraudCodes() {
		return $fraudCodes = array('0001' => 'Input/File (Current/Previous) Address Is A Mail Receiving/Forwarding Service ',
			'0002' => 'Input/File (Current/Previous) Address Is A Hotel/Motel Or Temporary Residence ',
			'0003' => 'Input/File (Current/Previous) Address Is A Credit Correction Service',
			'0004' => 'Input/File (Current/Previous) Address Is A Camp Site',
			'0005' => 'Input/File (Current/Previous) Address Is A Secretarial Service',
			'0006' => 'Input/File (Current/Previous) Address Is A Check Cashing Service',
			'0007' => 'Input/File (Current/Previous) Address Is A Restaurant /Bar/Night Club',
			'0008' => 'Input/File (Current/Previous) Address Is A Storage Facility ',
			'0009' => 'Input/File (Current/Previous) Address Is An Airport/Airfield ',
			'0010' => 'Input/File (Current/Previous) Address Is A Truck Stop',
			'0500' => 'Input/File (Current/Previous) Address Is Commercial (Default For Codes 001 - 0500)',
			'0501' => 'Input/File (Current/Previous) Address Is A Correctional Institution ',
			'0502' => 'Input/File (Current/Previous) Address Is A Hospital Or Clinic ',
			'0503' => 'Input/File (Current/Previous) Address Is A Nursing Home',
			'1000' => 'Input/File (Current/Previous) Address is Institutional (Default For Codes 0501 - 1000)',
			'1001' => 'Input/File (Current/Previous) Address Is A U.S. Post Office',
			'1500' => 'Input/File (Current/Previous) Address Is Governmental  (Default For Codes 1001 - 1500)',
			'1501' => 'Input/File (Current/Previous) Address Has Been Reported As Suspicious (POB:#) ',
			'1502' => 'Input/File (Current/Previous) Address Is A Multi-Unit Building Reported  As Suspicious (Unit: #)',
			'1503' => 'Input/File (Current/Previous) Address Has Been Reported Misused And Requires Further Investigation (Unit: #)',
			'1504' => 'Input/File (Current/Previous) Address Is A Multi-Unit Building Reported Misused And Requires Further Investigation (Unit: #)',
			'2001' => 'Input/File (Current/Previous) Address Is Reported Used In True-Name Fraud Or Credit Fraud',
			'2501' => 'Input (Current/Previous) Address Has Been Used (#) Times In The Last (30,60,90) Days On Different Inquiries',
			'2502' => 'Input/File (Current/Previous) Address Has Been Reported More Than Once (Up To 10 POB Or Unit #S)',
			'3001' => 'Input/file SSN reported as suspicious or minor SSN',
			'3003' => 'Input/file SSN reported misused and requires further investigation ',
			'3501' => 'Input/file SSN reported used in true-name fraud or credit fraud ',
			'4001' => 'Input/file SSN reported deceased',
			'4501' => 'Input/file SSN is not likely issued prior to June 2011',
			'5501' => 'Input SSN has been used (#) times in the last (30,60,90) days on different inquiries ',
			'5502' => 'Input/file SSN associated with additional subject(s) not displayed/returned ',
			'5503' => 'Input/file SSN issued within last (2,5,10) years.',
			'5504' => 'Input/file SSN issued:xxxx-xxxx; state:xx; (est. Age obtained: xx to xx)',
			'6000' => 'Input/file SSN used in death benefits claim.',
			'6001' => 'Input/file telephone number is an answering service ',
			'6002' => 'Input/file telephone number is a cellular telephone ',
			'6003' => 'Input/file telephone number is a public/pay telephone ',
			'6500' => 'Input/file telephone number is commercial',
			'7000' => 'Input/file telephone number is institutional ',
			'7500' => 'Input/file telephone number is governmental ',
			'7501' => 'Input/file telephone number reported as suspicious',
			'7503' => 'Input/file telephone number reported misused and requires further investigation',
			'8001' => 'Input/file telephone number reported used in true-name fraud or credit fraud',
			'9000' => 'Input/file telephone number requires further investigation (Default for codes 6001 . 9000)',
			'9001' => 'Input address(es), SSN and/or telephone number reported together in suspected misuse',
			'9002' => 'Input/file addresses, SSN, or telephone number reported by more than one source ',
			'9003' => 'Security alert or consumer statement on file relates to true name or credit fraud ',
			'9004' => 'Active duty alert on file',
			'9005' => 'Initial fraud alert on file',
			'9006' => 'Extended fraud victim alert on file',
			'9997' => 'Clear for all searches performed',
			'9996' => 'High Risk Fraud Alert system is partially available',
			'9998' => 'High Risk Fraud Alert system is temporarily unavailable',
			'9999' => 'High Risk Fraud Alert system access not authorized',
		);
	}
	
	/**
	 * @return array
	 */
	public function getAccountRating() {
		return $accountRating = array('01' => 'Paid or paying as agreed',
			'02' => '30 days past due',
			'03' => '60 days past due',
			'04' => '90 days past due',
			'05' => '120 days past due',
			'07' => 'Wage earner or similar plan',
			'08' => 'Repossession',
			'8A' => 'Voluntary surrender',
			'8P' => 'Payment after repossession',
			'09' => 'Charged off as bad debt',
			'9B' => 'Collection account',
			'9P' => 'Payment after charge off/collection',
			'UR' => 'Unrated or bankruptcy (remarks code will show whether the account is a bankruptcy and, if so, what type of bankruptcy)',
		);
	}
	
	
}
