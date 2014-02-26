<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname($_SERVER['SCRIPT_FILENAME']) . "/lib");
require("Ingress/Cycle/Cycle.class.php");

if (!isset($_REQUEST['cycle'])) {
	$cycle = \Ingress\Cycle\Cycle::getCurrentCycle();
	$redir =  $_SERVER['PHP_SELF'] . "?cycle=" . $cycle->getName();
	header("Location: " . $redir);
}
else {
	$c = $_REQUEST['cycle'];
	$cycle = \Ingress\Cycle\Cycle::fromIdentifier($c);
	$cycle->getNext();
	$cycle->getPrevious();
	
	echo "<h1>Cycle " . $cycle->getName() . "</h1>";
	echo "current? " . ($cycle->isCurrent() ? "yes" : "no");
	echo "<br/>";
	echo "future? " . ($cycle->isFuture() ? "yes" : "no");
	ECHO "<br/>";
	echo "start: " . $cycle->getStartTime()->format(CYCLE_DATE_FORMAT);
	echo "<br/>";
	echo "end: " . $cycle->getEndTime()->format(CYCLE_DATE_FORMAT);

	echo "<h2>Checkpoints</h2>";
	echo "<ul>";
	foreach ($cycle->getCheckpoints() as $number => $checkpoint) {

		echo "<li class=\"\">" . $checkpoint->getDateTime()->format(CHECKPOINT_DATE_FORMAT)  . ($checkpoint->hasPassed() ? " (Passed)" : "") . "</li>";
	}
	echo "</ul>";
}
?>
