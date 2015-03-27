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
		$dataArr = array();
		// [array(Set,Universe,SetName,fileName), ...]
		
		foreach($sets as $index => $data){
			$set = SetFactory::Create($fp.$data[3],$data[0],$data[1],$data[2]);
			$dataArr[] = $set;
		}
		return $dataArr;
	}
	
	
	function allSets(){
		return SetFactory::createAll();;
	}

	
	function poolFromCol($col,$forDraft = true){
		$allSets = SetFactory::allSets();
		$pool = array();
		$rarities = CardFactory::Rarities();
		$string = '';
		foreach($col as $card){
			$idArr = explode('.',$card->id);
			$set	= $idArr[0];
			$string .= "SET: $set".PHP_EOL;
			$num	= $idArr[1];
			$string .= "NUMBER: $num".PHP_EOL;
			$rarity = $idArr[2];
			$string .= "RARITY: $rarity".PHP_EOL;
			foreach($allSets[$set]->cards() as $setCard){
				$numRar = array_search($setCard->Rarity,$rarities);
				if($setCard->Number == $num AND array_search($setCard->Rarity,$rarities) == $rarity){
				$string .= "Adding... $setCard->Title $setCard->SubTitle (NUM: $setCard->Number RAR: $numRar)".PHP_EOL;
					if($forDraft){
						$i = 0;
						while($i < $card->cards){
							$string .= "Added $setCard->Title $setCard->SubTitle (NUM: $setCard->Number RAR: $numRar)".PHP_EOL;
							$i++;	
							$setCard->id = $card->id;
							$pool[] = $setCard;
							
						}
					}else{
						if(empty($card->cards)){
							$setCard->cards = 1;
						}else{
							$setCard->cards = $card->cards;
						}
						
						if(empty($card->dice)){
							$setCard->dice = 1;
						}else{
							$setCard->dice = $card->cards;
						}
						$setCard->id = $card->id;
						$pool[] 		= $setCard;
					}
				}else{
					$string .= "Skipped $setCard->Title $setCard->SubTitle (NUM: $setCard->Number RAR: $numRar)".PHP_EOL;
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

	public $cards = array();
	
	function json(){
		return json_encode($this);
	}
	
	function add($cardNum, $amt){
		$set = SetFactory::createAll();
		$avx = $set[1]->cards();
		$added = false;
		$log = array();
		foreach($avx as $card){
			$log[] = "$card->Title, $card->SubTitle ";
			if($card->Number == $cardNum AND $card->Rarity != 'OP'){
				$log[] =  "added!";
				$newCard 		= $card;
				$card->cards 	= $amt;
				$this->cards[] 	= $newCard;
				$added 			= true;
			}else{
				$log[] =  "skipped.";
			}
		}
		return $added;
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
		if(empty($card->dice)){
			$card->dice = 1;
		}
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
	$collection->cards = SetFactory::poolFromCol($collection->cards,$forDraft = false); 
	return $collection;
	
}

class Draft{
	// this class generates team(s) based on requested draft rules
	private $pool 		= array();
	private $teamSize  	= 6; 		// team size: default 6 [num]
	private $teamCount	= 2; 		// number of teams: default 2 [teams]
	
	
	private $draftCount	= 0;	// drafts (teams) completed
	private $drafts		= array();
	
	// did it work?
	public $success = false;

	// log errors
	private $log		= array();
	
	private $maxRarityBinCt	= null;		// max number of cards in each rarity bin
	private $lastRarityBinCt	= null;	// max number of cards in each rarity bin
	private $costBinCt		= null;		// number of cards in each bin
	
	function __construct($cards,$rules){
		if(is_array($cards)){
			$this->pool 		= SetFactory::poolFromCol($cards);
		}else{
			try{
				$this->pool 		= SetFactory::poolFromCol($cards->cards);
			}catch(Exception $e){
				print "['fail','could not load collection($e)']";
			}
		}
		$this->teamSize 	= $rules->teamSize;
		$this->teamCount 	= $rules->teamCount;
		
		$this->setBins($rules);
		
		$this->draftTeams();
		if(count($this->drafts) == $this->teamCount){
			$this->success = true;
		}else{
			$this->success = false;
		}
	}
	
	function rarityCount($cards){
		$totals = array('Common' => 0, 'Uncommon' => 0, 'Rare' => 0, 'Super Rare' => 0, 'OP' => 0);
		foreach($cards as $card){
			// Count total for each rarity in collection
			if(empty($totals[$card->Rarity])){
				$totals[$card->Rarity] = 1;
			}else{
				$totals[$card->Rarity]++;
			}
		}
		return $totals;
	}
	
	function setBins($rules){
		// Cost bins
		
		if($rules->balanceCost){
			// set bin size for each cost 
			$binSize = floor($this->teamSize/3); // base bin size is 1/3 of team size rounded down
			$remainder = $this->teamSize % 3; // remainder is allocated to the cheaper bins
			
			if($remainder % 2 == 0){
				// if the remainder can be devided by 2, allocate to low and medium bins
				$splitRem = $remainder / 2;
				$remainder 		 = $remainder / 2;
			}else{
				// otherwise, just the low bin
				$splitRem = 0;
			}
			
			// QUESTION : should remainder be allocated to cheap or middle?
			$this->costBinCt = array('low'=> $binSize+$remainder, 'medium' => $binSize+$splitRem, 'high' => $binSize);	
		}
		
		// Rarity bins
		if($rules->balanceRarity){
			// first, count rarities in collection
			$maxBins = $this->rarityCount($this->pool);
			foreach($maxBins as $key => $count){
				// Max bin size for a rarity is # of rarity in collection / # of teams, rounded down
				//	(we need each card to have a matched rarity option for the other teams, e.g. 3 teams and 2 SRs means no SRs used)
				$maxBins[$key] = floor($count/$this->teamCount);
			}
			$this->maxRarityBins = $maxBins;
		}
		return true;
	}
	
	function draftTeams(){
		$draftsCountTracker = 0;
		$MAX_TRIES 			= 1000;
		while(count($this->drafts) < $this->teamCount && $draftsCountTracker < $MAX_TRIES){
			// generate drafts. stop when we've got enough, or after MAX_TRIES
			try{
				$this->draftTeam();
			}catch(Exception $e){
				$this->log[] = 'Draft failed ('.$e->getMessage().')';
			}
			$draftsCountTracker++;
			$this->log[] = "DRAFTS: $draftsCountTracker";
		}
	}
	
	function binCost($cst){
		if($cst < 4){
			return 'low';
		}
		if($cst < 7){
			return 'medium';
		}
		return 'high';
	}
		
	function draftTeam($carryOver = null){
		$maxTries = 2000;
		$usedNames = array(); // holds the names picked so far (no character dupes!)
		$draft = array(); // holds the team being picked
		if(!empty($this->maxRarityBinCt) && !empty($this->lastRarityBinCt)){
			# if this isn't the first team, get the list of rarities that are legal
			// legal rarities determined by first team
			$rarityCounts = $this->lastRarityBinCt;
		}else{
			// if it's the first team, we will build the list of legal rarities
			$rarityCounts = $this->maxRarityBinCt;
		}
		// clone the pool, shuffle it
		$pool = $this->pool;
		shuffle($pool);
		
		// clone the costBins
		//	-- costBins represents how many of each cost on EACH team, so it gets reset for each team.
		$costBins = $this->costBinCt;
		
		$ok = true;			// are we gonna use this card?
		$cardCount = 0;		// how many cards have been looked at so far this loop (reset when loop is reset)?
		$resets = 0;		// how many times has the loop reset?
		
		foreach($pool as $index => $c){
			// loop through the cards in the pool
			$cardCount++;
			$ok = true;		
			if($ok AND !empty($costBins) AND empty($c->Cost)){
				// we care about cost but the cost of this card is undefined
				$reason = 'cost not defined';
				$ok = false;
			}
			if($ok AND !empty($costBins) AND $costBins[$this->binCost($c->Cost)] < 1){
				// we care about cost but this card's cost doesn't fit in an open bin
				$reason = 'cost';
				$ok = false;
			}
			if($ok AND !empty($rarityCounts) AND $rarityCounts[$c->Rarity] < 1){
				// we care about rarity but this card's rarity doesn't fit in an open bin
				$reason = 'rarity';
				$ok = false;
			}
			if($ok AND in_array($c->Title,$usedNames)){
				// this can't go on this team because that character is already on it
				$ok = false;
				$reason = 'name';
			}
			if($ok){
				// Add card, this card works!
				$draft[] = $c;
				// add title to ban list
				$usedNames[] = $c->Title;
				if(!empty($costBins)){
					// if we care about cost, decrease cost bin this card went into
					$costBins[$this->binCost($c->Cost)]--;
				}
				if(!empty($rarityCounts)){
					// if we care about rarity, decrease rarity bin this card went into
					$rarityCounts[$this->Rarity]--;
				}
				// clear this card from pool
				unset($pool[$index]);
			}else{
				// if we're not using this card, log the reason
				$this->log[] = $reason;
			}
			if(count($draft) >= $this->teamSize){
				# SUCCESS
				// if we're done, add the draft to the list, update the pool and update the rarity bins
				$this->drafts[] = $draft;
				$this->pool = $pool;
				$this->lastRarityBinCt = $this->rarityCount($draft);
				return true;
			}
			if(count($draft)>(count($pool)-$cardCount) AND count($pool)>$this->teamSize - count($draft) AND $resets < $maxTries){
				// try again
				$resets++;
				reset($pool);
				$cardCount = 0;
			}
		}
		// give up
		return false;
	}
	
	function result(){
		if($this->success){
			return $this->drafts;
		}else{
			array_unshift($this->log,'fail');
			return $this->log;
		}
	}

}

class Conversion{

	function __construct($data){
		try{
			$string = $this->toClearString($data);
		}catch(Exception $e){
			print "Bad code (".$e->getMessage().")";
		}
		$collection = new Collection();
		$collection->name = 'Imported Collection';
		
		foreach(str_split($string) as $index => $count){
			$collection->add($index,$count);
		}
		$this->collection =  $collection;
	}

	function saveString(){
		return abstractJSON($this->collection);
	}

	function toClearString($out){
		if($string = gzuncompress(base64_decode(urldecode($out)))){
			return $string;
		}else{
			throw new Exception('invalid save code.');
		}
	}

}

?>