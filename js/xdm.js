(function() {
	var app = angular.module('xdm', ["xeditable","ipCookie","yaru22.md"]);
	
	app.run(function(editableOptions) {
	  editableOptions.theme = 'bs3'; // bootstrap3 theme. Can be also 'bs2', 'default'
	});
	
	// services
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
		  draft: function(d,r){
			  console.log("DRAFTING");
			//Creating a deferred object
			var deferred = $q.defer();
	 
			//Calling Web API to fetch shopping cart items
			$http.post(this.apiPath+'?q=draft&rules='+r, JSON.stringify(d)).success(function(data){
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
	function IOController($scope,ipCookie,AJAXService){
		
		this.collectionList = {};
		this.collectionCount = 0;	
		
		this.saveCollection = function(){
			var col = $scope.collection;
			// current collection data to save
			var collectionData = {'name' : col.name, 'cards' : col.cards, 'last' : true};
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
			this.updateColList();
			// provide saveString for backing up collections
			AJAXService.getSaveString(collectionData).then(function(res){
				$scope.saveString = res;
				$scope.saved = true;
				console.log("Displaying "+collectionData.name+" as a paste code for back-up.");
			},
			function(errorMessage){
				$scope.error=errorMessage;
			});
			
			
		}
	
		this.updateColList = function(){
			// stores contents of 'col' cookie; list of existing collection cookies
			var cookies 	= ipCookie('collections');
			if(angular.isDefined(cookies)){
				var cnt 		= 0;
				this.collectionList = {};
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
			$scope.clearCol();	// first, make active collection empty
			$scope.collection.name  = cookie.name;	// load name
			$scope.collection.cards = cookie.cards || []; // load cards
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
				$scope.collection.name = res.name;
				$scope.collection.cards = res.cards || [];
				$scope.loaded = true;
			},
			function(errorMessage){
				$scope.error=errorMessage;
			});
		}
		
		this.updateColList();
	}
	app.controller("IOController",IOController); 
	
	// Controls active collection: this is the top-level controller
	function CollectionController($scope,ipCookie,AJAXService){
		$scope.collection = {'name' : 'my collection', 'cards' : []};
		$scope.saveString = '';
		$scope.saved = false;
		$scope.loaded = false;
		$scope.LoadResponse = '';
		$scope.collectionView = true;
		
		
		this.clearCol = function(){
			// remove all cards from collection
			$scope.collection.cards = [];
		}		
		$scope.clearCol = this.clearCol;
		
		this.setName = function(colName){
			//Creating a deferred object
			var response = 'Setting collection name to '+colName;
			$scope.collection.name = colName;
		}
		
		this.addCard = function(card){
			if(!this.inCollection(card)){
				card.cards = 1;
				card.dice = 1;
				card.in = true;
				$scope.collection.cards.push(card);
				console.log("Added \""+card.Title+', '+card.SubTitle+'\" to '+$scope.collection.name+'.');
				return true;
			}
			console.log("\""+card.Title+', '+card.SubTitle+'\" was already in '+$scope.collection.name+'.');
			return false;
		}
		
		this.dropCard = function(card){
			var index = $scope.collection.cards.indexOf(card);
			if(index !== -1){
				$scope.collection.cards.splice(index, 1);
				console.log("Dropped \""+card.Title+', '+card.SubTitle+'\" from '+$scope.collection.name+'.');
				return true;
			}
			console.log("\""+card.Title+', '+card.SubTitle+'\" was not in '+$scope.collection.name+'.');
			return false;
		}
		
		this.inCollection = function(card){
			var index = $scope.collection.cards.indexOf(card);
			if(index === -1){
				return false;
			}
			return true;
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
	
	}
	app.controller("CollectionController",CollectionController); 
	
	// Controls drafts
	function DraftController($scope,AJAXService){
		$scope.draft = '';
		$scope.rules = {
			teamSize : 6,
			teamCount: 2,
			balanceCost: 'yes',
			balanceRarity: 'yes'
		};
		$scope.drafted 	= false;
		this.draftList = true;
		
						
		this.draft = function(){
			col = {};
			col.name = $scope.collection.name;
			col.cards = $scope.collectionCards();
			/*	$rules = 
			[
						team size: default 6,
						number of teams: default 1,
						balance cost?: default false,
						balance rarities?:  default false (recursive call: array(rarities)) 
			]
			*/
			rules =  $scope.rules || [6,2,'yes','yes'];
			
			AJAXService.draft(col,rules).then(function(data){
				$scope.draft 	= data;
				$scope.drafted 	= true;
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
			{	value: 1, 	text: 'Title',	query: ['Title','Subtitle']	},
			{	value: 2, 	text: 'Rarity',	query: ['Rarity','Title','Subtitle']	},
			{	value: 3, 	text: 'Cost',	query: ['Cost','Title','Subtitle']		}
		];
		
		$scope.predicate 	= 'Title';
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
		
		
	};
	app.controller("DataController",DataController); 
	
	app.directive('card', function() {
	  return {
		restrict: 'E',
		templateUrl: 'templates/card.html'
	  };
	});
})();
