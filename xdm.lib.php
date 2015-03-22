<?php


abstract class SetFactory{
	
	function dummyCol(){
		$sets = SetFactory::allSets();
		$cards = new Collection();
		$cards->cards = array();
		$cards->name = 'dummy col';
		$n = mt_rand(1,3);

		$cards->cards = array_merge($cards->cards, $sets[$n]->cards());

		foreach($cards->cards as $index => $obj){
			$obj->cards = 2;
		}
		return $cards;
	}
	
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
		foreach($col as $card){
			
			$idArr = explode('.',$card->id);
			$set	= $idArr[0];
			$num	= $idArr[1];
			$rarity = $idArr[2];
			foreach($allSets[$set]->cards() as $setCard){
				if($setCard->Number == $num AND array_search($setCard->Rarity,$rarities) == $rarity){
					if($forDraft){
						$i = 1;
						while($i < $card->cards){
							
							$i++;	
# print "Added $card->Title, $card->SubTitle <br/>";
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
	
	private $maxRarityBinCt	= null;	// max number of cards in each rarity bin
	private $lastRarityBinCt	= null;	// max number of cards in each rarity bin
	private $costBinCt		= null;		// number of cards in each bin
	
	function __construct($cards,$rules){
		$this->pool 		= SetFactory::poolFromCol($cards);
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
			foreach($totals as $key => $count){
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
			return $this->log;
		}
	}

}



?>