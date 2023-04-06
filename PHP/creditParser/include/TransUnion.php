<?php
/**
 * Created by PhpStorm.
 * User: jnason
 */

class TransUnion {

        protected $URL                  = '******';
        protected $CERT_FILE            = 'certs/MAIN5.crt';
        protected $KEY_FILE             = 'certs/MAIN5.key';
    	protected $CA_BUNDLE		    = 'certs/MAIN5.ca-bundle';
    	protected $PEM_FILE		        = 'certs/MAIN5.pem';
        protected $USERNAME             = '******';
        protected $PASSWORD             = '******';
        protected $INDUSTRY_CODE        = '******';
        protected $MEMBER_CODE          = '******';
        protected $SUBSCRIBER_CODE      = '******';
        protected $SUBSCRIBER_PWD       = '******';
        protected $ISPROD				= false;
        protected $ERRORS               = array();
	
	/**
	 * Create a new TransUnion object
	 *
	 * The function __construct() returns a new
	 * object
	 *
	 * @param string $env
	 */
        public function __construct($env = 'dev') {
                $this->USERNAME                 = '******';
                $this->PASSWORD                 = '******';
                $this->URL                      = '******';
                $this->INDUSTRY_CODE            = '******';
				$this->MEMBER_CODE              = '******';
				$this->SUBSCRIBER_PREFIX		= '******';
				$this->SUBSCRIBER_PWD			= '******';
				if ($env == 'prod' || $env == 'production') {
					$this->MEMBER_CODE = '******';
					$this->SUBSCRIBER_PREFIX = '******';
					$this->SUBSCRIBER_PWD = '******';
	                $this->URL = '******';
	                $this->ISPROD = true;
				}
                $this->CERT_FILE            = 'certs/MAIN5.crt';
                $this->KEY_FILE             = 'certs/MAIN5.key';
                $this->CA_BUNDLE            = 'certs/MAIN5.ca-bundle';
		        $this->PEM_FILE		        = 'certs/MAIN5.pem';
                error_log('CERT_FILE: ' . $this->CERT_FILE);
                error_log('KEY_FILE: ' . $this->KEY_FILE);
                error_log("TU!  URL = " . $this->URL);
                return true;
        }
        
       public function submit($personArr = []) {
	        //personArr is: : firstName, lastName, address, city, state, zip, dob, ssn
			$dir = "/path/to/bin/creditParser/include/";
		   
                $errFile = 'log/TU-' . $personArr['ssn'] . '-' . microtime(true) . '.stderr';
                $inFile = 'log/TU-' . $personArr['ssn'] . '-' . microtime(true) . '.input';

                $uniqueVar = md5($personArr['lastName'].$personArr['ssn'] . microtime());

                $systemId               = $this->USERNAME;
                $systemPassword         = $this->PASSWORD;
                $subscriberIndustryCode = $this->INDUSTRY_CODE;
                $subscriberMemberCode   = $this->MEMBER_CODE;
                $subscriberPrefixCode   = $this->SUBSCRIBER_PREFIX;
                $subscriberPassword     = $this->SUBSCRIBER_PWD;
				$isProd 				= $this->ISPROD;
                
                ob_start();
                include('transunion-xml.php');
                $xml_data = ob_get_contents();
                ob_end_clean();
                file_put_contents($inFile, $xml_data);
                /* read input from STDIN and write to STDOUT */
                $cmd = "curl --url " . $this->URL . " --verbose --data @- --output - --cacert " . $dir . $this->CA_BUNDLE . " -E " . $dir . $this->PEM_FILE . ":" . $this->PASSWORD . " -1 --location";

                $descriptorspec = array(
                           0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                           1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                           2 => array("pipe", "w")   // stderr is a pipe that the child will to write to
                );

                error_log("Opening proc for $cmd");
                $process = proc_open($cmd, $descriptorspec, $pipes);


                if (is_resource($process)) {
                        // $pipes now looks like this:
                        // 0 => writeable handle connected to child stdin
                        // 1 => readable handle connected to child stdout
                        // 2 => readable handle connected to child stderr

                        fwrite($pipes[0], $xml_data);
                        fclose($pipes[0]);

                        $response = stream_get_contents($pipes[1]);
                        fclose($pipes[1]);

                        $stderr = stream_get_contents($pipes[2]);
                        fclose($pipes[2]);

                        error_log("TU: STDERR: $stderr");
                        // It is important that you close any pipes before calling
                        // proc_close in order to avoid a deadlock
                        $return_value = proc_close($process);
                } else {
                        file_put_contents($errFile, "$xml_data\n\n$cmd\n\nFAILED TO OPEN PROCESS");
                        return false;
                }
                if ($return_value) {
                        /* process exited abnormally, save stderr */
                        file_put_contents($errFile, "PROCESS EXITED ABNORMALLY ($return_value)\n\n$xml_data\n\n$cmd\n\n$stderr");
                        return false;
                }

                /* find start of payload */
                $start = strpos($response, '<creditBureau ');
                if ($start !== false) {
                        $xmlString = substr($response, $start);
                        libxml_use_internal_errors(true);

                        try {

                        $xml = new SimpleXMLElement($xmlString);
                        $errors = libxml_get_errors();
                                if (count($errors)):
                                foreach ($errors as $error):
                                $errors[] = ("\nXML ERROR: L " . $error->line . " C " . $error->column . " LEVEL " . $error->level . " CODE " . $error->code . " - " . $error->message);
                                endforeach;
                                libxml_clear_errors();
                                        return false;
                        else:
                                        return $xml;
                                endif;
                        } catch (Exception $e) {
                                $this->ERRORS[] = 'Error parsing XML: ' . $e->getMessage();
                                return false;
                        }
                } else {
                        $this->ERRORS[] = 'No valid XML found';
                        file_put_contents($errFile, "NO VALID XML\n\n$xml_data\n\n$cmd\n\n$stderr");
                        return false;
                }

        }

       public function hasErrors() {
                return count($this->ERRORS);
       }
       public function getErrors() {
                return $this->ERRORS;
       }

        
}
