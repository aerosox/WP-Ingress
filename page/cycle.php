<div id="cycle-<?php echo str_replace(".", "-", $cycle->getName());?>" class="ingress-cycle <?php echo $cycle->isCurrent() ? "current-cycle" : "";?> <?php echo $cycle->isPast() ? "past-cycle" : "";?> <?php echo $cycle->isFuture() ? "future-cycle" : "";?>"><?php
	$prev = $cycle->getPrevious();
	$next = $cycle->getNext();
	$curr = $cycle->isCurrent() ? $cycle : \Ingress\Cycle\Cycle::getCurrentCycle();
	$date_fmt = get_option("date_format") . " " . get_option("time_format");
?>
	<div class="cycle-data" data-is-current="<?php echo $cycle->isCurrent() ? "true" : "false";?>" data-is-future="<?php echo $cycle->isFuture() ? "true" : "false";?>" data-is-past="<?php echo $cycle->isPast() ? "true" : "false";?>" data-start-time="<?php echo $cycle->getStartTime()->getTimestamp();?>" data-end-time="<?php echo $cycle->getEndTime()->getTimestamp();?>"></div>
	<div class="cycle-meta">
		<div class="is-current-cycle">Current Cycle: <?php echo $cycle->isCurrent() ? "Yes" : "No";?></div>
		<div class="is-current-cycle">Future Cycle: <?php echo $cycle->isFuture() ? "Yes" : "No";?></div>
		<div class="is-current-cycle">Past Cycle: <?php echo $cycle->isPast() ? "Yes" : "No";?></div>
	</div>
	<div class="cycle-time">
		<div class="cycle-start-time">Start Time: <?php echo $cycle->getStartTime()->format($date_fmt);?></div>
		<div class="cycle-end-time">End Time: <?php echo $cycle->getEndTime()->format($date_fmt);?></div>
	</div>
	<div class="cycle-navigation">
		<div class="previous-cycle"><?php if ($prev != null) { ?><a href="/cycle/<?php echo $prev->getName();?>/">&laquo; <?php echo $prev->getName();?></a><?php } ?></div>
		<div class="current-cycle"><?php if ($curr != $cycle) { ?><a href="/cycle/<?php echo $curr->getName();?>/">[goto current]</a><?php } ?></div>
		<div class="next-cycle"><?php if ($next != null) { ?><a href="/cycle/<?php echo $next->getName();?>/"><?php echo $next->getName();?> &raquo;</a><?php } ?></div>
	</div>
	<h3>Checkpoints</h3>
	<ul class="cycle-checkpoints">
<?php foreach($cycle->getCheckpoints() as $checkpoint_number => $checkpoint) { ?>
		<li class="checkpoint checkpoint-<?php echo $checkpoint_number+1;?> <?php echo $checkpoint->isNext() ? "next-checkpoint" : "";?> <?php echo $checkpoint->hasPassed() ? "has-passed" : "";?>" data-has-passed="<?php echo $checkpoint->hasPassed() ? "true" : "false";?>" data-is-next="<?php echo $checkpoint->isNext() ? "true" : "false";?>" data-time="<?php echo $checkpoint->getDateTime()->getTimestamp();?>"><?php echo $checkpoint->getDateTime()->format($date_fmt);?></li>
<?php } ?>
	</ul>
</div>


