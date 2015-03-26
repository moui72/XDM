(function() {
	var app = angular.module('xdm', ["xeditable","yaru22.md","LocalStorageModule"]);
	// yaru22.md is angular markdown parser using marked.js
	
	app.run(function(editableOptions) {
	  editableOptions.theme = 'bs3'; // bootstrap3 theme. Can be also 'bs2', 'default'
	});
	
	app.config(function (localStorageServiceProvider) {
	  localStorageServiceProvider
		.setPrefix('xdm')
		.setNotify(true, true)
	});
		

	
	app.factory('AJAXService',function($http, $q){
		return{
		  apiPath:'xdm.json-server.php',
		  getSaveString: function(d){
			//Creating a deferred object
			var deferred = $q.defer();
	 
			//Calling Web API to fetch shopping cart items
			$http.post(this.apiPath+'?q=save', JSON.stringify(d)).success(function(data){
			  //Passing data to deferred's resolve function on successful completion
			  deferred.resolve(data);
		    }).error(function(){
	 
			  //Sending a friendly error message in case of failure
			  deferred.reject("An error occurred while generating paste code.");
		    });
	 
		    //Returning the promise object
		    return deferred.promise;
		  },
		  load: function(d){
			//Creating a deferred object
			var deferred = $q.defer();
	 
			//Calling Web API to fetch shopping cart items
			$http.post(this.apiPath+'?q=load', JSON.stringify(d)).success(function(data){
			  //Passing data to deferred's resolve function on successful completion
			  deferred.resolve(data);
		  }).error(function(){
	 
			//Sending a friendly error message in case of failure
			deferred.reject("An error occurred while trying to load collection.");
		  });
	 
		  //Returning the promise object
		  return deferred.promise;
		  },
		  draft: function(d){
			  console.log("Sending draft request.");
			//Creating a deferred object
			var deferred = $q.defer();
	 
			//Calling Web API to fetch shopping cart items
			$http.post(this.apiPath+'?q=draft', JSON.stringify(d)).success(function(data){
			  //Passing data to deferred's resolve function on successful completion
			  deferred.resolve(data);
		  }).error(function(){	 
			//Sending a friendly error message in case of failure
			deferred.reject("An error occurred while drafting.");
		  });
	 
		  //Returning the promise object
		  return deferred.promise;
		},
		getAllItems: function(){
		  //Creating a deferred object
		  var deferred = $q.defer();
	 
		  //Calling Web API to fetch shopping cart items
		  $http.get(this.apiPath+'?q=all').success(function(data){
			  //Passing data to deferred's resolve function on successful completion
			  deferred.resolve(data);
		  }).error(function(){	 
		    //Sending a friendly error message in case of failure
		    deferred.reject("An error occurred while fetching items");
		  });	 
		  //Returning the promise object
		  return deferred.promise;
		}
	  };
	});

//- Controllers
		
	// Controls active collection: this is the top-level controller
	function CollectionController($scope,AJAXService,localStorageService){
		this.collection 	= {"name" : "My Collection", "cards" : []};		
		$scope.AJAXData 	= {"name" : "Load failed!", "cards" : []};		
		this.saved 			= false;
		this.colList		= {};
		this.colListCount	= [];
		this.active			= false;
		
		
		$scope.pasteCode 	= '';
		$scope.loading 		= false;
		
		if(!localStorageService.isSupported) {
			console.log("No local storage.");
			this.storage = false;
		}else{
			console.log("Using local storage.");
			this.storage		= true;
		}
		
		this.onLoad = function(){
			this.updateColList();
			this.getLast();
			if(this.active){
				this.collection = this.active;
			}
		}
		
		this.getLast = function(){
			try{
				last = localStorageService.get('last');
				if(angular.isDefined(last)){
					console.log("Loading last used collection.");
					this.active = localStorageService.get(last);
				}
			}catch(err){
				console.log("No pre-existing save data ("+err.message+").");
			}
		}
				
		this.saveCollection = function(){
			newSave = this.collection;
			try{
				oldSave = localStorageService.get(newSave.name);
				if(angular.isDefined(oldSave)){
					console.log("Overwriting previously saved collection \""+this.collection.name+"\"");
				}
			}catch(err){
				console.log("No pre-existing save data ("+err.message+").");
			}
			
			localStorageService.set(newSave.name, newSave);
			console.log("Saving collection: \""+this.collection.name+"\"");
			this.saved = true;
			localStorageService.set('last', newSave.name);
			this.updateColList();
			
			// add past code functionality
		}	
			
		this.updateColList 		= function(){
			delete this.colList;
			this.colList = {};
			var newColList = localStorageService.keys();
			console.log("Saved collections: ");
			for(var colIndex in newColList){
				var col = localStorageService.get(newColList[colIndex]);
				console.log(col.name);
				this.colList[colIndex] = {"name" : col.name, "cards" : col.cards};
			}
			this.colListCount = Object.keys(this.colList).length;
			return true;
		}
		
		this.clearCol = function(){
			this.collection.cards = [];
			this.collection.name = "Blank Collection";
			this.saved = false;
		}
		
		this.clearAllSavedData = function(){
			console.log("Deleting all data...");
			console.log(localStorageService.clearAll());
			this.updateColList();
		}
		
		this.loadFromSave = function(loadByName){
			string = '';
			loadData = localStorageService.get(loadByName);
			this.collection.name  = loadData.name;
			this.collection.cards = loadData.cards;
			localStorageService.set('last', loadByName);
			this.updateColList();
			
		}
		
		this.killSave = function(deleteByName){
			localStorageService.remove(deleteByName);
			this.updateColList();
		}		
		
		this.loadFromCode = function(string){
			data = string.replace(" ","+");			
			AJAXService.load(data).then(function(res){
				console.log($scope);
				$scope.AJAXData = res;
				$scope.colDB.loadAJAXData();
			},
			function(errorMessage){
				$scope.error=errorMessage;
			});
		}
		
		this.loadAJAXData = function(){
			this.collection.name  = $scope.AJAXData.name;
			this.collection.cards = $scope.AJAXData.cards;
		}

		$scope.loadAJAXData = this.loadAJAXData;
		
		this.addCard = function(card){
			if(!this.inCollection(card)){
				card.cards = 1;
				card.dice = 1;
				card.in = true;
				this.collection.cards.push(card);
				console.log("Added \""+card.Title+', '+card.SubTitle+'\" to '+this.collection.name+'.');
				this.saved = false;
				return true;
			}
			console.log("\""+card.Title+', '+card.SubTitle+'\" was already in '+this.collection.name+'.');
			return false;
		}
		
		this.dropCard = function(card){
			var index = this.collection.cards.indexOf(card);
			if(index !== -1){
				this.collection.cards.splice(index, 1);
				console.log("Dropped \""+card.Title+', '+card.SubTitle+'\" from '+this.collection.name+'.');
				this.saved = false;
				return true;
			}
			console.log("\""+card.Title+', '+card.SubTitle+'\" was not in '+this.collection.name+'.');
			return false;
		}
		
		this.inCollection = function(card){
			if(angular.isDefined(card) && this.collection.cards.length > 0){
				for(var i = 0; i<this.collection.cards.length; i++){
					if(card.id === this.collection.cards[i].id){
						return true;
					}
				}
			}
			return false;
		}
		this.onLoad();
		
	}
	app.controller("CollectionController",CollectionController); 
	
	// Controls tab views
	function TabController(){
		this.tab = 1;
		this.miniTop = false;
		
		this.isSet = function(checkTab) {
		  return this.tab === checkTab;
		};

		this.setTab = function(setTab) {
		  this.tab = setTab;
		}
	}
	app.controller("TabController", TabController); 

	// Controls column views
	function ColumnController(){
		this.column = [1,2];

		this.isSet = function(checkColumn) {
			if(this.column.indexOf(checkColumn) != -1){
				return true;
			}
			return false;
		};

		this.maxColumn = function(maxColumn) {
			console.log("Maximizing column "+maxColumn+"...");
			if(!this.isSet(maxColumn)){
				this.column.push(maxColumn);
			}
		}
		
		this.minColumn = function(minColumn){
			console.log("Minimizing column "+minColumn+"...");
			if(this.column.length<2){
				this.reset();
				return false;
			}
			if(this.isSet(minColumn)){
				this.column.splice(this.column.indexOf(minColumn),1);
				if(minColumn === 1){
					this.maxColumn(2);
				}else{
					this.maxColumn(1);	
				}
			}
		}
		
		this.reset = function(){
			this.column = [1,2];
		}
	}
	app.controller("ColumnController", ColumnController); 
	
	// Controls drafts
	function DraftController($scope,AJAXService){
		$scope.draft = {};
		$scope.rules = {
			'teamSize' : 6,
			'teamCount': 2,
			'balanceCost': true,
			'balanceRarity': false
		};
		$scope.loading 		= false;
		$scope.drafted 		= false;
		$scope.draftedFail 	= false;
		this.draftList		= true;
		
						
		this.draft = function(col){
			$scope.loading = true;
			draftData = {};
			draftData.cards = col.cards;
			draftData.rules = $scope.rules;
			console.log('Drafting from '+JSON.stringify(col)+'...');
			/*	$rules = 
			[
						team size: default 6,
						number of teams: default 1,
						balance cost?: default false,
						balance rarities?:  default false (recursive call: array(rarities)) 
			]
			*/

			AJAXService.draft(draftData).then(function(data){
				console.log("Response received...");
				
				if(angular.isDefined(data) && data.length > 0 && data[0] != 'fail'){
					$scope.draft 	= data;
					var message = "Success.";
					$scope.drafted 	= true;
					$scope.draftedFail = false;	
				}else{
					var message = "Failure."
					$scope.drafted 	= false;
					$scope.draftedFail = true;
				}
				console.log(message);
			},
			function(errorMessage){
				$scope.error=errorMessage;
			});
			$scope.loading 	= false;
		}
		this.sayRules = function(){
			console.log("Rules: "+JSON.stringify($scope.rules));
		}
	}
	app.controller("DraftController",DraftController); 
		
	// Controls card database
	function DataController($scope, AJAXService, $filter) {
		$scope.sets = [];		
		$scope.loading = true;
				
		$scope.setsMap = [
			{	value: 0, 	text: ''	},
			{	value: 1, 	text: ''	},
			{	value: 2, 	text: ''	},
			{	value: 3, 	text: ''	}
		];		
		
		$scope.user = {
			sortBy: 1,
			activeSet: 1			
		}; 
	
		
		$scope.user.set = $scope.sets[$scope.user.activeSet];
		
		 $scope.refreshItems = function (){
			$scope.loading = true;
			AJAXService.getAllItems().then(function(data){
				$scope.sets = data;	
				$scope.user.set = $scope.sets[$scope.user.activeSet];
				var map = [];
				angular.forEach($scope.sets, function (set) {
					angular.forEach(set.cards, function(card){
					  card.Cost = parseFloat(card.Cost);
					});
				});
				for(var i = 0;i<$scope.sets.length;i++){
					map.push({value: i, text: $scope.sets[i].name});
					
				}
				$scope.setsMap	= map;				
			},
			function(errorMessage){
				$scope.error = errorMessage;
			});		
			$scope.loading = false;
		}		
		$scope.refreshItems();
	
		
		$scope.sorts = [
			{	value: 1, 	text: 'Title',	query: ['Title',			'SubTitle']	},
			{	value: 2, 	text: 'Rarity',	query: ['Rarity','Title',	'SubTitle']	},
			{	value: 3, 	text: 'Cost',	query: ['Cost','Title',		'SubTitle']	}
		];
		
		$scope.RaritySortFunction = function (card){
			var rarityMap = { 
				'Starter' : 0, 
				'Common' : 1, 
				'Uncommon' : 2, 
				'Rare' : 3, 
				'Super Rare' : 4, 
				'OP' : 5
			};
			return rarityMap[card.Rarity];
		}
		
		$scope.predicate 	= ['Title','SubTitle'];
		$scope.reverse 		= false;
		
		$scope.sortSet = function(rev){
			$scope.loading = '/XDM/img/ajax-loader.gif';
			var selected = $filter('filter')($scope.sorts, {value: $scope.user.sortBy});
			$scope.predicate = selected[0].query;
			if(typeof rev !== -1){
				if($scope.reverse){
					$scope.reverse 	= false;
				}else{
					$scope.reverse 	= true;
				}
			}
			$scope.loading = false;
		}

		$scope.showSets = function () {
			var selected = $filter('filter')($scope.setsMap, {value: $scope.user.activeSet});
			return ( $scope.user.activeSet !== -1 ) ? selected[0].text : 'some';
		};
		
		$scope.showSorts = function () {
			var selected = $filter('filter')($scope.sorts, {value: $scope.user.sortBy});
			return ($scope.user.sortBy !== -1 && selected.length) ? selected[0].text : 'Not set';
		};
		$scope.isST = function(item) {
		  return !(item.SubTitle === null);
		}		
	};
	app.controller("DataController",DataController); 
	
	
// - Directives
	app.directive('card', function() {
	  return {
		restrict: 'E',
		templateUrl: 'templates/card.html'
	  };
	});
	
	app.directive('about', function() {
	  return {
		restrict: 'E',
		templateUrl: 'templates/about.html'
	  };
	});

	
	app.directive('collection', function() {
	  return {
		restrict: 'E',
		templateUrl: 'templates/collection.html'
	  };
	});

	
	
})();
