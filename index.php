<!DOCTYPE html>
<html ng-app='xdm'>

<head>

<link href='http://fonts.googleapis.com/css?family=Oswald:700' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed' rel='stylesheet' type='text/css'>

<!-- marked -->
<script src="js/marked.min.js"></script>

<!-- angular -->
<script type="text/javascript" src="js/angular.min.js"></script>
<script type="text/javascript" src="js/angular-cookie.min.js"></script>
<script src="js/angular-sanitize.min.js"></script>
<script src="js/angular-translate.min.js"></script>

<!--bootstrap-->
<script src="js/ui-bootstrap-tpls-0.11.2.min.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">

<!-- xeditable -->
<script src="js/xeditable.min.js"></script>
<link rel="stylesheet" href="css/xeditable.css">

<!-- dialogs -->
<script src="js/dialogs-default-translations.min.js"></script>
<script src="js/dialogs.min.js"></script>
<script src="js/dialogTest.js"></script>
<link rel="stylesheet" href="css/dialogs.min.css">

<!-- angular md -->
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
		<!-- display active collection -->
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
		<!-- main display-->
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
								<label>Showing:</label> <a href editable-select="user.activeSet" e-ng-options="s.value as s.text for s in setsMap" > {{ showSets() }} </a> cards.
							</span>
							
							<span>
								<label>Sorted by:</label> <a href editable-select="user.sortBy" e-ng-options="s.value as s.text for s in sorts" onaftersave="sortSet()">{{ showSorts() }}</a> 
							</span>
							
							<small>(<span ng-show='reverse'>&uarr;  <a href  ng-click="sortSet(reverse)">Descending</a></span><span ng-hide='reverse'>&darr; <a href  ng-click="sortSet(reverse)">Ascending</a></span>)</small>.
						</div>
						<ul ng-repeat="card in sets[user.activeSet].cards | orderBy:predicate:reverse | filter:card.in===true | filter:isST">
							<card></card>
						</ul>							
					</div>
					<!--  DRAFT!  -->
					<div ng-controller="DraftController as drafts" ng-show="tab.isSet(2)">
						<h1>Get draft</h1>
						<p>Draft cards for a game.</p>
						<p>
							Team size: 		<a href editable-text="rules.teamSize" >{{rules.teamSize}}</a>. 
							Team count: 	<a href editable-text="rules.teamCount">{{rules.teamCount}}</a>. 
							Balance cost: 	<input type='checkbox' ng-model="rules.balanceCost" value="true">. 
							Balance rarity: <input type='checkbox' ng-model="rules.balanceRarity" value="false">.
						</p>
						<div ng-show='draftedFail'>
							Failed to generate draft.
						</div>
						<div ng-show="drafted" ng-repeat="team in draft">
							<h3>Team {{$index}}</h3>
							<ul>
								<li ng-repeat="card in team">
									<card></card>
								</li>

							</ul>
						</div>
						<button ng-click="drafts.sayRules()">test rules</button>
						<button ng-click="drafts.draft()">draft</button>
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
					<h2><span editable-text="colDB.collection.name" e-form="colName" onaftersave="colDB.setColName()">{{ colDB.collection.name || 'empty' }}</span> ({{CollectionDataService.name.cards.length}} cards)</h2>
					<p>debug <br> {{colDB.collection.name}}</p>
					<div ng-controller='IOController as io'>
						<div class='load' ng-show='loading'  >
							<div class='controls'>
								<h1>Collections</h1>
								<div ng-hide="io.collectionList.length<1">
									<p>You have {{ io.collectionCount }} stored collections. Load or delete a collection?</p>
									<ul >
										<li class='card animate-repeat' ng-repeat="cookie in io.collectionList"> 
											<div class='title animate-repeat'> {{cookie.name}}</div>
											<div>{{cookie.cards.length}} cards </div>
											<div>
												[<a href ng-click="io.loadFromCookie(cookie)">load {{cookie.name}} </a>] 
												[<a href ng-click="io.killCookie(cookie)">delete {{cookie.name}} </a>] 
											</div>
										</li>
									</ul>
								</div>
								<p>Load collection from paste code backup.</p>
								<p>{{LoadResponse}}</p>
								<input type='text' ng-model='loadCode' />
								<button class="btn btn-load" type='submit' ng-click='io.loadFromCode(loadCode)' >load code</button>
								<div class='dialogue-btn'><button class="btn btn-clear" type='submit' ng-click='loading=false' >cancel</button></div>
							</div>
						</div>
					
						
						<div class='controls' ng-hide='loading'>
							<button  title='Rename your collection.' class="btn btn-default" ng-click="colName.$show()" ng-hide="colName.$visible">
								rename
							</button>
							<button title='Save this collection.' class="btn btn-default btn-save" ng-click='io.saveCollection(colDB.collection)' ng-hide="colName.$visible">save</button>
							<button title='Load or delete a saved collection.' class="btn btn-default btn-load" ng-click='loading=true' >load</button>
							<button title='Empty this collection.' class="btn btn-default btn-clear" ng-click='clearCol()' ng-hide="colName.$visible">clear</button>
							<div ng-show='io.saved()' class='saveString'><label>Paste Code: (<a href ng-click="pastCodeTip=true" >what's this?</a>)</label> <small>{{pasteCode}}</small></div>
							<div class='toolTip' ng-show='pastCodeTip'>
								<div class='toolTipText'>
									<h1>What is a paste code?</h1>
									The paste code is provided as a way to back-up your collection(s). By default your XDM data is stored in cookies, which is somewhat unreliable. If you keep your most up-to-date past code, you can recover your collection should you ever clear your cookies.
									<div class='dialogue-btn'><button class="btn btn-clear" type='submit' ng-click='pastCodeTip=false' >close</button></div>
								</div>
							</div>
							
						</div>
						<div ng-show="colDB.collection.cards.length<1" ng-hide="loading">No cards.</div>
					</div>
					
					<div>
						<ul ng-repeat='card in colDB.collection.cards' >									
							<card ></card>
						</ul>
					</div>
					
					
					
				</div>
			</section>
		</div>
	</section>
</body>
</html>