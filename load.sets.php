<?php
require_once('classes.lib.php');

$sets = SetFactory::createAll();
?>
<pre>
<?php
foreach($sets as $set){
	print "loaded ".$set->name().PHP_EOL;
}
?>
</pre>