<? 

	//foreach($_GET as $key=>$val){print "$key : $val<br>";}
	
	header("Content-type: text/html; charset=utf-8");
	include("config.php");

	$fExtension = $_GET['ext'];
	$current_url =  $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if(substr($current_url, -1, 1)!="/" && !$fExtension){
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: http://$current_url/");
		// add slash at the end
	}
//	dbg(print_r($_GET,1));

	// Check authentification
	session_start();
    //dbg($_SESSION);
	if($_SESSION['email']){
		$caCFG->UserData->getData($_SESSION['email'], $_SESSION['pass']);

	}
	
	$Pages = new Pages();
	
	$rsi = new rsi();
	$rsi->start();
	
	if(($caCFG->URI = $_GET['sub']) || ($caCFG->SubDomainURI)){
		// Detect URI 
		$caCFG->VirtualUri = $caCFG->SubDomainURI."/".$caCFG->URI;	// merge subdomain path with uri path
		
		$uri_arr = explode('/', $caCFG->VirtualUri);
		$first = $f = array_shift($uri_arr) ? $f : array_shift($uri_arr);	// shift one more time if null
		switch($first){
			case 'json' : case 'xml' : case 'blank' : 
				$caCFG->DataType = $first;
				$caCFG->VirtualUri = join('/', $uri_arr);
			break;
		}
		/*
		$RequestPageId = ($id = $Pages->getId($caCFG->VirtualUri, 0)) 
			? $id 
			: $Pages->getId($caCFG->VirtualUri, $caCFG->SiteHomePage)
		;
		print "{". $Pages->getId($caCFG->VirtualUri, 0) . "|" .$Pages->getId($caCFG->VirtualUri, $caCFG->SiteHomePage). " ($caCFG->SiteHomePage)}";
		*/

		$RequestPageId = $Pages->getId($caCFG->VirtualUri, $caCFG->SiteHomePage);
		if($caCFG->VirtualUri && $RequestPageId == $caCFG->SiteHomePage){$RequestPageId = $Pages->getId($caCFG->VirtualUri, 0);}
		//print "<br/>[$caCFG->VirtualUri][$RequestPageId]<br/>";
	}
	else $RequestPageId = $caCFG->SiteHomePage;
	
	
	// Get subsite map 
	$SubMap = $Pages->siteMap($RequestPageId, $RequestLanguage);
	
		
	// Show data 
	$ip=$_SERVER['REMOTE_ADDR'];
	



	$CONTENT =  $Pages->ShowHTML($RequestPageId, $debug);
	$rsi->stop();
	$rsi->msg("all");
	print injector($CONTENT);

	//$caCFG->rsi->showLog();
	//print "<!-- [$ip] -->";

?>

