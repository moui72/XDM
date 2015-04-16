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
<script type="text/javascript" src="js/angular-route.min.js"></script>

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

	<div id='banner' ng-class='{min : miniTop==true}'>
	<div class='minify'><a href ng-click='miniTop=false' ng-show='checkMiniTop()'>maximize</a><a href ng-click='miniTop=true' ng-show='checkMiniTop()'>minimize</a></div>
	<div id='bnr'>
		<h1>XDM Randomiser</h1>
		<h4> &lt; <span class='x' title='fully implemented'>AvX</span>, <span class='x' title='fully implemented'>Ultimate X-men</span>, <span class='x'  title='fully implemented'>Yu-Gi-Oh</span>, <span class='x p'  title='partially implemented'>D&amp;D</span>, <span class='x c'  title='not yet implemented'>DC</span> &gt;  Dice Masters</h4>
	</div>
	<!-- main nav -->
	<ul ng-controller='TabController as tab'>
		<li ng-class="{ active:colDB.onTab('/') }">
			<a href="#manage" ng-click="colDB.onTab">Edit active collection</a>
		</li>
		<li ng-class="{ active:colDB.onTab('/draft') }">
			<a href="#draft" >Draft teams</a>
		</li>
		<li ng-class="{ active:colDB.onTab('/about') }">
			<a href="#about" >About XDM</a>
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
					
			<div class='content' ng-view></div>	
			
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

</body>
</html>