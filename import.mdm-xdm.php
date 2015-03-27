<?php
require_once('xdm.lib.php');
if(empty($_GET['code'])):
?>
<center>
<h1>Generate MDM to XDM conversion code</h1>
<form action='' method='GET'>
<input name='code' type='text' value="" />
<p>Paste your MDMr code in the box above and click submit to generate a code that will work in XDM.</p>
<input type='submit' />
</form>
</center>
<pre>
<?php
endif;
if(!empty($_GET['code'])):
	$oldCode = $_GET['code'];

	$converter = new Conversion($oldCode);
	$string = $converter->saveString();
?>
</pre>
<center>
<h1>MDM to XDM conversion code</h1>
<input type='text' onclick='this.select()' value="<?php echo $string ?>" />
<p>Click in the text box to select your code. Copy and paste it into the Load From Code field in the load menu to import your collection over to <a href='http://gotu-game.com/XDM/'>XDM</a></p>
</center>
<?php endif; ?>
<!--
SetFactory{
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
}
if($_REQUEST['q'] === 'test_draft'){

		/*	$rules = 
			[
				team size: default 6,
				number of teams: default 1,
				balance cost?: default false,
				balance rarities?:  default false (recursive call: array(rarities)) 
			]
		*/		
		$json = '
		{
			"teamSize": 6,
			"teamCount": 2,
			"balanceRarity": false,
			"balanceCost": true
		}';
		$request 	=  json_decode('{
    "name": "My Collection",
    "cards": [
        {
            "in": true,
            "Number": "1",
            "Rarity": "Starter",
            "Title": "Angel",
            "SubTitle": "Air Transport",
            "Cost": 3,
            "id": "2.1.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "63",
            "Rarity": "Uncommon",
            "Title": "Angel",
            "SubTitle": "Flying High",
            "Cost": 3,
            "id": "2.63.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "2",
            "Rarity": "Starter",
            "Title": "Angel",
            "SubTitle": "Inspiring",
            "Cost": 3,
            "id": "2.2.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "3",
            "Rarity": "Starter",
            "Title": "Angel",
            "SubTitle": "Superhero",
            "Cost": 3,
            "id": "2.3.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "35",
            "Rarity": "Common",
            "Title": "Ant-Man",
            "SubTitle": "Biophysicist",
            "Cost": 2,
            "id": "2.35.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "64",
            "Rarity": "Uncommon",
            "Title": "Ant-Man",
            "SubTitle": "Pym Particles",
            "Cost": 3,
            "id": "2.64.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "65",
            "Rarity": "Uncommon",
            "Title": "Angel",
            "SubTitle": "Avenging Angel",
            "Cost": 3,
            "id": "1.65.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "35",
            "Rarity": "Common",
            "Title": "Angel",
            "SubTitle": "High Ground",
            "Cost": 3,
            "id": "1.35.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "99",
            "Rarity": "Uncommon",
            "Title": "Angel",
            "SubTitle": "Soaring",
            "Cost": 2,
            "id": "1.99.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "1",
            "Rarity": "Starter",
            "Title": "Beast",
            "SubTitle": "Big Boy Blue",
            "Cost": 2,
            "id": "1.1.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "2",
            "Rarity": "Starter",
            "Title": "Beast",
            "SubTitle": "Genetic Expert",
            "Cost": 2,
            "id": "1.2.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "66",
            "Rarity": "Uncommon",
            "Title": "Beast",
            "SubTitle": "Kreature",
            "Cost": 3,
            "id": "1.66.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "3",
            "Rarity": "Starter",
            "Title": "Beast",
            "SubTitle": "Mutate #666",
            "Cost": 2,
            "id": "1.3.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "100",
            "Rarity": "Rare",
            "Title": "Black Widow",
            "SubTitle": "Killer Instinct",
            "Cost": 2,
            "id": "1.100.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "36",
            "Rarity": "Common",
            "Title": "Black Widow",
            "SubTitle": "Natural",
            "Cost": 2,
            "id": "1.36.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "129",
            "Rarity": "Super Rare",
            "Title": "Black Widow",
            "SubTitle": "Tsarina",
            "Cost": 2,
            "id": "1.129.4",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "4",
            "Rarity": "Starter",
            "Title": "Captain America",
            "SubTitle": "American Hero",
            "Cost": 4,
            "id": "1.4.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "5",
            "Rarity": "Starter",
            "Title": "Captain America",
            "SubTitle": "Natural Leader",
            "Cost": 4,
            "id": "1.5.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "67",
            "Rarity": "Uncommon",
            "Title": "Captain America",
            "SubTitle": "Sentinel of Liberty",
            "Cost": 6,
            "id": "1.67.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "6",
            "Rarity": "Starter",
            "Title": "Captain America",
            "SubTitle": "Star-Spangled Avenger",
            "Cost": 5,
            "id": "1.6.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "10",
            "Rarity": "OP",
            "Title": "Colossus",
            "SubTitle": "Phoenix Force",
            "Cost": 8,
            "id": "1.10.5",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "101",
            "Rarity": "Rare",
            "Title": "Colossus",
            "SubTitle": "Piotr Rasputin",
            "Cost": 7,
            "id": "1.101.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "68",
            "Rarity": "Uncommon",
            "Title": "Colossus",
            "SubTitle": "Russian Bear",
            "Cost": 7,
            "id": "1.68.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "37",
            "Rarity": "Common",
            "Title": "Colossus",
            "SubTitle": "Unstoppable",
            "Cost": 6,
            "id": "1.37.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "69",
            "Rarity": "Uncommon",
            "Title": "Cyclops",
            "SubTitle": "If Looks Could Kill",
            "Cost": 7,
            "id": "1.69.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "12",
            "Rarity": "OP",
            "Title": "Cyclops",
            "SubTitle": "Phoenix Force",
            "Cost": 8,
            "id": "1.12.5",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "102",
            "Rarity": "Rare",
            "Title": "Cyclops",
            "SubTitle": "Scott Summers",
            "Cost": 7,
            "id": "1.102.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "38",
            "Rarity": "Common",
            "Title": "Cyclops",
            "SubTitle": "Slim",
            "Cost": 5,
            "id": "1.38.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "39",
            "Rarity": "Common",
            "Title": "Deadpool",
            "SubTitle": "Assassin",
            "Cost": 4,
            "id": "1.39.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "103",
            "Rarity": "Rare",
            "Title": "Deadpool",
            "SubTitle": "Chiyonosake",
            "Cost": 5,
            "id": "1.103.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "70",
            "Rarity": "Uncommon",
            "Title": "Deadpool",
            "SubTitle": "Jack",
            "Cost": 5,
            "id": "1.70.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "71",
            "Rarity": "Uncommon",
            "Title": "Doctor Doom",
            "SubTitle": "Nemesis",
            "Cost": 6,
            "id": "1.71.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "40",
            "Rarity": "Common",
            "Title": "Doctor Doom",
            "SubTitle": "Reed Richards\' Rival",
            "Cost": 5,
            "id": "1.40.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "104",
            "Rarity": "Rare",
            "Title": "Doctor Doom",
            "SubTitle": "Victor",
            "Cost": 6,
            "id": "1.104.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "72",
            "Rarity": "Uncommon",
            "Title": "Doctor Octopus",
            "SubTitle": "Fully Armed",
            "Cost": 6,
            "id": "1.72.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "105",
            "Rarity": "Rare",
            "Title": "Doctor Octopus",
            "SubTitle": "Mad Scientist",
            "Cost": 6,
            "id": "1.105.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "41",
            "Rarity": "Common",
            "Title": "Doctor Octopus",
            "SubTitle": "Megalomaniac",
            "Cost": 6,
            "id": "1.41.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "73",
            "Rarity": "Uncommon",
            "Title": "Doctor Strange",
            "SubTitle": "Master of the Mystic Arts",
            "Cost": 6,
            "id": "1.73.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "106",
            "Rarity": "Rare",
            "Title": "Doctor Strange",
            "SubTitle": "Probably a Charlatan",
            "Cost": 7,
            "id": "1.106.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "42",
            "Rarity": "Common",
            "Title": "Doctor Strange",
            "SubTitle": "Sorcerer Supreme",
            "Cost": 7,
            "id": "1.42.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "43",
            "Rarity": "Common",
            "Title": "Gambit",
            "SubTitle": "Ace in the Hole",
            "Cost": 3,
            "id": "1.43.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "107",
            "Rarity": "Rare",
            "Title": "Gambit",
            "SubTitle": "Cardsharp",
            "Cost": 5,
            "id": "1.107.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "74",
            "Rarity": "Uncommon",
            "Title": "Gambit",
            "SubTitle": "Le Diable Blanc",
            "Cost": 5,
            "id": "1.74.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "108",
            "Rarity": "Rare",
            "Title": "Ghost Rider",
            "SubTitle": "Brimstone-Biker",
            "Cost": 4,
            "id": "1.108.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "44",
            "Rarity": "Common",
            "Title": "Ghost Rider",
            "SubTitle": "Johnny Blaze",
            "Cost": 2,
            "id": "1.44.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "75",
            "Rarity": "Uncommon",
            "Title": "Ghost Rider",
            "SubTitle": "Spirit of Vengeance",
            "Cost": 4,
            "id": "1.75.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "130",
            "Rarity": "Super Rare",
            "Title": "Green Goblin",
            "SubTitle": "Gobby",
            "Cost": 3,
            "id": "1.130.4",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "45",
            "Rarity": "Common",
            "Title": "Green Goblin",
            "SubTitle": "Goblin-Lord",
            "Cost": 3,
            "id": "1.45.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "109",
            "Rarity": "Rare",
            "Title": "Green Goblin",
            "SubTitle": "Norman Osborn",
            "Cost": 4,
            "id": "1.109.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "76",
            "Rarity": "Uncommon",
            "Title": "Hawkeye",
            "SubTitle": "Br\'er Hawkeye",
            "Cost": 3,
            "id": "1.76.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "46",
            "Rarity": "Common",
            "Title": "Hawkeye",
            "SubTitle": "Longbow",
            "Cost": 4,
            "id": "1.46.1",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "110",
            "Rarity": "Rare",
            "Title": "Hawkeye",
            "SubTitle": "Robin Hood",
            "Cost": 3,
            "id": "1.110.3",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "7",
            "Rarity": "Starter",
            "Title": "Hulk",
            "SubTitle": "Anger Issues",
            "Cost": 7,
            "id": "1.7.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "8",
            "Rarity": "Starter",
            "Title": "Hulk",
            "SubTitle": "Annihilator",
            "Cost": 6,
            "id": "1.8.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "77",
            "Rarity": "Uncommon",
            "Title": "Hulk",
            "SubTitle": "Green Goliath",
            "Cost": 6,
            "id": "1.77.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "9",
            "Rarity": "Starter",
            "Title": "Hulk",
            "SubTitle": "Jade Giant",
            "Cost": 6,
            "id": "1.9.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "10",
            "Rarity": "Starter",
            "Title": "Human Torch",
            "SubTitle": "Flame On!",
            "Cost": 4,
            "id": "1.10.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "78",
            "Rarity": "Uncommon",
            "Title": "Human Torch",
            "SubTitle": "Johnny Storm",
            "Cost": 4,
            "id": "1.78.2",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "11",
            "Rarity": "Starter",
            "Title": "Human Torch",
            "SubTitle": "Matchstick",
            "Cost": 3,
            "id": "1.11.0",
            "cards": 1,
            "dice": 1
        },
        {
            "in": true,
            "Number": "12",
            "Rarity": "Starter",
            "Title": "Human Torch",
            "SubTitle": "Playing with Fire",
            "Cost": 2,
            "id": "1.12.0",
            "cards": 1,
            "dice": 1
        }
    ]
}');	print "<pre>";
		$rules		= json_decode($json);

		$request->rules = $rules;
		
		$draft 	= new Draft($request->cards,$request->rules);
		print json_encode($draft->result());
		print "</pre>";
	}

-->