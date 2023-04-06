<?php 
ini_set('error_log', 'error-view-creditreport.log');


$script = array_shift($argv);
$inputFile = array_shift($argv);
$scorecardFile = array_shift($argv);

if (! is_file($inputFile)) {
    usage("Missing input json file");
} else {
    $inputData = loadFile($inputFile);
    
}

if (! is_file($scorecardFile)) { //this is just needed for the decision text, which isn't in the credit report data
    usage("Missing scorecard json file");
} else {
    $scorecard = loadFile($scorecardFile);
    
}


//input is a json encoded file!

$data = generateArray($inputData, $scorecard);
echo "\ncreated array";
$html = generateHTML($data);
echo "\ncreated HTML";
//you can save the HTML if need be, I was for testing
//file_put_contents("response/".$inputData['input']['lastName']."-".$inputData['input']['ssn'].".html", $html);

//turning off pdf for testing
$pdf = generatePDF($html);

file_put_contents("response/".$inputData['input']['lastName']."-".$inputData['input']['ssn'].".pdf", $pdf);




function generateHTML($input) {
	
    $template = 'creditreport-template.html.php';
        
    ob_start();
    include($template);
    $html    = ob_get_contents();
    ob_end_clean();

    return $html;
}

function generatePDF($input) {

    //TODO this path needs to change, depending on where/how you have DOM PDF setup
    require_once('/path/to/include/dompdf/dompdf_config.inc.php');

    $dompdf = new DOMPDF();
    $dompdf->load_html($input);
    $dompdf->render();
    $generatePDF = $dompdf->output();
    
  return $generatePDF;  
    
}


function generateArray($input, $scorecard) {
	
    $outputArr = [];

    $outputArr['inputDataArr'] = $input['input'];
    $outputArr['main'] = $input['main'];

    $outputArr['addOns'] = $input['addOns'];
    
    //      error_log("What is in add ons? " . json_encode($addOns));
    $outputArr['summ'] = $input['summary'];

    $outputArr['pr'] = $input['publicRecord'];
    $outputArr['inq'] = $input['inquiry'];
    $outputArr['coll'] = $input['collection'];
    $outputArr['tr'] = $input['trade'];
    
    //break down the json encoded fields for use in the template
    $outputArr['aka'] = $input['main']['akaList'];
    $outputArr['cAdd'] = ["status" => "Current", "address" => $input['main']['address'], "city" => $input['main']['city'], "state" => $input['main']['state'], "zip" => $input['main']['zip']];
    $outputArr['pAdd'] = $input['main']['pastAddresses'];
    $outputArr['eInf'] = $input['main']['employmentInformation'];
    $outputArr['creditDataStatus'] = $input['main']['creditDataStatus'];

    $outputArr['score'] = $outputArr['addOns']['fico'];
    $outputArr['highRiskFraud'] = $outputArr['addOns']['highRiskFraud']; 
    $highRiskFraud = $outputArr['addOns']['highRiskFraud']; //moved the processing of this to below
    $outputArr['ofacMessage'] = $outputArr['addOns']['ofacNameScreen'];
    
    $outputArr['cr']['creditReportDate'] = date("m/d/Y", strtotime($input['main']['creditReportDate']));
    $outputArr['cr']['name'] = $input['main']['name'];
    $outputArr['cr']['ssn'] = $input['main']['socialSecurity']['number'];
    $outputArr['cr']['dob'] = date("m/d/Y", strtotime($input['main']['dob'][0]));
    $outputArr['cr']['score'] = $input['main']['score'];
    $outputArr['cr']['scoreReason'] = $input['main']['scoreReason'];
    $outputArr['cr']['factors'] = $input['main']['factors'];
    $outputArr['cr']['decisionText'] = $scorecard['decisionText'];
    $outputArr['fraudCodes'] = getFraudCodes();
    $outputArr['industryCodes'] = getIndustryCode();
    $outputArr['consumerStatement'] = $input['main']['consumerFileData'];

    $fraudToProcess = [];

    if (isset($highRiskFraud['message']) && !isset($highRiskFraud['message']['@attributes'])):
        if (count($highRiskFraud['message']) > 1): //means there's more than one in here
            foreach ($highRiskFraud['message'] as $inside):
                $fraudToProcess[] = $inside;
            endforeach;
        endif;
    elseif (isset($highRiskFraud['message'])):
        $fraudToProcess[] = $highRiskFraud['message'];
    endif;
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

    $outputArr['fraudToProcess'] = $fraudToProcess;


    return $outputArr;

}

function loadFile($jsonFile) {

    $processedData = [];
    $in = file_get_contents($jsonFile);
    $processedData = json_decode($in, true);

    //make sure inputdata has something
    if (! count($processedData)) {
            usage("Input file is empty ");
    }

    return $processedData;

}


    /**
     * @return array
     */
    function getFraudCodes() {
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
            '3000' => 'Input/File address requires further investigation', 
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
    function getIndustryCode() {
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
    

function usage($err) {
    echo "\n\nUSAGE: view-creditreport.php <path/to/input/json/file> \n";
            echo "Contents of the json file created in the credit report process\n";
    echo "\nERROR: $err\n\n";
    exit;
}
