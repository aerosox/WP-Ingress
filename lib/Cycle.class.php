<?php
/*
 *
 */
if (!class_exists("Cycle")) {
	
	require("./CycleCheckpoint.class.php");

	$start = new DateTime("2014-01-07 19:00:00", new DateTimeZone("US/Pacific"));

	define ('DATE_FORMAT', "Y-m-d H:i:s (l)");
	define ('CHECKPOINT_DATE_FORMAT', "D, M j H:i");
	define ('CHECKPOINT_LENGTH_SECONDS', 18000);
	define ('CHECKPOINTS_PER_CYCLE', 35);
	define ('CYCLE_LENGTH_SECONDS', 630000);
	define ('CYCLES_PER_YEAR', 50);
	define ('TIMEZONE', "US/Pacific");

	/**
	 * Class representing a single "cycle" for regional MU scoring
	 *
	 * @author John Luetke <john@johnluetke.net>
	 */
	class Cycle {
	
		/**
		 * Gets the current cycle
		 *
		 * Calculates current cycle by examing the number of 175 hour periods that have elapsed since Jan 7, 2014, which was cycle 2014.1
		 *
		 * @return Cycle 
		 */
		public static function getCurrentCycle() {
			global $start;

			$now = new DateTime("now", new DateTimeZone(TIMEZONE));
			$cycle_number = (int)(($now->getTimestamp() - $start->getTimestamp()) / CYCLE_LENGTH_SECONDS);
			$temp = $cycle_number + 1; // +1 because the "first" cycle was 2014.2
			$year = 2014 + ($temp > 12 ? (int)($temp / 12) : 0);
			$cycle = $temp > 12 ? $temp % 12 : $temp;
			$cycle = $year . "." . $cycle;
			return Cycle::fromIdentifier($cycle);
			
		}
		
		/**
		 * Construct a new instance of Cycle based on a cycle identifier, such as 2014.7
		 *
		 * @param string $cycle the cycle identifier
		 *
		 * @return Cycle
		 */
		public static function fromIdentifier($cycle) {
			$year = substr($cycle, 0, strpos($cycle, "."));
			$cycle = substr($cycle, strpos($cycle, ".") + 1, strlen($cycle));

			$c = new Cycle($year, $cycle);
			if (!$c->isValid()) {
				throw new Exception("$cycle is an invalid cycle");
			}

			return $c;
		}

		/**
		 * Determins if the given cycle identifier is valid
		 *
		 * @param string $cycle the cycle identifier
		 *
		 * @return boolean true if valid, false otherwise
		 */
		public static function isValidIdentifier($cycle) {
			$year = substr($cycle, 0, strpos($cycle, "."));
			$cycle = substr($cycle, strpos($cycle, ".") + 1, strlen($cycle));

			return !($cycle < 1) && ($year >= 2014) && !($year == 2014 && $cycle == 1) ;
		}

		private $name;
		private $year;
		private $number;
		private $startTime;
		private $endTime;

		private $next;
		private $previous;

		private function __construct($year, $cycle) {
			global $start;
	
			$this->year = $year;
			$this->cycle = $cycle;

			$this->name = $year . "." . $cycle;
			// TODO: this may not be 100% accurate..
			$this->number = (abs(date("Y") - $this->year) * CYCLES_PER_YEAR) + $this->cycle - 1;

			$this->startTime = clone $start;
			$this->startTime->setTimestamp($start->getTimestamp() + ($this->number * CYCLE_LENGTH_SECONDS));

			$this->endTime = clone $this->startTime;
			$this->endTime->add(new DateInterval("PT" . CYCLE_LENGTH_SECONDS . "S"));

			$this->checkpoints = array();
			$checkpoint = clone $this->startTime;

			do {
				$checkpoint->add(new DateInterval("PT5H")); // 5 hours
				$this->checkpoint[] = new CycleCheckpoint(clone $checkpoint);
			}
			while ($checkpoint < $this->endTime);
		}

		public function getNext() {
			if ($this->next == null) {
				$nextCycle = $this->cycle + 1;
				$year = $this->year;
				if ($nextCycle > CYCLES_PER_YEAR) {
					$nextCycle = 1;
					$year++;
				}

				$this->next = Cycle::fromIdentifier($year . "." . $nextCycle);
			}
			
			return $this->next;
		}

		public function getPrevious() {
			if ($this->previous == null) {
				$previousCycle = $this->cycle - 1;
				$year = $this->year;
				if ($previousCycle < 1) {
					$previousCycle = CYCLES_PER_YEAR;
					$year--;
				}
				
				try {
					$this->previous = Cycle::fromIdentifier($year . "." . $previousCycle);
				}
				catch (Exception $e) {
					$this->previous = null;
				}
			}
			
			return $this->next;
		}

		/**
		 * Determines if this is a valid cycle.
		 *
		 * An example of an invalid cycle would be anything prior to 2014.2, since that was the "first" one.
		 *
		 * @return boolean true if valid, false otherwise
		 */
		public function isValid() {
			return Cycle::isValidIdentifier($this->name);
		}

		/**
		 * Determins if this cycle is the present one
		 *
		 * @return boolean true if this is the present cycle, false otherwise
		 */
		public function isCurrent() {
			$now = time();
			return $this->startTime->getTimestamp() <= $now && $this->endTime->getTimestamp() >= $now;
		}

		/**
		 * Determines if this cycle is a future one
		 *
		 * @return boolean true if a future cycle, false otherwise
		 */
		public function isFuture() {
			return $this->startTime->getTimestamp() > time();
		}

		public function getName() {
			return $this->name;
		}

		public function getStartTime() {
			return $this->startTime;
		}

		public function getEndTime() {
			return $this->endTime;
		}

		public function getCheckpoints() {
			return $this->checkpoint;
		}
	
	}
}
?>
