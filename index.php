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
	<section ng-controller='IOController as io'>
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
					<a href ng-click="tab.setTab(2)">Manage collections</a>
				</li>
				<li ng-class="{ active:tab.isSet(3) }">
					<a href ng-click="tab.setTab(3)">Draft teams</a>
				</li>
				<li ng-class="{ active:tab.isSet(4) }">
					<a href ng-click="tab.setTab(4)">About XDM</a>
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
								
								<small>(<span ng-show='reverse'>&uarr;  <a href  ng-click="sortSet(reverse)">Ascending</a></span><span ng-hide='reverse'>&darr; <a href  ng-click="sortSet(reverse)">Descending</a></span>)</small>.
							</div>
							<ul ng-repeat="card in sets[user.activeSet].cards | orderBy:predicate:reverse | filter:card.in===true | filter:card.Cost > 0">
								<card></card>
							</ul>							
						</div>
						
						<!--  Load/Delete collection  -->
						<div ng-show="tab.isSet(2)">
							<h1>Load collection</h1>
							<div ng-hide="io.collectionList.length<1">
								<p>You have {{ io.collectionCount }} stored collections. Load a collection?</p>
								<ul >
									<li class='card animate-repeat' ng-repeat="cookie in io.collectionList"> 
										<div class='title'> {{cookie.name}}</div>
										<div>{{cookie.cards.length}} cards </div>
										<div>
											[<a href ng-click="io.loadFromCookie(cookie)">load {{cookie.name}} </a>] 
											[<a href ng-click="io.killCookie(cookie)">delete {{cookie.name}} </a>] 
										</div>
									</li>
								</ul>
							</div>
							<p><a href ng-click='saveCollection()'>save</a>.</p>
							<p>Load collection from paste code backup.</p>
							<p>{{LoadResponse}}</p>
							<input type='text' ng-model='loadCode' />
							<button type='submit' ng-click='io.loadFromCode(loadCode)' >load</button>
						</div>

						<!--  DRAFT!  -->
						<div ng-controller="DraftController as drafts" ng-show="tab.isSet(3)">
							<h1>Get draft</h1>
							<p>Draft cards for a game.</p>
							<p>
								Team size: 		<a href editable-text="rules.teamSize" >{{rules.teamSize}}</a>. 
								Team count: 	<a href editable-text="rules.teamCount">{{rules.teamCount}}</a>. 
								Balance cost: 	<a href editable-text="rules.balanceCost"> {{rules.balanceCost}}</a>. 
								Balance rarity: <a href editable-text="rules.balanceRarity" > {{rules.balanceRarity}}</a>.
							</p>
							<div ng-show="drafted" ng-repeat="team in draft">
								<ul ng-show="drafted" ng-repeat="card in team">
									<li>
										<div>{{card.Title}}, {{card.SubTitle}}</div>
										<div>Rarity: {{card.Rarity}}, Cost: {{card.Cost}}</div>
									</li>

								</ul>
							</div>
							<button ng-click="drafts.sayRules()">test rules</button>
							<a href ng-click="draft()">test draft</a>
						</div>					
					
					<!--  About XDM  -->
						<div ng-show="tab.isSet(4)">
							<h1>About</h1>
	<md>
	XDM is a tool for selecting random cards to play with in Dice Masters, a dice and card game by Eric Lang and Mike Elliot. This site is no no way affiliated with Wizkids, Eric Lang, Mike Elliot, or any of the licenses (Marvel, Yu-Gi-Oh, Dungeons and Dragons, DC Comics, etc.) associated with this game. This app is simply a fan-made tool intended for fans of the game.

	## Changelog

	  - XDM Beta 1 released. 
	  - XDM is essentially [mdmR](http://opinionator.net/mdmR) v2 and represents:
		- Major UI overhaul 
		- Addition of Marvel: Uncanny X-men set cards
		- Addition of Yu-Gi-Oh cards set cards
		- Partial implementaion of Dungeons and Dragons: Battle for Faerun cards.

	## Planned improvements
		  
	  - Add costs of D&amp;D set cards.
	  - Improve drafting algorithm.
	  - Add UI documentation and feedback
	  - Add DC set cards.

	## Credits and contributors
	  - Developed by Tyler Peckenpaugh &#91; [moui](http://boardgamegeek.com/user/moui) &#93; on BGG
	  - Avengers vs. X-men data file provided by MajorOracle via [boardgamegeek.com (BGG)](http://boardgamegeek.com)
	  - Ultimate X-men data file provided by pishposh via [BGG](http://boardgamegeek.com)
	  - Yu-Gi-Oh data file provided by pishposh via [BGG](http://boardgamegeek.com)
	  - D&amp;D partial data provided by oneone78 via [BGG](http://boardgamegeek.com)
	</md>
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
					
					<div class='content' ng-hide='cc'>
						<h2><span editable-text="collection.name" e-form="colName">{{ collection.name || 'empty' }}</span></h2>
						
						<div class='controls'>
							<button class="btn btn-default" ng-click="colName.$show()" ng-hide="colName.$visible">
								rename
							</button>
							<div class='ioc	'>							
								<button class="btn btn-default btn-save" ng-click='io.saveCollection()' ng-hide="colName.$visible">save</button>
								<button class="btn btn-default btn-clear" ng-click='clearCol()' ng-hide="colName.$visible">clear</button>
								<div ng-show='saved' class='saveString'><label>String:</label> <br/> {{saveString}}</div>
							</div>
						</div>
						
						
						<p ng-hide='collection.cards.length>0'>No cards.</p>
						<ul >

							<card ng-repeat='card in collection.cards'></card>

						</ul>
						
					</div>
				</section>
			</div>
		</section>
	</section>

</body>

</html>