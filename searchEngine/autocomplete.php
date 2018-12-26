<?php

	header('Content-Type:text/html; charset=utf-8');
	$query = strtolower($_REQUEST["q"]);
	$limit = 10;
	$hint = "";

	if($query !== ""){
		$pieces = explode(" ", $query);
		$query = array_pop($pieces);
		require_once('solr-php-client/Apache/Solr/Service_autocomplete.php');
// Service1.php is a replica of Service.php with requesthandler as suggest.
		$solr = new Apache_Solr_Service('localhost',8983,'/solr/latimes/');
		$q = htmlspecialchars($query, ENT_QUOTES, 'utf-8');

		if(get_magic_quotes_gpc() == 1){ 
			$q = stripslashes($q);
		}

		try{
			$results = $solr->search($q, 0, $limit); 
		}
		catch(Exception $e){
			 die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
		}
	}
	else{
		echo json_encode(""); 
	}

	if($results){
		$doc = $results->suggest; 
		foreach($doc as $field=>$value){ 
			$solution = $value; 
		}
		foreach($solution as $field=>$value){
			$try = $value; 
		}
		$output[] = array();
		for($i = 0; $i < $try->numFound ; $i++){
//			if($strlen($output[$i]) < 15)
				$output[$i] = ($try->suggestions[$i]->term);
		}
		echo json_encode($output);
	}else{
		echo "";
	}
?>

