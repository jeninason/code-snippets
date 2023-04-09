<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use CommonBundle\Annotation\Auth;
use CommonBundle\Annotation\Validation;
use CommonBundle\Annotation\Output;
use CommonBundle\Helper\ResponseBody;
use \Firebase\JWT\JWT;

/**
 * Endpoint for notifications from MailGun
 */
class MailgunController extends Controller {

    /**
     * Endpoint for MailGun to send new messages
     * 
     * @Route("/api/partner/mailgun", name="mailgun_webook")
     * @Method("POST")
     * @Auth("")
     * @Validation("Mailgun/Webhook")
     */
    public function webhookAction() {

        $response = new ResponseBody();

        $args = $this->get('validator')->getArgs();

        //TODO ensure tmp folder is being cleared regularly
        file_put_contents('/tmp/mg-' . microtime(true) . '.dat', print_r($args, true));

        $recipient = $args['recipient'];
        list($msgid, $domain) = explode('@', $recipient);

        $sender = $args['sender'];
        $senderName = $sender;
        if (preg_match('/([\w\s\d]+)\s+\<.*/', $args['From'], $matches)) {
            $senderName = $matches[1];
        }

        if (is_numeric($msgid)) {

            /* create a new msg object */
            $msg = $this->get('mgr')->getObject('AppMessage');
            $msg->set('subject', $args['subject']);
            $msg->set('message', $args['stripped-text']);
            $msg->set('outbound', 0);
            $msg->set('mode', 'Email');

            /* grab the original message */
            $orig = $this->get('mgr')->getObject('AppMessage');
            $orig->select($msgid);

            if ($orig->getId()) {
                $msg->setTarget($orig->getSender());
                $msg->setSender($orig->getTarget());
                $msg->set('apptype', $orig->get('apptype'));
                $msg->set('appid', $orig->get('appid'));
                $msg->set('quoteid', $orig->get('quoteid'));
                $msg->set('parentid', $orig->getId());
                $msg->write();

                /* create a new activity record */
                $activity = $this->get('mgr')->getObject('AppActivity');
                $activity->set('apptype', $orig->get('apptype'));
                $activity->set('appid', $orig->get('appid'));
                $activity->set('quoteid', $orig->get('quoteid'));
                $activity->set('dealerid', $orig->get('dealerid'));
                $activity->set('usertype', $msg->get('senderType'));
                $activity->set('userid', $msg->get('senderid'));
                $activity->set('username', $msg->get('senderName'));

                $activity->set('info', $msg->get('senderName') . ' replied to ' . $msg->get('targetName'));

                /* type of activity depends on who the sender was */
                $code = false;
                $info = '';
                switch ($msg->get('senderType')) {
                    case 'DealerUser':
                        $code = 'dealer-reply';
                        break;
                    case 'Applicant':
                        $code = 'consumer-reply';
                        break;
                    case 'InternalUser':
                        $code = 'internal-reply';
                        break;
                }

                if (!$code || !$activity->setTypeByCode($code)) {
                    // throw new \Exception("UNKNOWN ACTIVITY CODE $code");
                    $this->get('logger')->critical('UNKNOWN ACTIVITY CODE' . $code);
                }

                $this->get('logger')->info('Created new incoming message id ' . $msg->getId());
            } else {
                $this->get('logger')->critical('Invalid incoming message id ' . $msgid);
            }

            $msg->write();

        } elseif (preg_match('/^\-([^@]+)\@mg.jnason.com$/', $recipient, $matches)) {
            $email = $matches[1];
            str_replace($email, '-', '@');


        } else {
            $this->get('logger')->critical('Invalid incoming message recipient ' . $recipient);
        }

        return new JsonResponse($response->ok("hi"), $response->code);


    }
    
    /**
     * Endpoint for MailGun to receive app doc messages
     *
     * @Route("/api/partner/mailgunreceive/{leadid}", name="mailgun_receive_webook")
     * @Route("/api/partner/mailgunreceive", name="mailgun_receive2_webook")
     * @Method("POST")
     * @Auth("")
     * @Validation("Mailgun/MailgunReceiveWebhook")
     */
    public function appDocWebhookAction() {

        $response = new ResponseBody();

        $args = $this->get('validator')->getArgs();
        //file_put_contents('/tmp/mg-receive-' . microtime(true) . '.dat', print_r($args, true));

        $recipient = $args['recipient'];
        list($leadid, $domain) = explode('@', $recipient);

        if (stripos($leadid, "-test") === false) {
            if (is_numeric($leadid)) {
                //this is a real lead id!
                //$this->get('logger')->info("The leadid: $leadid");
            } else {
                //there's something else in here, exit prematurely
                $this->get('logger')->critical("There is no leadid $leadid");
	            return new JsonResponse($response->validation('Invalid Lead ID'), $response->code);
            }
        } else {
            //this is a test email, strip out test and move forward
            $leadid=str_replace("-test", "", $leadid);
            $this->get('logger')->info("The test leadid: $leadid");
        }
        
        //load the lead
        $lead = $this->get('mgr')->getObject('DealerLead');
        if (! $lead->select($leadid)) {
            return new JsonResponse($response->validation('Invalid Lead ID'), $response->code);
        }
        
        //check there were attachments
   		$numAttachment = $args['attachment-count'];
		if (! $numAttachment) {
			return new JsonResponse($response->ok("No file found"), $response->code);
		}
		
		//$this->get('logger')->info("found attachments: " . $numAttachment);
		
		if ($numAttachment >= 1) {
			//$file = array_shift($args['files']);
			foreach ($args['files'] as $file) {
				$attachment = $file['path'];
				$filename = $file['filename'];
//				$filename = preg_replace("/[^a-zA-Z0-9_.]", "", $filename); //any non word character or full stop
                $filename = str_replace("(", "_", $filename);
                $filename = str_replace(")", "_", $filename);

				$filename = str_replace(' ', '_', $filename);
				//save the doc(s)
				$fparts = explode('.', $filename);
	            $type = array_pop($fparts);
                $type = strtolower($type);
                $fullpath = $attachment;
	            $path = dirname($fullpath);

	            if (in_array($type, ['jpg', 'jpeg', 'gif', 'tif', 'tiff', 'png', 'pdf'])) { //bad type, don't save
		            if (!in_array($type, array('pdf', 'application/pdf', 'application/x-pdf', 'text/pdf', 'application/vnd.pdf'))) {
			
			            /* convert the doc to pdf format */
			            $parts = explode('.', $filename);
			            $ext = array_pop($parts);
			            $new_name = implode('.', $parts) . '.pdf';
			
			            $convertCmd = 'convert ' . $path . '/' . $filename . ' ' . $path . '/' . $new_name;
			            $this->get('logger')->info('Converting ' . $filename . ' to ' . $new_name . ' using CMD ' . $convertCmd);
			            `$convertCmd`;
			
			            $filename = $new_name;
			            $fullpath = $path . '/' . $filename;
		            }
		
		            $doc = $lead->createDoc();
		            $doc->set('type', 'document');
		            $doc->set('filename', $filename);
		            $doc->set('status', 'new');
		            $doc->set('mime', 'application/pdf');
		            $doc->storeFile(file_get_contents($fullpath));
		            $doc->write();
		
		            $task = $lead->addWorkflowTask('reviewDocument', 'Document', $doc->getId());
	            } else {
                    $this->get('logger')->info("WRONG FILE TYPE: $type");
                }
			}
		}

		
        return new JsonResponse($response->ok("hi"), $response->code);
        
    }

}