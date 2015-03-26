<!DOCTYPE html>
<html ng-app='xdm'>

<head>
<title>XDM &raquo; Dice Masters Randomiser</title>
<meta charset="UTF-8">

<!-- Google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:700' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed' rel='stylesheet' type='text/css'>

<!-- marked [markdown parser] -->
<script src="js/marked.min.js"></script>

<!-- angular -->
<script type="text/javascript" src="js/angular.min.js"></script>
<script type="text/javascript" src="js/angular-local-storage.min.js"></script>

<!-- bootstrap-->
<link rel="stylesheet" href="css/bootstrap.min.css">

<!-- xeditable -->
<script src="js/xeditable.min.js"></script>
<link rel="stylesheet" href="css/xeditable.css">

<!-- angular md [used marked.js] -->
<script type="text/javascript" src="js/angular-md.min.js"></script>

<!--- XDM -->
<script type="text/javascript" src="js/xdm.js"></script>
<link rel="stylesheet" href="css/xdm.layout.css">
<link rel="stylesheet" href="css/xdm.style.css">

</head>

<body ng-controller='CollectionController as colDB'>
	<section ng-controller='TabController as tab'>
		<div id='banner' ng-class='{min : miniTop==true}'>
		<div class='minify'><a href ng-click='miniTop=false' ng-show='checkMiniTop()'>maximize</a><a href ng-click='miniTop=true' ng-show='checkMiniTop()'>minimize</a></div>
		<div id='bnr'>
			<h1>XDM Randomiser</h1>
			<h4> &lt; <span class='x' title='fully implemented'>AvX</span>, <span class='x' title='fully implemented'>Ultimate X-men</span>, <span class='x'  title='fully implemented'>Yu-Gi-Oh</span>, <span class='x p'  title='partially implemented'>D&amp;D</span>, <span class='x c'  title='not yet implemented'>DC</span> &gt;  Dice Masters</h4>
		</div>
		<!-- main nav -->
		<ul>
			<li ng-class="{ active:tab.isSet(1) }">
				<a href ng-click="tab.setTab(1)">Edit active collection</a>
			</li>
			<li ng-class="{ active:tab.isSet(2) }">
				<a href ng-click="tab.setTab(2)">Draft teams</a>
			</li>
			<li ng-class="{ active:tab.isSet(3) }">
				<a href ng-click="tab.setTab(3)">About XDM</a>
			</li>
		</ul>
	</div>
	
	<!-- Columns -->
		<div ng-controller="ColumnController as columnCtrl">		
		<!-- left col -->
			<section 
			class='column midd left'  
			ng-class="{
				mind : !columnCtrl.isSet(1), 
				midd : columnCtrl.isSet(2) && columnCtrl.isSet(1), 
				maxd : columnCtrl.isSet(1) && !columnCtrl.isSet(2)
			}">
		
			
				<!-- minimize -->
				<div class='ccbtn' id='dcbtn'><a ng-hide='columnCtrl.isSet(1)' ng-click="columnCtrl.reset()">&raquo;</a><a ng-show='columnCtrl.isSet(1)' ng-click="columnCtrl.minColumn(1)">&laquo;</a></div>
						
				<div class='content'>
					
					<!-- the DB -->
					<div ng-show="tab.isSet(1)" ng-controller='DataController as db'>					
						<h1>Set: {{ sets[user.activeSet].name }}</h1>
						<div class='sorts'>
							<span>
								<label>Showing:</label> 								
								<select ng-model="user.activeSet" ng-options="s.value as s.text for s in setsMap"></select> cards.
							</span>
							<br/>
							<span>
								<label>Sorted by:</label>
								<select ng-model="user.sortBy" ng-options="s.value as s.text for s in sorts" ng-change="sortSet()"></select> cards.
							</span>
							
							<small>(<span ng-show='reverse'>&uarr;  <a href  ng-click="sortSet(reverse)">Descending</a></span><span ng-hide='reverse'>&darr; <a href  ng-click="sortSet(reverse)">Ascending</a></span>)</small>.
						</div>
						<div class='loading' ng-show='db.loading'>
							<img src='img/ajax-loader.gif'>
							<p>loading</p>
						</div>
						<ul >
							<card ng-hide="colDB.inCollection(card)" ng-repeat="card in sets[user.activeSet].cards | orderBy:predicate:reverse | filter:isST"></card>
						</ul>							
					</div>
					<!--  DRAFT!  -->
					<div ng-controller="DraftController as drafts" ng-show="tab.isSet(2)">
						<h1>Get draft</h1>
						<p>
							Draft cards for a game. 
							<span class="error" >
								Team size must be an integer and between 1 and 20.
							</span>
							<span class="error" >
								Team count must be an integer and between 1 and 6.
							</span>
						</p>
						<form name='draftRules' novalidate 	>
							<div class='draft'>
							
								Team size: 		<input name='size' type='number' ng-model="rules.teamSize" value="{{rules.teamSize}}" min="1" max="20" required /> 
								
								Team count: 	<input name='count' type='number' ng-model="rules.teamCount" value="{{rules.teamSize}}" min="1" max='6' required /> 
								
								Balance cost: 	<input type='checkbox' ng-model="rules.balanceCost" value="true" >
								
								Balance rarity: <input type='checkbox' ng-model="rules.balanceRarity" value="false">
								
								<button ng-click="drafts.draft(colDB.collection)">draft</button>
								<!--<button ng-click="drafts.sayRules()">rules</button>-->
								
							</div>
						</form>
						<div ng-show='draftedFail'>
							Failed to generate draft.
						</div>
						<div class='loading' ng-show='loading'>
							<img src='img/ajax-loader.gif'>
							<p>loading</p>
						</div>
						<div ng-show="drafted" ng-repeat="team in draft track by $index">
							<h3>Team {{$index}}</h3>
							<ul>
								<li ng-repeat="card in team track by $index">
									<card></card>
								</li>

							</ul>
						</div>
						
					</div>					
				
				<!--  About XDM  -->
					<div ng-show="tab.isSet(3)">
						<about></about>
					</div>
				</div>	
			</section>
			
			
			<!-- Collection display -->
			<section 
				class='right column midd' 
				id='collectionDisplay' 
				ng-class="{
					mind : !columnCtrl.isSet(2), 
					midd : columnCtrl.isSet(1) && columnCtrl.isSet(2), 
					maxd : columnCtrl.isSet(2) && !columnCtrl.isSet(1)}
				">
				<!-- minimize -->
				<div class='ccbtn' id='ccbtn'><a ng-hide='columnCtrl.isSet(2)' ng-click="columnCtrl.reset()">&laquo;</a><a ng-show='columnCtrl.isSet(2)' ng-click="columnCtrl.minColumn(2)">&raquo;</a></div>
				
				<div class='content'>
					<collection><collection>
				</div>
			</section>
		</div>
	</section>
</body>
</html>