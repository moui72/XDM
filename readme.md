# XDM: A Dicemasters Randomizer

## Draft algorithm


  draft(pool, rules, passedRarities = false){
	if(balanceCost){
		costs = array(
			cheap	= range(1,3);
			middle	= range(4,5);
			high	= range(6,20);
		)
		
		binSize = floor(teamSize/3);
		remainder = teamSize % 3;
		
		// should remainder be allocated to cheap or middle?
		costCounts = array(cheap = binSize + remainder, middle = binsize, high = binSize);
	}else{
		costs = false;
	}
	if(balanceRarity){
		rarities = array(common=>0,uncommon=>0,rare=>0,superRare=>0);
		rarities[starter] = rarities[common];
	}else{
		rarities = false;
	}
	
	draft = rare();
	
	shuffle(pool);
	
	while(count(draft)<teamSize){
		if(costCounts){
			nextCost = array_search(costCounts,min(costCounts));
		}
		// while we want to balance cost AND balance rarity AND ( the current pick's cost is not the desired cost OR the current pick's rarity is not the desired rarity)
		while(costs AND rarities AND (!inArray(pool[0]->cost,costs[nextCost]) OR !in_array(pool[0]->rarity,passedRarities))){
			shuffle(pool);
		}
		pick = array_shift(pool);
		
		draft[] = pick;
		if(!empty(rarities)){
			rarities[pick->rarity]++;
		}
		if(!empty(passedRarities)){
			unset(passedRarities[array_search(pick->rarity),passedRarities]);
		}
	}
	
	drafts[]=draft;
	if(teamCount > 1){
		drafts = array_merge(drafts,draft(pool,rules,rarities));
	}
	return drafts;
	
  }
