(function() {
	var app = angular.module('xdm', ["xeditable","ipCookie","yaru22.md"]);
	
	app.run(function(editableOptions) {
	  editableOptions.theme = 'bs3'; // bootstrap3 theme. Can be also 'bs2', 'default'
	});
	
	// services
	app.service('CollectionDataService', function(ipCookie){
		var name = 'My Collection';
		var cards = [];
		this.saveString = '';
		this.saved = false;
		this.loaded = false;
		this.LoadResponse = '';
		
		this.getName = function(){
			return name;
		}
		this.getCards = function(){
			return cards;
		}
		
		this.clearCol = function(){
			// remove all cards from collection
			cards = [];
			this.saveState(false);
		}		
		
		this.saveState = function(isSaved){
			this.saved = isSaved;
		}
		
		this.setName = function(colName){
			//Creating a deferred object
			var response = 'Setting collection name to '+colName;
			name = colName;
			this.saveState(false);
			return response;
		}
		
		this.addCard = function(card){
			if(!this.inCollection(card)){
				card.cards = 1;
				card.dice = 1;
				card.in = true;
				cards.push(card);
				console.log("Added \""+card.Title+', '+card.SubTitle+'\" to '+name+'.');
				this.saveState(false);
				return true;
			}
			console.log("\""+card.Title+', '+card.SubTitle+'\" was already in '+name+'.');
			this.saveState(false);
			return false;
		}
		
		this.dropCard = function(card){
			var index = this.cards.indexOf(card);
			if(index !== -1){
				cards.splice(index, 1);
				console.log("Dropped \""+card.Title+', '+card.SubTitle+'\" from '+name+'.');
				this.saveState(false);
				return true;
			}
			console.log("\""+card.Title+', '+card.SubTitle+'\" was not in '+name+'.');
			return false;
		}
		
		this.inCollection = function(card){
			if(angular.isDefined(card) && cards.length > 0){
				for(var i = 0; i<cards.length; i++){
					if(card === cards[i]){
						return true;
					}
				}
			}
			return false;
		}
				
		this.validNumber = function(card){
			var fix = '';
			if(isNaN(card.cards)){
				card.cards = 0;
				var fix = 'card';
			}
			if(isNaN(card.dice)){
				card.dice = 0;
				var dfix = 'die';
			}
			if(fix !== ''){
				console.log('Invalid '+fix+' count: '+card.Title+', '+card.SubTitle+' now has 0 '+fix);
			}else{
				this.announceCounts(card);
			}
		}
		
		this.announceCounts = function(card){
			console.log(card.Title+', '+card.SubTitle+' now has '+card.cards+' cards and '+card.dice+' dice');
		}
	
	});
	
	app.factory('AJAXService',function($http, $q){
		return{
		  apiPath:'json.serve.php',
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

	// Controllers
		
	// Controls active collection: this is the top-level controller
	function CollectionController($scope,ipCookie,AJAXService,CollectionDataService){
		this.collection = {};
		
		this.updateCol = function(){
			this.collection.name = CollectionDataService.getName();
			this.collection.cards = CollectionDataService.getCards();
			console.log(this.collection);
		}
		
		this.addCard = function(card){
			return CollectionDataService.addCard(card);
			this.updateCol();
		}
		
		this.dropCard = function(card){
			return CollectionDataService.dropCard(card);
			this.updateCol();
		}
		
		this.inCollection = function(card){
			return CollectionDataService.inCollection(card);
		}
		
		this.setColName = function(){
			console.log(CollectionDataService.setName(this.collection.name));
		}
		
		this.updateCol();
		
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
	
	// Controls saving/loading collections
	function IOController($scope,ipCookie,AJAXService,CollectionDataService){
		
		this.collectionList = {};
		this.collectionCount = 0;	
		$scope.pasteCode = '';
		$scope.loading = false;
		
		this.saved = function(){
			return CollectionDataService.saved;
		}
		
		this.pasteCode = function(){
			return this.pasteCode;
		}
		
		this.saveCollection = function(){
			// current collection data to save
			var name = CollectionDataService.getName();
			var cards = CollectionDataService.getCards();
			collectionData = {'name' : name, 'cards' : cards, 'last' : true};
			console.log("Saving "+collectionData.name+" to a cookie.");
			
			var cookie = ipCookie('collections');
			
			if(angular.isDefined(cookie)){
				// user data exists, update existing cookie
				if(angular.isUndefined(cookie.data[collectionData.name])){
					cookie.count++;
				}
				cookie.data[collectionData.name] = collectionData;
			}else{
				var collections = {'count' : 0, 'data' : {}};
				collections.data[collectionData.name] = collectionData;
				// no user data yet, make a cookie
				cookie = {};
				cookie = collections;
				cookie.count++;
			}
			
			// save this collection to cookie with key 'last' and to cookie with key '<collectionData.name>'
			ipCookie('collections', cookie, { expires: 365*21 });
			
			// provide saveString for backing up collections
			AJAXService.getSaveString(collectionData).then(function(res){
				$scope.pasteCode  =  res;
				CollectionDataService.saveState(true);
				console.log("Displaying "+collectionData.name+" as a paste code for back-up ("+$scope.pasteCode+").");
			},
			function(errorMessage){
				$scope.error=errorMessage;
			});
			
			this.updateColList();
			
			$scope.loading = false;
		}
	
		this.updateColList = function(){
			// stores contents of 'col' cookie; list of existing collection cookies
			var cookies 	= ipCookie('collections');
			if(angular.isDefined(cookies)){
				var cnt 		= 0;
				this.collectionList = {};
				console.log(cookies.data);
				for(var name in cookies.data){
					this.collectionList[name] = cookies.data[name];
					cnt++;
				}
				this.collectionCount = cnt;
				return true;
			}
			return false;
		}
		
		this.loadFromCookie = function(cookie){
			// load a collection from a cookie
			CollectionDataService.clearCol();	// first, make active collection empty
			CollectionDataService.setName(cookie.name);	// load name
			CollectionDataService.setCards(cookie.cards); // load cards
			this.loading = false;
			CollectionDataService.saveState(false);
		}
		
		this.killCookie = function(cookie){
			//delete an existing collection cookie
			cols = ipCookie('collections');
			if(angular.isDefined(cols.data[cookie.name])){
				delete cols.data[cookie.name];
				cols.count--;
				console.log('Deleted collection: '+cookie.name);
				ipCookie('collections',cols, {expires: 365*21}	);
				this.updateColList();
				return true;
			}else{
				console.log('Could not delete collection: '+cookie.name);
				return false
			}
		}		
		
		this.loadFromCode = function(string){
			data = string.replace(" ","+");			
			AJAXService.load(data).then(function(res){
				this.collection.name = res.name;
				this.collection.cards = res.cards || [];
				CollectionDataService.loaded = true;
			},
			function(errorMessage){
				$scope.error=errorMessage;
			});
			$scope.saveState = false;
		}
		
		this.updateColList();
		this.loading = false;
	}
	app.controller("IOController",IOController); 

	
	// Controls drafts
	function DraftController($scope,AJAXService){
		$scope.draft = {};
		$scope.rules = {
			'teamSize' : 6,
			'teamCount': 2,
			'balanceCost': true,
			'balanceRarity': false
		};
		$scope.drafted 		= false;
		$scope.draftedFail 	= false;
		this.draftList = true;
		
						
		this.draft = function(){
			col = {};
			col.name = $scope.collection.name;
			col.cards = $scope.collection.cards;
			console.log('Drafting...');
			/*	$rules = 
			[
						team size: default 6,
						number of teams: default 1,
						balance cost?: default false,
						balance rarities?:  default false (recursive call: array(rarities)) 
			]
			*/
			col.rules =  $scope.rules;

			AJAXService.draft(col).then(function(data){
				console.log("Response recieved...");
				
				if(angular.isDefined(data) && data.length > 0){
					$scope.draft 	= data;
					var message = "Good draft.";
					$scope.drafted 	= true;
					$scope.draftedFail = false;	
				}else{
					var message = "Bad draft."
					$scope.drafted 	= false;
					$scope.draftedFail = true;
					console.log(data);
				}
				console.log(message+" Data: "+$scope.draft+" Drafted? "+$scope.drafted+" Failed? "+$scope.draftedFail);
			},
			function(errorMessage){
				$scope.error=errorMessage;
			});
		}
		this.sayRules = function(){
			console.log("Rules: "+JSON.stringify($scope.rules));
		}
	}
	app.controller("DraftController",DraftController); 
		
	// Controls card database
	function DataController($scope, AJAXService, $filter) {
		$scope.collectionView = false;
		$scope.sets = [];		
				
		function refreshItems(){
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
		}		
		refreshItems();
		
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
		
		$scope.sorts = [
			{	value: 1, 	text: 'Title',	query: ['Title','SubTitle']	},
			{	value: 2, 	text: 'Rarity',	query: ['Rarity','Title','SubTitle']	},
			{	value: 3, 	text: 'Cost',	query: ['Cost','Title','SubTitle']		}
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
			return rarityMap[card.Rarity] || -1;
		}
		
		$scope.predicate 	= ['Title','SubTitle'];
		$scope.reverse 		= false;
		
		$scope.sortSet = function(rev){
			var selected = $filter('filter')($scope.sorts, {value: $scope.user.sortBy});
			$scope.predicate = selected[0].query;
			if(typeof rev !== -1){
				if($scope.reverse){
					$scope.reverse 	= false;
				}else{
					$scope.reverse 	= true;
				}
			}
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
	
	app.directive('readme', function() {
	  return {
		restrict: 'E',
		templateUrl: 'templates/readme.html'
	  };
	});
	
	
})();
