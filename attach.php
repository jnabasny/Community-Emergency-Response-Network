<?php
# Coded By Jijo Last Update Date [Jan/19/06] (http://www.phpclasses.org/browse/package/2964.html)
# Updated 2008-12-18 by Dustin Davis (http://nerdydork.com)
	# Utilized $savedirpath parameter
	# Added delete_emails parameter
	
	
class ReadAttachment
{
	function getdecodevalue($message,$coding) {
		switch($coding) {
			case 0:
			case 1:
				$message = imap_8bit($message);
				break;
			case 2:
				$message = imap_binary($message);
				break;
			case 3:
			case 5:
				$message=imap_base64($message);
				break;
			case 4:
				$message = imap_qprint($message);
				break;
		}
		return $message;
	}


	function getdata($host,$login,$password,$savedirpath,$delete_emails=false) {
		// make sure save path has trailing slash (/)
		$savedirpath = str_replace('\\', '/', $savedirpath);
		if (substr($savedirpath, strlen($savedirpath) - 1) != '/') {
			$savedirpath .= '/';
		}
		
		$mbox = imap_open ($host, $login, $password) or die("can't connect: " . imap_last_error());
		$message = array();
		$message["attachment"]["type"][0] = "text";
		$message["attachment"]["type"][1] = "multipart";
		$message["attachment"]["type"][2] = "message";
		$message["attachment"]["type"][3] = "application";
		$message["attachment"]["type"][4] = "audio";
		$message["attachment"]["type"][5] = "image";
		$message["attachment"]["type"][6] = "video";
		$message["attachment"]["type"][7] = "other";
		
		for ($jk = 1; $jk <= imap_num_msg($mbox); $jk++) {
			$structure = imap_fetchstructure($mbox, $jk , FT_UID);    
			$parts = $structure->parts;
			$fpos=2;
			for($i = 1; $i < count($parts); $i++) {
				$message["pid"][$i] = ($i);
				$part = $parts[$i];
				
				if($part->disposition == "ATTACHMENT") {
					$message["type"][$i] = $message["attachment"]["type"][$part->type] . "/" . strtolower($part->subtype);
					$message["subtype"][$i] = strtolower($part->subtype);
					$ext=$part->subtype;
					$params = $part->dparameters;
					$filename=$part->dparameters[0]->value;
					
					$mege="";
					$data="";
					$mege = imap_fetchbody($mbox,$jk,$fpos);  
					$filename="$filename";
					$fp=fopen($savedirpath.$filename,w);
					$data=$this->getdecodevalue($mege,$part->type);	
					fputs($fp,$data);
					fclose($fp);
					$fpos+=1;
				}
			}
			if ($delete_emails) {
				// imap_delete tags a message for deletion
				imap_delete($mbox,$jk);
			}
		}
		// imap_expunge deletes all tagged messages
		if ($delete_emails) {
			imap_expunge($mbox);
		}
		imap_close($mbox);
	}
}