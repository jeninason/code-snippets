<?php
/**
 * Created by PhpStorm.
 * User: jnason
 */


class TransUnionXmlParser {
	protected $environment;
	protected $ERRORS = array();
	
	public function __construct($env = 'dev') {
		$this->environment = $env;

		error_log('TransUnionXmlParser: START');
		
	}

	public function loadFile($xmlFile) {
		//in case this is run independently

		$xmlString = file_get_contents($xmlFile);

		//make sure inputdata has something
		if (! $xmlString) {
			usage("XML file is empty ");
		}

		return $this->process($xmlString);
		
	}
	
	
	public function process($xmlString) {


		$row = [];
		$row['xml'] = $xmlString;
		$row['creditReportDate'] = date("Y-m-d");
		$processedData = [];
		
		$processedData['main'] 		   = $this->processMain($row);
		$processedData['addOns'] 	   = $this->processAddOns($row);
		$processedData['collection']   = $this->processCollection($row);
		$processedData['summary']	   = $this->processCreditSummary($row);
		$processedData['inquiry']	   = $this->processInquiry($row);
		$processedData['publicRecord'] = $this->processPublicRecord($row);
		$processedData['trade']		   = $this->processTrade($row);

		//return
		return $processedData;
		
		
	}
	
	public function processMain($in) {
		
		$insArr = [];
		$mainData = ["consumerFileData" => '', "fileHitIndicator" => '',
			"ssnMatchIndicator" => '', "consumerStatementIndicator" => '', "creditDataStatus" => '', "akaList" => '', "name" => '', "firstName" => '', "lastName" => '',
			"address" => '', "city" => '', "state" => '', "zip" => '', "addressSinceDate" => '', "pastAddresses" => '', "dob" => '1000-01-01', "ssnConfirmed" => '',
			"employmentInformation" => '', "driversLicense" => '', "socialSecurity" => "", "idState" => '', "idNumber" => '',
			"factors" => "", "score" => "", "scoreType" => "", "scoreReason" => '',
			"cvAuto" => "", "cvAutoFactors" => "", "cvLink" => "", "cvLinkFactors" => "", "cvParams" => ""];
		$ficoFactors = $this->getFicoFactors();
		if ($in['xml']) {
			$xml = new SimpleXMLElement($in['xml']);
			$subjectRecord = $xml->product->subject->subjectRecord;
			$fileSummary = (isset($subjectRecord->fileSummary)) ? $subjectRecord->fileSummary : '';
			$indicative = (isset($subjectRecord->indicative)) ? $subjectRecord->indicative : '';
			
			if (isset($subjectRecord->consumerFileData)) {
				$mainData['consumerFileData'] = ($subjectRecord->consumerFileData);
			}
			if (!empty($fileSummary)) {
				$mainData['creditReportDate'] = date("Y-m-d");
				$mainData['fileHitIndicator'] = (string)$fileSummary->fileHitIndicator;
				$mainData['ssnMatchIndicator'] = (string)$fileSummary->ssnMatchIndicator;
				$mainData['consumerStatementIndicator'] = ((string)$fileSummary->consumerStatementIndicator == TRUE) ? 1 : 0;
				$mainData['creditDataStatus'] = $fileSummary->creditDataStatus;
				$mainData['inFileSince'] = (string)$fileSummary->inFileSinceDate;
			}
			if (!empty($indicative)) {
				$person_aka = array();
				$person_name = "";
				$person_first = "";
				$person_last = "";
				foreach ($indicative->name as $names) {
					if ((string)$names->qualifier == "alsoKnownAs") {
						if (isset($names->person->unparsed)) {
							$person_aka[] = $names->person->unparsed;
						} else {
							$person_aka[] = $names->person->first . " " . $names->person->last;
						}
					} else {
						if (isset($names->person->unparsed)) {
							$person_name = $names->person->unparsed;
						} else {
							$person_name = $names->person->first . " " . $names->person->last;
							$person_first = (string)$names->person->first;
							$person_last = (string)$names->person->last;
						}
					}
				}
				$mainData['akaList'] = $person_aka;
				$mainData['name'] = $person_name;
				$mainData['firstName'] = $person_first;
				$mainData['lastName'] = $person_last;
				$oldAddress = [];
				foreach ($indicative->address as $personAddress) {
					if ($personAddress->status == "current") {
						$mainData['address'] = (string)$personAddress->street->number . " " . (string)$personAddress->street->name . " " . (string)$personAddress->street->type;
						$mainData['city'] = (string)$personAddress->location->city;
						$mainData['state'] = (string)$personAddress->location->state;
						$mainData['zip'] = (string)$personAddress->location->zipCode;
						$mainData['addressSinceDate'] = (isset($personAddress->dateReported)) ? (string)$personAddress->dateReported : '1000-01-01';
					} else {
						$oldAddress[] = [
							"status" => (string)$personAddress->status,
							"qualifier" => (string)$personAddress->qualifier,
							"address" => (string)$personAddress->street->number . " " . (string)$personAddress->street->name . " " . (string)$personAddress->street->type,
							"city" => (string)$personAddress->location->city,
							"state" => (string)$personAddress->location->state,
							"zip" => (string)$personAddress->location->zipCode,
							"zipExtension" => (string)$personAddress->location->zipExtension,
							"dateReported" => (string)$personAddress->dateReported];
					}
				}
				
				$mainData['pastAddresses'] = $oldAddress;
				$mainData['dob'] = (!empty($indicative->dateOfBirth)) ? $indicative->dateOfBirth : '1001-01-01';
				$mainData['ssnConfirmed'] = preg_replace('/^(\d{3})(\d{2})(\d{4})$/', '$1-$2-$3', $indicative->socialSecurity->number);
				
				$empData = [];
				foreach ($indicative->employment as $emp) {
					$empData[] = ["nameUnparsed" => $emp->employer->unparsed, "occupation" => $emp->occupation, "dateOnFileSince" => $emp->dateOnFileSince, "dateHired" => $emp->dateHired];
				}
				$mainData['employmentInformation'] = $empData;

				// if (isset($indicative->driverseLicense)) {
				$mainData['driversLicense'] = $indicative->driversLicense;
				$mainData['idState'] = (string)$indicative->driversLicense->issuanceState;
				$mainData['idNumber'] = (string)$indicative->driversLicense->number;
				$mainData['socialSecurity'] = $indicative->socialSecurity;

				$cvLink = '';
				$cvLinkFactors = array();
				$cvParams = '';
				$cvParamsArray = array();
				$cvAuto = '';
				$cvAutoFactors = array();
				$creditScore = '';
				$scoreType = '';
				$factorList = '';
				
				foreach ($subjectRecord->addOnProduct as $addOnProduct) {
					switch ((string)$addOnProduct->code) {
						case '00Z10': // 00Z10 CV Link
							$cvLink = $addOnProduct->scoreModel;
							break;
						case '00V80': // 00V80 CV Auto
							$cvAuto = $addOnProduct->scoreModel;
							break;
						case '00WQ5': // 00WQ5 CV
							$cvParams = $addOnProduct->scoreModel;
							break;
						case '00P02':  /* Fico 4 */
						case '00Q88':  /* Fico 8 */
						case '00W18':  /* Fico 9 */
							// Fico Scores
							$creditScore = $addOnProduct->scoreModel;
							$scoreType = "fico";
							break;
						case '00N94':
							// 00N94 Vantage Credit
							$creditScore = $addOnProduct->scoreModel;
							$scoreType = "vantage";
							break;
					}
				}
				if (!empty($cvLink)) {
					$mainData['cvLink'] = (string)$cvLink->score->results;
					foreach ($cvLink->score->factors->factor as $f) {
						$cvLinkFactors[] = (string)$f->code;
					}
				}
				
				$mainData['cvLinkFactors'] = implode(',', $cvLinkFactors);
				
				if (!empty($cvAuto)) {
					$mainData['cvAuto'] = (string)$cvAuto->score->results;
					if (!empty($cvAuto->score->factors->factor)) {
						foreach ($cvAuto->score->factors->factor as $f) {
							$cvAutoFactors[] = (string)$f->code;
						}
					}
				}
				$mainData['cvAutoFactors'] = implode(',', $cvAutoFactors);
				
				if ($cvParams) {
					foreach ($cvParams->characteristic as $c) {
						$id = (string)$c->id;
						$val = (string)$c->value;
						$cvParamsArray[$id] = $val;
					}
				}
				$mainData['cvParams'] = $cvParamsArray;
				
				if (!empty($creditScore)) {
					$score = substr((string)$creditScore->score->results, 1);
					if (!empty($creditScore->score->factors->factor)) {
						//print_r($creditScore->score->factors->factor);
						foreach ($creditScore->score->factors->factor as $ffactor) {
							//print_r($ffactor);
							$factorList .= $ficoFactors[ (string)$ffactor->code ] . "\n";
						}
					}
					$noScoreReason = $creditScore->score->noScoreReason;
					
				}
				$mainData['score'] = $score;
				$mainData['scoreType'] = $scoreType;
				$mainData['factors'] = $factorList;
				$mainData['scoreReason'] = $noScoreReason;
			}
			
			$insArr = $mainData;
			
		}
		return $insArr;
		
	}

	
	public function processAddOns($in) {
		$insArr = array();
		if ($in['xml']) {
			$xml = new \SimpleXMLElement($in['xml']);
			$subjectRecord = $xml->product->subject->subjectRecord;
			$highRiskFraud = '';
			$fraudCodes = [];
			$fraudMessages = [];
			$addonData = array('creditorContact' => '', 'addressAddOn' => '', 'highRiskFraud' => '', 'fico' => '', 'vantage' => '', 'ofacNameScreen' => '', 'cvLink' => '', 'cvAuto' => '', 'cvParams' => '', 'cvCreditSummary' => '');
			if (!empty($subjectRecord->addOnProduct)) {
				foreach ($subjectRecord->addOnProduct as $addOnProduct) {
					switch ((string)$addOnProduct->code) {
						case '07500': // 7500 Creditor Contact
							$addonData['creditorContact'] = $addOnProduct->creditorContact;
							break;
						case '06400': // 06400 Address match
							$addonData['addressAddOn'] = ($addOnProduct->idMismatchAlert);
							break;
						case '06500': // 06500 High Risk Fraud Alert
							$addonData['highRiskFraud'] = ($addOnProduct->highRiskFraudAlert);
							$highRiskFraud = $addOnProduct->highRiskFraudAlert;
							break;
						case '00P02':  /* Fico 4 */
						case '00Q88':  /* Fico 8 */
						case '00W18':  /* Fico 9 */ // Fico Scores
							$addonData['fico'] = ($addOnProduct);
							break;
						case '00N94': // 00N94 Vantage Credit
							$addonData['vantage'] = ($addOnProduct);
							break;
						case '06800': // 06800 OFAC
							$addonData['ofacNameScreen'] = ($addOnProduct->ofacNameScreen);
							break;
						case '00Z10': // 00Z10 CV Link
							$addonData['cvLink'] = ($addOnProduct);
							break;
						case '00V80': // 00V80 CV Auto
							$addonData['cvAuto'] = ($addOnProduct);
							break;
						case '00WQ5': // 00WQ5 CV
							$addonData['cvParams'] = ($addOnProduct);
							break;
						case '07226': //mystery
							$addonData['cvCreditSummary'] = ($addOnProduct);
					}
				}
				//if it's high risk fraud, break out the codes
				if (isset($highRiskFraud['searchStatus']) && $highRiskFraud['searchStatus'] == "availableClear") {
					//original version, and clear
					if (!empty($highRiskFraud->message)) {
						$fraudCodes[] = (string)$highRiskFraud->message->code;
						$fraudMessages[] = (string)$highRiskFraud->message->code . ":" . (string)$highRiskFraud->message->text;
					}
				} elseif (!empty($highRiskFraud->message)) {
					foreach ($highRiskFraud->message as $fr) {
						$fraudCodes[] = (string)$fr->code;
						if ($fr->custom->addressMatch) {
							$fraudMessages[] = (string)$fr->code . ": AddressMatch " . (string)$fr->custom->addressMatch;
						}
					}
				} else {
					//now check if it's in the new format?
					if (!empty($highRiskFraud->inquiryHistory->messageCode)) {
						foreach ($highRiskFraud->inquiryHistory as $fr) {
							$fraudCodes[] = (string)$fr->messageCode;
							$fraudMessages[] = (string)$fr->messageCode . "|addressMatch:" . (string)$fr->addresMatch . "|inquiryWithCurrentInputCount:" . (string)$fr->inquiryWithCurrentInputCount . "|maxInquiryCount:" . (string)$fr->maxInquiryCount;
						}
					} elseif (!empty($highRiskFraud->message)) {
						
						echo "\nHigh Risk Message: " . (string)$highRiskFraud->message;
						echo "\nOther loop: " . ($highRiskFraud->message);
						foreach ($highRiskFraud->message as $fr) {
							$fraudCodes[] = (string)$fr->code;
							$fraudMessages[] = (string)$fr->code . ":" . (string)$fr->text;
						}
					}
				}
				$addonData['fraudCode'] = implode(",", $fraudCodes);
				$addonData['fraudMessage'] = implode(", ", $fraudMessages);
				
				$insArr = $addonData;
				
			}
		}
		
		return $insArr;
		
	}
	
	public function processCollection($in) {
		
		//$in is an array with creditreportid, personid, and xml
		$collectArr = array();
		if ($in['xml']) {
			$xml = new \SimpleXMLElement($in['xml']);
			$subjectRecord = $xml->product->subject->subjectRecord;
			
			if (isset($subjectRecord->custom->credit->collection)) {
				$collections = $subjectRecord->custom->credit->collection;
			}
			$accountTypes = $this->getAccountType();
			$industryCodes = $this->getIndustryCode();
			$remarkCodes = $this->getRemarkCodes();
			
			if (!empty($collections)) {
				foreach ($collections as $collection) {
					$newRow = array();
					$accountTypeStr = "";
					$accountType = "";
					if (isset($collection->account->type)) {
						$accountType = (string)$collection->account->type;
						if (isset($accountType)) {
							$accountTypeStr = $accountTypes[$accountType];
						}
					}
					$closedIndicator = (string)$collection->closedIndicator;
					//Score Card
					$newRow['ecoa'] = substr($collection->ECOADesignator, 0, 2);
					$newRow['ecoaDesignator'] = (string)$collection->ECOADesignator;
					$newRow['creditor'] = (string)$collection->subscriber->name->unparsed;
					$newRow['account'] = (string)$collection->accountNumber;
					$newRow['industry'] = $industryCodes[(string)$collection->subscriber->industryCode];
					$newRow['closedIndicator'] = $closedIndicator;
					$newRow['creditGrantor'] = (string)$collection->original->creditGrantor->unparsed;
					$newRow['creditorClass'] = (string)$collection->original->creditorClassification;
					$newRow['remarkCode'] = isset($collection->remark->code) ? $remarkCodes[(string)$collection->remark->code] : "";
					$newRow['remarkType'] = ucfirst($collection->remark->type);
					$newRow['originalBalance'] = (int)$collection->original->balance;
					$newRow['pastDue'] = (int)$collection->pastDue;
					$newRow['currentBalance'] = (int)$collection->currentBalance;
					$newRow['portfolioType'] = ucfirst($collection->portfolioType);
					$newRow['accountTypeStr'] = $accountTypeStr;
					$newRow['accountRating'] = (string)$collection->accountRating;
					$newRow['dateOpened'] = (isset($collection->dateOpened)) ? date("Y-m-d", strtotime((string)$collection->dateOpened)) : NULL;
					$newRow['dateEffective'] = (isset($collection->dateEffective)) ? date("Y-m-d", strtotime((string)$collection->dateEffective)) : NULL;
					$newRow['dateClosed'] = (isset($collection->dateClosed)) ? date("Y-m-d", strtotime((string)$collection->dateClosed)) : NULL;
					$newRow['datePaidOut'] = (isset($collection->datePaidOut)) ? date("Y-m-d", strtotime((string)$collection->datePaidOut)) : NULL;
					$newRow['dateFirstDelinquent'] = (isset($collection->dateFirstDelinquent)) ? date("Y-m-d", strtotime((string)$collection->dateFirstDelinquent)) : NULL;
					$newRow['mostRecentPayment'] = (isset($collection->mostRecentPayment->date)) ? date("Y-m-d", strtotime((string)$collection->mostRecentPayment->date)) : NULL;
					$collectArr[] = $newRow;
				}
			}
		}
		
		return $collectArr;
		
		
	}

	public function processCreditSummary($in) {
		//$in is an array with creditreportid, personid, and xml
		$insArr = array();
		if ($in['xml']) {
			$xml = new \SimpleXMLElement($in['xml']);
			$subjectRecord = $xml->product->subject->subjectRecord;
			$inFileSinceDate = ($subjectRecord->fileSummary->inFileSinceDate) ? (string)$subjectRecord->fileSummary->inFileSinceDate : '1001-01-01';
			$creditSummary = (isset($subjectRecord->custom->credit->creditSummary)) ? $subjectRecord->custom->credit->creditSummary : "";
			$creditSummaryArr = array("cs_revolving_percent" => "0", "cs_revolving_high" => "0", "cs_revolving_limit" => "0",
				"cs_revolving_balance" => "0", "cs_revolving_past" => "0", "cs_revolving_monthly" => "0", "cs_installment_high" => "0", "cs_installment_limit" => "0",
				"cs_installment_balance" => "0", "cs_installment_past" => "0", "cs_installment_monthly" => "0", "cs_open_percent" => "0", "cs_open_high" => "0",
				"cs_open_limit" => "0", "cs_open_balance" => "0", "cs_open_past" => "0", "cs_open_monthly" => "0", "cs_total_percent" => "0", "cs_total_high" => "0",
				"cs_total_limit" => "0", "cs_total_balance" => "0", "cs_total_past" => "0", "cs_total_monthly" => "0", "publicRecordCount" => "0",
				"collectionCount" => "0", "totalTradeCount" => "0", "negativeTradeCount" => "0", "historicalNegativeTradeCount" => "0",
				"historicalNegativeOccurrencesCount" => "0", "revolvingTradeCount" => "0", "installmentTradeCount" => "0", "mortgageTradeCount" => "0",
				"openTradeCount" => "0", "unspecifiedTradeCount" => "0", "totalInquiryCount" => "0", "inFileSinceDate" => "", "bankruptcyCount" => "0",
				"judgementCount" => "0", "authorizedUserCount" => "0", "satisfactoryCount" => "0",
				"openRevolvingTradeCount" => "0", "openInstallmentTradeCount" => "0", "openMortgageTradeCount" => "0", "otherTradeCount" => "0", "openOtherTradeCount" => "0",
				"rev_creditLimit" => "0", "rev_currentBalance" => "0", "rev_pastDue" => "0", "rev_estimatedSpend" => "0", "rev_currentPaymentDue" => "0", "rev_priorPaymentDue" => "0", "rev_mostRecentPaymentAmount" => "0",
				"ins_currentBalance" => "0", "ins_pastDue" => "0", "ins_currentPaymentDue" => "0", "ins_priorPaymentDue" => "0", "ins_mostRecentPaymentAmount" => "0",
				"mor_currentBalance" => "0", "mor_pastDue" => "0", "mor_currentPaymentDue" => "0", "mor_priorPaymentDue" => "0", "mor_mostRecentPaymentAmount" => "0",
				"oth_creditLimit" => "0", "oth_currentBalance" => "0", "oth_pastDue" => "0", "oth_currentPaymentDue" => "0", "oth_priorPaymentDue" => "0", "oth_mostRecentPaymentAmount" => "0",
				"tot_creditLimit" => "0", "tot_currentBalance" => "0", "tot_pastDue" => "0", "tot_estimatedSpend" => "0", "tot_currentPaymentDue" => "0", "tot_priorPaymentDue" => "0", "tot_mostRecentPaymentAmount" => "0",
				"accountDelinquencySummary" => "", "accountSummaryDetail" => "", "reportingPeriod" => "", "cvCreditSummary" => "0");
			$creditSummaryArr['cvCreditSummary'] = 0;
			if (!empty($creditSummary)) {
				$creditSummaryArr['cs_revolving_percent'] = (int)$creditSummary->revolvingAmount->percentAvailableCredit;
				$creditSummaryArr['cs_revolving_high'] = (int)$creditSummary->revolvingAmount->highCredit;
				$creditSummaryArr['cs_revolving_limit'] = (int)$creditSummary->revolvingAmount->creditLimit;
				$creditSummaryArr['cs_revolving_balance'] = (int)$creditSummary->revolvingAmount->currentBalance;
				$creditSummaryArr['cs_revolving_past'] = (int)$creditSummary->revolvingAmount->pastDue;
				$creditSummaryArr['cs_revolving_monthly'] = (int)$creditSummary->revolvingAmount->monthlyPayment;
				$creditSummaryArr['cs_installment_high'] = (int)$creditSummary->installmentAmount->highCredit;
				$creditSummaryArr['cs_installment_limit'] = (int)$creditSummary->installmentAmount->creditLimit;
				$creditSummaryArr['cs_installment_balance'] = (int)$creditSummary->installmentAmount->currentBalance;
				$creditSummaryArr['cs_installment_past'] = (int)$creditSummary->installmentAmount->pastDue;
				$creditSummaryArr['cs_installment_monthly'] = (int)$creditSummary->installmentAmount->monthlyPayment;
				$creditSummaryArr['cs_open_percent'] = (int)$creditSummary->openAmount->percentAvailableCredit;
				$creditSummaryArr['cs_open_high'] = (int)$creditSummary->openAmount->highCredit;
				$creditSummaryArr['cs_open_limit'] = (int)$creditSummary->openAmount->creditLimit;
				$creditSummaryArr['cs_open_balance'] = (int)$creditSummary->openAmount->currentBalance;
				$creditSummaryArr['cs_open_past'] = (int)$creditSummary->openAmount->pastDue;
				$creditSummaryArr['cs_open_monthly'] = (int)$creditSummary->openAmount->monthlyPayment;
				$creditSummaryArr['cs_total_percent'] = (int)$creditSummary->totalAmount->percentAvailableCredit;
				$creditSummaryArr['cs_total_high'] = (int)$creditSummary->totalAmount->highCredit;
				$creditSummaryArr['cs_total_limit'] = (int)$creditSummary->totalAmount->creditLimit;
				$creditSummaryArr['cs_total_balance'] = (int)$creditSummary->totalAmount->currentBalance;
				$creditSummaryArr['cs_total_past'] = (int)$creditSummary->totalAmount->pastDue;
				$creditSummaryArr['cs_total_monthly'] = (int)$creditSummary->totalAmount->monthlyPayment;
				$creditSummaryArr['publicRecordCount'] = (int)$creditSummary->recordCounts->publicRecordCount;
				$creditSummaryArr['collectionCount'] = (int)$creditSummary->recordCounts->collectionCount;
				$creditSummaryArr['totalTradeCount'] = (int)$creditSummary->recordCounts->totalTradeCount;
				$creditSummaryArr['negativeTradeCount'] = (int)$creditSummary->recordCounts->negativeTradeCount;
				$creditSummaryArr['historicalNegativeTradeCount'] = (int)$creditSummary->recordCounts->historicalNegativeTradeCount;
				$creditSummaryArr['historicalNegativeOccurrencesCount'] = (int)$creditSummary->recordCounts->historicalNegativeOccurrencesCount;
				$creditSummaryArr['revolvingTradeCount'] = (int)$creditSummary->recordCounts->revolvingTradeCount;
				$creditSummaryArr['installmentTradeCount'] = (int)$creditSummary->recordCounts->installmentTradeCount;
				$creditSummaryArr['mortgageTradeCount'] = (int)$creditSummary->recordCounts->mortgageTradeCount;
				$creditSummaryArr['openTradeCount'] = (int)$creditSummary->recordCounts->openTradeCount;
				$creditSummaryArr['unspecifiedTradeCount'] = (int)$creditSummary->recordCounts->unspecifiedTradeCount;
				$creditSummaryArr['totalInquiryCount'] = (int)$creditSummary->recordCounts->totalInquiryCount;
			} else {
				//echo "\nCredit SUMMARY BLANK\n";
			}
			if (!empty($subjectRecord->addOnProduct)) {
				foreach ($subjectRecord->addOnProduct as $addOnProduct) {
					if ((string)$addOnProduct->code == "07226") {
						//now get the CVCreditSummary
						$cvSummary = $addOnProduct->CVCreditSummary;//<CVCreditSummary searchStatus="noHit" />
						if ((string)$cvSummary['searchStatus'] != "noHit" && !empty($cvSummary)) {
							$creditSummaryArr['cvCreditSummary'] = 1;
							$creditSummaryArr['reportingPeriod'] = (string)$cvSummary->recordCounts['reportingPeriod'];
							$recordCounts = array("publicRecordCount" => "", "collectionCount" => "", "totalTradeCount" => "", "negativeTradeCount" => "", "historicalNegativeTradeCount" => "",
								"historicalNegativeOccurrencesCount" => "", "revolvingTradeCount" => "", "installmentTradeCount" => "", "mortgageTradeCount" => "", "openTradeCount" => "",
								"totalInquiryCount" => "", "openRevolvingTradeCount" => "", "openInstallmentTradeCount" => "", "openMortgageTradeCount" => "", "otherTradeCount" => "", "openOtherTradeCount" => "");
							$revolvingAmounts = array("creditLimit" => "", "currentBalance" => "", "pastDue" => "", "estimatedSpend" => "", "currentPaymentDue" => "", "priorPaymentDue" => "");
							$installmentAmounts = array("currentBalance" => "", "pastDue" => "", "currentPaymentDue" => "", "priorPaymentDue" => "");
							$mortgageAmounts = array("currentBalance" => "", "pastDue" => "", "currentPaymentDue" => "", "priorPaymentDue" => "");
							$otherAmounts = array("creditLimit" => "", "currentBalance" => "", "pastDue" => "", "currentPaymentDue" => "", "priorPaymentDue" => "");
							$totalAmounts = array("creditLimit" => "", "currentBalance" => "", "pastDue" => "", "estimatedSpend" => "", "currentPaymentDue" => "", "priorPaymentDue" => "");
							$accountSummaryDetails = [];
							foreach ($recordCounts as $item => $v) {
								$creditSummaryArr[$item] = ($cvSummary->recordCounts->$item == "NA") ? 0 : (int)$cvSummary->recordCounts->$item;
							}
							foreach ($revolvingAmounts as $item => $v) {
								$creditSummaryArr['rev_' . $item] = (int)$cvSummary->revolvingAmount->$item;
							}
							foreach ($installmentAmounts as $item => $v) {
								$creditSummaryArr['ins_' . $item] = (int)$cvSummary->installmentAmount->$item;
							}
							foreach ($mortgageAmounts as $item => $v) {
								$creditSummaryArr['mor_' . $item] = (int)$cvSummary->mortgageAmount->$item;
							}
							foreach ($otherAmounts as $item => $v) {
								$creditSummaryArr['oth_' . $item] = (int)$cvSummary->otherAmount->$item;
							}
							foreach ($totalAmounts as $item => $v) {
								$creditSummaryArr['tot_' . $item] = (int)$cvSummary->totalAmount->$item;
							}
							//additional sub field amount:
							$creditSummaryArr['rev_mostRecentPaymentAmount'] = (!empty($cvSummary->revolvingAmount->mostRecentPayment) && $cvSummary->revolvingAmount->mostRecentPayment->amount != "NA") ? (int)$cvSummary->revolvingAmount->mostRecentPayment->amount : 0;
							$creditSummaryArr['ins_mostRecentPaymentAmount'] = (!empty($cvSummary->installmentAmount->mostRecentPayment) && $cvSummary->installmentAmount->mostRecentPayment->amount != "NA") ? (int)$cvSummary->installmentAmount->mostRecentPayment->amount : 0;
							$creditSummaryArr['mor_mostRecentPaymentAmount'] = (!empty($cvSummary->mortgageAmount->mostRecentPayment) && $cvSummary->mortgageAmount->mostRecentPayment->amount != "NA") ? (int)$cvSummary->mortgageAmount->mostRecentPayment->amount : 0;
							$creditSummaryArr['oth_mostRecentPaymentAmount'] = (!empty($cvSummary->otherAmount->mostRecentPayment) && $cvSummary->otherAmount->mostRecentPayment->amount != "NA") ? (int)$cvSummary->otherAmount->mostRecentPayment->amount : 0;
							$creditSummaryArr['tot_mostRecentPaymentAmount'] = (!empty($cvSummary->totalAmount->mostRecentPayment) && $cvSummary->totalAmount->mostRecentPayment->amount != "NA") ? (int)$cvSummary->totalAmount->mostRecentPayment->amount : 0;
							$creditSummaryArr['accountDelinquencySummary'] = ($cvSummary->accountDelinquencySummary);
							//no, there are many of these!
							if (!empty($cvSummary->accountSummaryDetail)) {
								foreach ($cvSummary->accountSummaryDetail as $item) {
									$accountSummaryDetails[] = $item;
								}
							}
							$creditSummaryArr['accountSummaryDetail'] = ($accountSummaryDetails);
						}
					}
				}
			}
			$creditHistory = '';
			if (isset($subjectRecord->custom->credit->trade)) {
				$creditHistory = $subjectRecord->custom->credit->trade;
			}
			//$creditHistory = $xml->product->subject->subjectRecord->custom->credit->trade;
			$satisfactories = 0;
			$hasAu = 0;
			$bankruptcyCount = 0;
			$judgementCount = 0;
			if (!empty($creditHistory)) {
				foreach ($creditHistory as $ch) {
					$late30 = (int)$ch->paymentHistory->historicalCounters->late30DaysTotal;
					$late60 = (int)$ch->paymentHistory->historicalCounters->late60DaysTotal;
					$late90 = (int)$ch->paymentHistory->historicalCounters->late90DaysTotal;
					if (($late30 + $late60 + $late90) == 0) {
						$satisfactories++;
					}
					if ((string)$ch->ECOADesignator == 'authorizedUser') {
						$hasAu++;
					}
					if ((string)$ch->ECOADesignator == 'terminated') {
						$hasAu++;
					}
					if ((string)$ch->ECOADesignator == 'participant') {
						$hasAu++;
					}
				}
			}
			$creditSummaryArr['satisfactoryCount'] = $satisfactories;
			$creditSummaryArr['authorizedUserCount'] = $hasAu;

//			$publicRecords = $xml->product->subject->subjectRecord->custom->credit->publicRecord;
			$publicRecords = '';
			if (isset($subjectRecord->custom->credit->publicRecord)) {
				$publicRecords = $subjectRecord->custom->credit->publicRecord;
			}
			
			
			if (!empty($publicRecords)) {
				
				$judgementOpenList = $this->getJudgementOpenList();
				$judgementClosedList = $this->getJudgementClosedList();
				$bankruptcyList = $this->getBankruptcyList();

				foreach ($publicRecords as $publicRecord) {
					if (in_array((string)$publicRecord->type, $bankruptcyList)) {
						$bankruptcyCount++;
					}
					if (in_array((string)$publicRecord->type, $judgementOpenList)) {
						$judgementCount++;
					}
					if (in_array((string)$publicRecord->type, $judgementClosedList)) {
						$judgementCount++;
					}
					
				}
			}
			$creditSummaryArr['bankruptcyCount'] = $bankruptcyCount;
			$creditSummaryArr['judgementCount'] = $judgementCount;
			$creditSummaryArr['inFileSinceDate'] = $inFileSinceDate;
			$insArr = $creditSummaryArr;
			
		}
		return $insArr;
		
	}

	public function processInquiry($in) {
		//$in is an array with creditreportid, personid, and xml
		$inqArr = array();
		if ($in['xml']) {
			$xml = new \SimpleXMLElement($in['xml']);
			$subjectRecord = $xml->product->subject->subjectRecord;
			if (isset($subjectRecord->custom->credit->inquiry)) {
				$inquiries = $subjectRecord->custom->credit->inquiry;
			}
			$industryCodes = $this->getIndustryCode();
			if (!empty($inquiries)) {
				foreach ($inquiries as $inquiry) {
					$insArr = array();
					$insArr['ecoa'] = substr($inquiry->ECOADesignator, 0, 2);
					$insArr['ecoaDesignator'] = (string)$inquiry->ECOADesignator;
					$insArr['nameUnparsed'] = $inquiry->subscriber->name->unparsed;
					$insArr['memberCode'] = $inquiry->subscriber->memberCode;
					$insArr['industryCode'] = $industryCodes[(string)$inquiry->subscriber->industryCode];
					$insArr['dateInquiry'] = (isset($inquiry->date)) ? date("Y-m-d", strtotime((string)$inquiry->date)) : "";
					$inqArr[] = $insArr;
				}
			}
		}
		
		return $inqArr;
		
	}

	public function processPublicRecord($in) {
		//EXTRA count the bankruptcy and judgement
		
		
		//$in is an array with creditreportid, personid, and xml
		$publicRecordArr = array();
		if ($in['xml']) {
			$xml = new \SimpleXMLElement($in['xml']);
			$subjectRecord = $xml->product->subject->subjectRecord;
			if (isset($subjectRecord->custom->credit->publicRecord)) {
				$publicRecords = $subjectRecord->custom->credit->publicRecord;
			}
			$industryCodes = $this->getIndustryCode();
			$remarkCodes = $this->getRemarkCodes();
			$PRTypes = $this->getPRTypes();
			$judgementOpenList = $this->getJudgementOpenList();
			$judgementClosedList = $this->getJudgementClosedList();
			$bankruptcyList = $this->getBankruptcyList();

			if (!empty($publicRecords)) {
				foreach ($publicRecords as $publicRecord) {
					$newRow = array();;
					$accountType = (string)$publicRecord->type;
					$datePaid = (string)$publicRecord->datePaid;
					$dateFiled = (string)$publicRecord->dateFiled;
					$newRow['ecoa'] = substr($publicRecord->ECOADesignator, 0, 2);
					$newRow['ecoaDesignator'] = $publicRecord->ECOADesignator;
					$newRow['publicRecordType'] = $PRTypes[(string)$publicRecord->type];
					$newRow['memberCode'] = (string)$publicRecord->subscriber->memberCode;
					$newRow['industryCode'] = $industryCodes[(string)$publicRecord->subscriber->industryCode];
					$newRow['docketNumber'] = (string)$publicRecord->docketNumber;
					$newRow['attorney'] = (string)$publicRecord->attorney;
					$newRow['plaintiff'] = (string)$publicRecord->plaintiff;
					$newRow['dateFiled'] = (isset($publicRecord->dateFiled)) ? date("Y-m-d", strtotime((string)$publicRecord->dateFiled)) : "1000-01-01";
					$newRow['liabilities'] = (int)$publicRecord->liabilities;
					$newRow['court'] = ucfirst((string)$publicRecord->source->type);
					$newRow['datePaid'] = (isset($publicRecord->datePaid)) ? date("Y-m-d", strtotime((string)$publicRecord->datePaid)) : "1000-01-01";
					$newRow['dateReported'] = (isset($publicRecord->dateReported)) ? date("Y-m-d", strtotime((string)$publicRecord->dateReported)) : "1000-01-01";
					$publicRecordArr[] = $newRow;
				}
			}
		}
		
		return $publicRecordArr;
	}
	
	public function processTrade($in) {
		$insArr = array();
		if ($in['xml']) {
			$xml = new \SimpleXMLElement($in['xml']);
			$subjectRecord = $xml->product->subject->subjectRecord;
			$inFileSinceDate = (string)$subjectRecord->fileSummary->inFileSinceDate;
			if (isset($subjectRecord->custom->credit->trade)) {
				$creditHistory = $subjectRecord->custom->credit->trade;
			}
			$realEstateList = ["CV", "FL", "HE", "MB", "RA", "RE", "RL", "RM", "SM", "VM"];
			$accountTypes = $this->getAccountType();
			
			if (!empty($creditHistory)) {
				foreach ($creditHistory as $ch) {
					$newRow = array("industryCode" => "", "memberCode" => "", "nameUnparsed" => "",
						"portfolioType" => "", "accountNumber" => "", "ecoaDesignator" => "", "dateOpened" => "", "dateEffective" => "", "dateClosed" => "", "closedIndicator" => "", "currentBalance" => "",
						"highCredit" => "", "highLimit" => "", "accountRating" => "", "remarkCodes" => "", "remarkTypes" => "", "paymentFrequency" => "", "paymentScheduleMonthCount" => "",
						"scheduledMonthlyPayment" => "", "calculatedMonthlyPayment" => "",
						"accountType" => "",
						"accountTypeStr" => "", "pastDue" => "", "maxDelinquency" => "", "paymentPattern" => "", "paymentPatternStartDate" => "", "monthsReviewedCount" => "", "late30Days" => "", "late60Days" => "", "late90Days" => "",
						"mostRecentPaymentDate" => "", "updateMethod" => "", "additionalTradeAccount" => "", "useForCalc" => "", "dateFlag" => "", "auFlag" => "", "mortgageAmount" => "");
					$remarkCodes = array();
					$remarkTypes = array();
					$maxDelinquency = array();
					$accountTypeStr = (string)$ch->account->type;
					if (!empty($ch->account->type)) {
						$accountType = (string)$ch->account->type;
						if (!empty($accountType) && array_key_exists($accountType, $accountTypes)) {
							$accountTypeStr = $accountTypes[$accountType];
						}
					}
					$newRow['industryCode'] = (string)$ch->subscriber->industryCode;
					$newRow['memberCode'] = (string)$ch->subscriber->memberCode;
					$newRow['nameUnparsed'] = (string)$ch->subscriber->name->unparsed;
					$newRow['portfolioType'] = (string)$ch->portfolioType;
					$newRow['accountNumber'] = (string)$ch->accountNumber;
					$newRow['ecoaDesignator'] = (string)$ch->ECOADesignator;
					$newRow['dateOpened'] = (isset($ch->dateOpened)) ? (string)$ch->dateOpened : '1000-01-01';
					$newRow['dateEffective'] = (isset($ch->dateEffective)) ? (string)$ch->dateEffective : '1000-01-01';
					$newRow['dateClosed'] = (isset($ch->dateClosed)) ? (string)$ch->dateClosed : '1000-01-01';
					$newRow['closedIndicator'] = (string)$ch->closedIndicator;
					$newRow['currentBalance'] = (int)$ch->currentBalance;
					$newRow['highCredit'] = (int)$ch->highCredit;
					$newRow['highLimit'] = ((int)$ch->highCredit > (int)$ch->creditLimit) ? (int)$ch->highCredit : (int)$ch->creditLimit;
					$newRow['accountRating'] = (string)$ch->accountRating;
					foreach ($ch->remark as $remark) {
						$remarkCodes[] = $remark->code;
						$remarkTypes[] = $remark->type;
					}
					$newRow['remarkCodes'] = ($remarkCodes);
					$newRow['remarkTypes'] = ($remarkTypes);
					
					$newRow['paymentFrequency'] = (string)$ch->terms->paymentFrequency;
					$newRow['paymentScheduleMonthCount'] = (int)$ch->terms->paymentScheduleMonthCount;
					$newRow['scheduledMonthlyPayment'] = (int)$ch->terms->scheduledMonthlyPayment;

					//added 20200924 - per conversation with Brian & Roupen
					//Revolving 3%, everything else 1% including student loans. NO review check (but tagged)
					$scheduledMonthlyPayment = (int)$ch->terms->scheduledMonthlyPayment;
					$portfolioType = (string)$ch->portfolioType;
					$setMonthly = true;
					if ($scheduledMonthlyPayment == 0 && (int)$ch->currentBalance > 0 ) {
						if (strtotime($ch->mostRecentPayment->date) < strtotime("-6 months") && !empty($ch->dateClosed)) {
							$setMonthly = false;
						}
						if ($portfolioType == "revolving" && $setMonthly) {
							$scheduledMonthlyPayment = (int)$ch->currentBalance * 0.03;
							if ($scheduledMonthlyPayment < 10) {
								$scheduledMonthlyPayment = 10;
							}
						} else if ((string)$ch->subscriber->name->unparsed == "AMEX" && $setMonthly) {
							$scheduledMonthlyPayment = (int)$ch->currentBalance * 0.03;
							if ($scheduledMonthlyPayment < 10) {
								$scheduledMonthlyPayment = 10;
							}
						} else if ((string)$ch->account->type == "LC" && $setMonthly) {
							$scheduledMonthlyPayment = (int)$ch->currentBalance * 0.03;
							if ($scheduledMonthlyPayment < 10) {
								$scheduledMonthlyPayment = 10;
							}
						} else if (empty($ch->dateClosed)) { //everyone else, student loans included, get 1% AMEX ALWAYS GETS 3%
							$scheduledMonthlyPayment = (int)$ch->currentBalance * 0.01;
							if ($scheduledMonthlyPayment < 10) {
								$scheduledMonthlyPayment = 10;
							}
						}
					}
					
					$newRow['calculatedMonthlyPayment'] = $scheduledMonthlyPayment;
					
					$newRow['accountType'] = (string)$ch->account->type;
					$newRow['accountTypeStr'] = $accountTypeStr;
					$newRow['pastDue'] = (int)$ch->pastDue;
					$newRow['maxDelinquency'] = ($ch->paymentHistory->maxDelinquency);
					$newRow['paymentPattern'] = (string)$ch->paymentHistory->paymentPattern->text;
					$newRow['paymentPatternStartDate'] = (isset($ch->paymentHistory->paymentPattern->startDate)) ? (string)$ch->paymentHistory->paymentPattern->startDate : '1000-01-01';
					$newRow['monthsReviewedCount'] = (int)$ch->paymentHistory->historicalCounters->monthsReviewedCount;
					$newRow['late30Days'] = (int)$ch->paymentHistory->historicalCounters->late30DaysTotal;
					$newRow['late60Days'] = (int)$ch->paymentHistory->historicalCounters->late60DaysTotal;
					$newRow['late90Days'] = (int)$ch->paymentHistory->historicalCounters->late90DaysTotal;
					$newRow['mostRecentPaymentDate'] = (isset($ch->mostRecentPayment->date)) ? (string)$ch->mostRecentPayment->date : '1000-01-01';
					$newRow['additionalTradeAccount'] = (string)$ch->additionalTradeAccount;
					$newRow['updateMethod'] = (string)$ch->updateMethod;
					
					$compareDate = (isset($ch->dateOpened)) ? strtotime($ch->dateOpened) : "";
					if ($compareDate < $inFileSinceDate) {
						$newRow['dateFlag'] = 1;
					} else {
						$newRow['dateFlag'] = 0;
					}
					
					if ($ch->ECOADesignator == 'authorizedUser' || $ch->ECOADesignator == 'terminated' || $ch->ECOADesignator == 'participant') {
						$newRow['auFlag'] = 1;
					} else {
						$newRow['auFlag'] = 0;
					}
					
					if (!empty($ch->dateClosed)) {
						$useForCalcVar = 0;
						if (!empty($ch->mostRecentPayment->date) && (int)$ch->currentBalance > 0) { //most recent date less than 6 months ago , revolving OR AMEX, still count it!
							if (strtotime($ch->mostRecentPayment->date) > strtotime("-6 months") && ($portfolioType == "revolving" || (string)$ch->subscriber->name->unparsed == "AMEX")) {
								$useForCalcVar = 1;
							}
						}
						$newRow['useForCalc'] = $useForCalcVar;
					} elseif ((int)$ch->currentBalance == 0) {
							$newRow['useForCalc'] = 0;
					} else {

						switch ($ch->accountRating) {
							case '8P':
							case '09':
							case '9B':
							case '9P':
							case 'UR':
								$newRow['useForCalc'] = 0;
								break;
							default:
								$newRow['useForCalc'] = 1;
								break;
						}
					}
					if ($newRow['useForCalc'] == 1 && in_array($newRow['accountType'], $realEstateList) && $scheduledMonthlyPayment > 0) {
						$newRow['mortgageAmount'] = $scheduledMonthlyPayment;
					} else {
						$newRow['mortgageAmount'] = 0;
					}
					$insArr[] = $newRow;
				}
			}
		}
		
		return $insArr;
		
		
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
	
	
	
}
