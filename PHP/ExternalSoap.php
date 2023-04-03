<?php 

use \SoapClient;

$PAYOFF_URL = '<INSERT URL HERE>';

//NOTE: this is part of a larger class with other functions handling the connection

function getPayoffQuote($appid, $PAYOFF_URL) {
		
		$client = new SoapClient($PAYOFF_URL);
		$template = 'get-payoff-quote.php';
		
		ob_start();
		include($template);
		$xmlDocument = ob_get_contents();
		ob_end_clean();
		

		error_log('create: payoff template ' . $template . ' grabbed xml doc => ' . substr($xmlDocument, 0, 500) . ' ....');
		
		$reqInput = array(
			'inputGetQuote' => trim($xmlDocument),
		);
		//TODO remove debugging lines before going live
		error_log("1: " . json_encode($reqInput));
		try {
			$start = time();
			//TODO these files are for testing, tmp is cleared regularly, turn off once no longer needed
			file_put_contents('/tmp/payoff-init-' . time() . '.txt', $PAYOFF_URL . "\n" . $reqInput);
			$response = $client->getPayoffQuote($reqInput);
			file_put_contents('/tmp/payoff-' . time() . '.txt', $PAYOFF_URL . "\n" . $reqInput . "\n\nRESP\n" . print_r($response, TRUE));
			
			if ($response) {
				error_log('LeasePak::create: RECEIVED ' . round(time() - $start, 0) . ' seconds');
				
				// got a response, parse it 
				libxml_use_internal_errors(TRUE);
				$xml = simplexml_load_string($response->return, 'SimpleXMLElement', LIBXML_NOCDATA);
				if ($xml === FALSE) {
					foreach (libxml_get_errors() as $error) {
						error_log('create: XML Error => ' . $error->message);
					}
					return FALSE;
				}
				return json_encode($xml, JSON_PRETTY_PRINT);
			}
		} catch (Exception $e) {
			error_log('Caught exception in create: ' . $e->getMessage());
			error_log('Caught exception in create: ' . $e->getMessage() . ' RAW ' . $client->getLastResponse());
			error_log('Caught exception in create: ' . $e->getMessage() . ' RAW ' . $client->getLastRequest());
			error_log($e->getMessage());
			return FALSE;
			
		}
		error_log('payoff: dropthrough failure');
		return FALSE;
	}
