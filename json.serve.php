<?php
require_once('classes.lib.php');
header('Content-type','application/json');

$sets = SetFactory::allSets();

if(!empty($_REQUEST['q'])){
	
	if($_REQUEST['q'] === 'save'){
		$collection = json_decode(file_get_contents('php://input'));
		print abstractJSON($collection);
	}
	
	if($_REQUEST['q'] === 'load'){
		$collection = file_get_contents('php://input');
		$json = json_encode(clarifyJSON(trim($collection,'"')));
		print $json;
	}
	
	if($_REQUEST['q'] === 'all'){
		$string = '[';
		foreach($sets as $set){
			$string .= $set->json(true).',';
		}
		$string = trim($string,',').']';
		print $string;
	}
	
	if($_REQUEST['q'] === 'draft'){
		$col = json_decode(file_get_contents('php://input'));
		if(empty($col)){
			print "Unable to read collection";
		}else{
			/*	$rules = 
				[
							team size: default 6,
							number of teams: default 1,
							balance cost?: default false,
							balance rarities?:  default false (recursive call: array(rarities)) 
				]
			*/
			$draft = draft($col,$_REQUEST['rules']);
			print(json_encode($draft));
		}
	}
	
}

?>