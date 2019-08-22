<?php

// INCLUDE FUNCTIONS FOR OPENING ATTACHMENT

include('attach.php');

// OPEN EMAIL AND CHECK FOR TEXTS
    

/* connect to gmail */
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'email@gmail.com';
$password = 'password';
/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');

$max_emails = 16;
 
 
/* if any emails found, iterate through each email */
if($emails) {
 
    $Ecount = 1;
 
    /* put the newest emails on top */
    rsort($emails);
 
    /* for every email... */
    foreach($emails as $email_number) 
    {
 
        /* get information specific to this email */
        $overview = imap_fetch_overview($inbox,$email_number,0);
        
        
      /* get sender's number */
      foreach ($overview as $msgparts){
		$fromaddress = $msgparts->from;
		$fromnumber = explode("@", $fromaddress);
		$sendernumber = substr($fromnumber[0], -10);	
		}
 
 
if($Ecount++ >= $max_emails) break;

} 
		
        $message = imap_fetchbody($inbox,$email_number,2);
        //error_log(print_r($message,true));
        
         /* get mail structure */
        $structure = imap_fetchstructure($inbox, $email_number);
 
        $attachments = array();
 
        /* if any attachments found... */
        if(isset($structure->parts) && count($structure->parts)) 
        {
            for($i = 0; $i < count($structure->parts); $i++) 
            {
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );
 
                if($structure->parts[$i]->ifdparameters) 
                {
                    foreach($structure->parts[$i]->dparameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'filename') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }
 
                if($structure->parts[$i]->ifparameters) 
                {
                    foreach($structure->parts[$i]->parameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'name') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }
 
                if($attachments[$i]['is_attachment']) 
                {
                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
 
                    /* 3 = BASE64 encoding */
                    if($structure->parts[$i]->encoding == 3) 
                    { 
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    }
                    /* 4 = QUOTED-PRINTABLE encoding */
                    elseif($structure->parts[$i]->encoding == 4) 
                    { 
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            }
        }
 
        /* iterate through each attachment and save it */
        foreach($attachments as $attachment)
        {
        	$attachtext = $attachment['is_attachment'];
        	//error_log(print_r($attachtext,true));
            if($attachment['is_attachment'] == 1)
            {
            	//error_log($attachment['name']);
            	//error_log($attachment['attachment']);
                $filetype = substr($attachment['name'], -3);
                //$filename = $attachment['name'];
                if ( ($filetype == "txt") || ($attachment['name'] == "")) {
                $filename = "message.txt";
                if(empty($filename)) $filename = $attachment['filename'];
 
                if(empty($filename)) $filename = time() . ".dat";
                //$fp = fopen("./" . $email_number . "-" . $filename, "w+");
                $fp = fopen("./message.txt", "w+");
                fwrite($fp, $attachment['attachment']);
                fclose($fp);
             		}
             		else { 
             		//not a text message
             		}
           }
	}


// CHECK EMAIL BODY FOR MESSAGE

If ($message != "") {
$relaymessage = $message;
goto send;
}

// EXTRACT TEXT MESSAGE FROM EMAIL ATTACHMENT

If (file_exists("message.txt")) {

$emailmessage = fopen("message.txt", "r") or die("Unable to open file!");
//error_log(print_r($emailmessage,true));
$relaymessage = fread($emailmessage,filesize("message.txt"));
//error_log(print_r($relaymessage,true));
fclose($emailmessage);
unlink ("message.txt");
} else {
	goto end;
}

}
else {
	//error_log("no emails");
	die();
}

send:
// SEND MESSAGE TO ALL COMMUNITY MEMBERS

If ($relaymessage != "") {
	
$servername = "localhost";
$username = "admin";
$password = "password";
$dbname = "cern";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    //die("Connection failed: " . $conn->connect_error);
   error_log("Connection failed: " . $conn->connect_error);
	goto end;
} 
	
	//UNSUBSCRIBE OPTION
	
	
	$lowercase = strtolower($relaymessage);
	$unsub = substr($lowercase, 0, 11);
	
	If ($unsub == "unsubscribe") {
	$sql = "DELETE FROM Members WHERE Phone_Number='$sendernumber'";
	if ($conn->query($sql) === TRUE) {
    echo $sendernumber . " removed from network!";
    mail($fromaddress, "", "You are now unsubscribed from the Community Emergency Response Network.", "");
	} else {
    echo "Error removing " . $sendernumber . " from network: " . $conn->error;
	}
	$conn->close();
	goto end;
	} else { //do nothing 
	}
	
$sql = "SELECT Address FROM Members";
$result = $conn->query($sql); 

if ($result->num_rows > 0) {	
	while($row = $result->fetch_assoc())  {
		$sent=normal($row["Address"], $relaymessage, "", 1);
}
}  else {
	  error_log("Error: " . $row["Address"]);
}

$conn->close();

} else {
	
	//error_log("Message is blank");
	goto end;
}

end:
imap_delete($inbox,$email_number);
imap_expunge($inbox);
imap_close($inbox, CL_EXPUNGE);
die();


function normal($to,$message,$oper,$num)
{
$adhead="";
for($i=1;$i<=$num;$i++)
{
$sent=mail($to, "", $message, $adhead);
}
if($sent)
{
//sent!
}
else
{
error_log("A message failed to send.");
}
}


?>