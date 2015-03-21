<?php


abstract class SetFactory{
	function create($file,$name = '',$universe = '',$safeName = ''){
		$fp = $file;
		$db = new Set($name,$universe,$safeName);
		if($fileArr = file($file)){
			$headers = array_shift($fileArr);
			$headers = explode('|',$headers);
			
			foreach($fileArr as $index => $dataString){
				$dataString = trim($dataString);
				$dataArr = explode('|',$dataString);
				$card = CardFactory::create($dataArr,$headers,$file);
				$db->add($card);
			}
			
			return $db;
		}
		throw new Exception("Failed to create set $universe $name from $file");
		return false;
	}
	
	function sets(){
		return array(
			array('Battle for Faerun',	'Dungeons & Dragons',	'dnd.bff.set',			'da.txt'),
			array('Avengers vs. X-men',	'Marvel',				'marvel.avx.set',		'ma.txt'),
			array('Uncanny X-men',		'Marvel',				'marvel.uncanny.set',	'mb.txt'),
			array('Yu-Gi-Oh',			'Yu-Gi-Oh',				'yugioh.yugioh.set',	'ya.txt')
			
		);
	}
	
	function setFiles(){
		$files = array();
		$sets = SetFactory::sets();
		foreach($sets as $index => $array){
			$files[] = $array[3];
		}
		return $files;
	}
	
	function createAll(){
		$fp = 'source/';
		$sets = SetFactory::sets();
		// [array(Set,Universe,SetName,fileName), ...]
		
		foreach($sets as $index => $data){
			$set = SetFactory::Create($fp.$data[3],$data[0],$data[1],$data[2]);
			SetFactory::save($set);
		}
		return SetFactory::allSets();
	}
	
	function save($set,$file = null){
		if(empty($file)){
			$file = 'sets/'.$set->safeName();
		}
		$setString = serialize($set);
		if(file_put_contents($file,$setString) > 0){
			return true;
		}else{
			throw new Exception("Failed to save set to $file.");
			return false;
		}
	}
	
	function allSets(){
		$sets = glob('sets/*.set');
		$setsArr = array();
		foreach($sets as $set){
			$setsArr[] = SetFactory::load($set);
		}
		return $setsArr;
	}
	
	function load($file){
		$set = unserialize(file_get_contents($file));
		if(!$set){
			throw new Exception("Failed to load $file as set.");
			return false;
		}
		return $set;
	}
	
	function poolFromCol($col,$forDraft = true){
		$allSets = SetFactory::allSets();
		$pool = array();
		$rarities = CardFactory::Rarities();
		foreach($col->cards as $card){
			
			$idArr = explode('.',$card->id);
			$set	= $idArr[0];
			$num	= $idArr[1];
			$rarity = $idArr[2];
			foreach($allSets[$set]->cards() as $setCard){
				if($setCard->Number == $num AND array_search($setCard->Rarity,$rarities) == $rarity){
					if($forDraft){
						$i = 0;
						while($i < $card->cards){
							$pool[] = $setCard;
							$i++;
						}
					}else{
						$setCard->cards = $card->cards;
						$setCard->dice	= $card->dice;
						$pool[] 		= $setCard;
					}
				}
			}
		}
		
		return $pool;
	}
}

abstract class CardFactory{
	
	function setKeys(){
		$sets = SetFactory::sets();
		$array = array();
		foreach($sets as $set){
			$array[] = $set[3];
		}
	}
	
	function create($arr,$headers,$set){
		$card = new Card();
		$fields = CardFactory::Fields();
		$rarities = CardFactory::Rarities();
		$sets = SetFactory::setFiles();
		
		foreach($headers as $key => $name){
			$name = trim($name);
			if(in_array($name,$fields)){
				if(!empty($arr[$key])){
					$card->$name = trim($arr[$key],' "\'');
				}else{
					$card->$name = null;
				}
			}
		}
		$setIndex = array_search(basename($set),$sets);
		// id is setIndex.CardNumber.Rarity
		$card->id = $setIndex.'.'.$card->Number.'.'.array_search($card->Rarity,$rarities);
		return $card;
	}
	
	function Rarities(){
		return array('Starter','Common','Uncommon','Rare','Super Rare','OP');
	}
	
	function Fields(){
		return array('Title','SubTitle','Rarity','Number','Cost');
	}
	
}

class Set{
	private $name 		= '';
	private $count 		= 0;
	private $universe 	= '';
	private $cards 		= array();
	private $safeName	= '';
	
	function __construct($name,$universe,$safeName){
		$this->name		= $name;
		$this->universe	= $universe;
		$this->safeName	= $safeName;
	}
	
	function add($card){
		$this->cards[] = $card;
		$this->count++;
	}
	
	function safeName(){
		return $this->safeName;
	}
	
	function json($cards = false){
		$json = '{';
		foreach($this as $prop => $val){
			if(!is_array($val)){
				$json .= "\"$prop\" : \"$val\",";
			}else{
				if($cards){
					$json .= "\"$prop\" : [";
					foreach($val as $key => $item){
						$json .= $item->json().',';
					}
					$json = trim($json,',');
					$json .= '],';
				}
			}
		}
		$json = trim($json,',');
		$json .= '}';
		return $json;
	}
	
	function cards(){
		return $this->cards;
	}
	
	function name(){
		return $this->name;
	}
}

class Card{
	public $in = false;
	function json(){
		return json_encode($this);
	}

}

class Collection{
	
	function json(){
		return json_encode($this);
	}
}

function deflate($string){
	return base64_encode(gzdeflate($string,9));
}

function inflate($string){
	return gzinflate(base64_decode($string));
}


function abstractJSON($obj){
	$string = $obj->name.':';
	foreach($obj->cards as $card){
		$string .= $card->id . '|' . $card->cards . '|' . $card->dice . ';';
	}
	$string = trim($string,';');
	return deflate($string);
}

function clarifyJSON($string){
	$string = inflate($string);
	$name = explode(':',$string);
	$cards = explode(';',$name[1]);
	$name = str_replace('+',' ',$name[0]);
	$collection = new Collection();
	$collection->name = $name;
	$collection->cards = array();
	foreach($cards as $card){
		$cardObj = new Card();
		$fields = explode('|',$card); // id, cardCount, dieCount
		$cardObj->id = $fields[0];
		$cardObj->cards = $fields[1];
		$cardObj->dice = $fields[2];
		$collection->cards[] = $cardObj;
	}
	$collection->cards = SetFactory::poolFromCol($collection,$forDraft = false); 
	return $collection;
	
}

function draft($col, $rules){
	
	if(!is_array($col)){
	# print "BUILDING POOL...".PHP_EOL;
		$pool = SetFactory::poolFromCol($col);
	}else{
	# print "USING POOL...".PHP_EOL;
		$pool = $col;
	}

	$num  		= $rules->teamSize; 									// team size: default 6
	$teams 		= $rules->teamCount; 									// number of teams: default 2
	$cost  		= ($rules->balanceCost 		=== 'yes')? true : false;	// balance cost?: default false
	$rarities	= ($rules->balanceRarities	=== 'yes')? true : false;	// balance rarities?:  default false (recursive call: array(rarities))
	
	shuffle($pool);
	
	$draft = array();
	$names = array();
	
	# Build/initiate Rarity bins
	if($rarities !== 'false'){
		# BALANCE RARITIES
		if(is_array($rarities)){
			# This is a recursive call; count rarities 
			$rarity_counts = array('Common' => 0,'Uncommon' => 0, 'Rare' => 0, 'Super-Rare' => 0);
			foreach($rarities as $r){
				$rarity_counts[$r]++;
			}
		}else{
			$rarity_counts = null;
			$rarities = array();
		}
	}
	
	# Build cost bins
	if($cost !== 'false'){
		$costs = array_fill(0,4,'low')+array_fill(4,2,'medium')+array_fill(6,10,'high');
		$cost_counts = array('low' => floor($num/3), 'medium' => (floor($num/3)+$num%3), 'high' => floor($num/3));
	}else{
		$cost_counts = null;
	}
	$c = false;
	$x = 0;
	while(count($draft) < $num) {
		
		$x++;
		$c = current($pool);
		next($pool);
		if(empty($c)){
			reset($pool);
			$c = current($pool);
		}
		if($x > 20000 OR empty($pool)){
			return "Failed to draft with this pool and these rules ($x)".PHP_EOL.count($draft);
		}
		if(!empty($cost_counts) AND $cost_counts[$costs[$c->Cost]] < 1){
			# print "$x: cost fail<br/>";
			continue;
		}
		if(!empty($rarity_counts) AND $rarity_counts[$c->Rarity] < 1){
			# print "$x: Rarity fail<br/>";
			continue;
		}
		if(in_array($c->Title,$names)){
			# print "$x: name fail<br/>";
			continue;
		}
		if(is_array($rarities)){
			$rarities[] = $c->Rarity;
		}
		$names[] = $c->Title;
		$draft[] = $c;
		if(isset($rarity_counts)){
			$rarity_counts[$c->Rarity]--;
		}
		if(isset($cost_counts)){
			$cost_counts[$costs[$c->Cost]]--;
		}
		
		unset($pool[array_search($c,$pool)]);
		
		
	}
		
	$drafts = array();
	$drafts[] = $draft;
	
	if($teams > 1){		
		$drafts = array_merge($drafts,draft($num,$pool,$teams-1,$cost,$rarities));
	}
	
	return $drafts;
}


?>