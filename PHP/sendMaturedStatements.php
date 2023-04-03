<?php 

//Basic script to ftp a file and email a message about the file. Page count is part of the email message, saves having to look at the file.
//Needs var list to run, as well as composer installed Mailgun SDK. 

require 'vendor/autoload.php';
use Mailgun\Mailgun;

include 'ftpVars.php';
/* Vars Needed: 
 * $oldPath
 * $newPath
 * $newName
 * $ftpUrl
 * $ftpUser
 * $ftpPwd
 * $mailgunApi
 * $mailgunDomain
 * $emailFrom
 * $emailTo
 * $emailCC
 * $emailSubject
 * $emailMessage --> moved this to inline since it contained some vars
 */


$script = array_shift($argv);
$pagecount = array_shift($argv);
$file = array_shift($argv);

$oldFile = $oldPath . "/".$file;
echo "\n".$pagecount;
echo "\n".$oldFile;
if (! is_file($oldFile)) {
        usage();
        exit;
}

$emailMessage = "New Matured Statements file $newName with $pagecount pages has been uploaded";

$sent = false;
$conn_id = ftp_connect($ftpUrl);

$finalPath = $newPath."/".$newName;

rename($oldFile, $finalPath);

// FTP THE FILE
if (ftp_login($conn_id, $ftpUser, $ftpPwd)) {

        ftp_pasv($conn_id, true);

        $remoteName = basename($finalPath);
        error_log("CALLING ftp_put ($conn_id, $remoteName, $finalPath)");
        if (ftp_put($conn_id, $remoteName, $finalPath, FTP_BINARY)) {
                $sent = true; 
                error_log("Uploaded $finalPath to FTP server");
        } else {
                echo "Error uploading $remoteName to FTP server";
        }
        ftp_close($conn_id);
}

// EMAIL CONFIRMATION WITH PAGE COUNT
if ($sent == true) {

	$mg = Mailgun::create($mailgunApi); // For US servers

	$mg->messages()->send($mailgunDomain, [
	  'from'    => $emailFrom,
	  'to'      => $emailTo,
	  'cc'	    => $emailCC,
	  'subject' => $emailSubject,
	  'text'    => $emailMessage
	]);
} else {
	echo "File not sent, something went wrong with ftp";
}

function usage() {
        echo "\n\nphp sendMaturedStatements.php #pagecount <fileNameInDownloadFolder>\n\n";
}

