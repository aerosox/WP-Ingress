<?php
/**
 * Copyright 2014 John Luetke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */
namespace Ingress\Cycle;

if (!class_exists("Cycle")) {
	
	require("Ingress/Cycle/Checkpoint.class.php");

	/**
	 * The format in which all dates will be shown
	 */
	define ('CYCLE_DATE_FORMAT', "Y-m-d H:i:s (l)");

	/**
	 * The number of Checkpoints per cycle
	 */
	define ('CHECKPOINTS_PER_CYCLE', 35);

	/**
	 * The length of a Cycle, in seconds
	 */
	define ('CYCLE_LENGTH_SECONDS', 630000);
	
	/**
	 * The number of cycles per calendar year. (365 * 24) / 175
	 */
	define ('CYCLES_PER_YEAR', 50);

	/**
	 * Timezone for all DateTime instances.
	 */
	define ('TIMEZONE', "US/Pacific");

	$start = new \DateTime("2014-01-07 19:00:00", new \DateTimeZone(TIMEZONE));

	/**
	 * Class representing a single "cycle" for regional MU scoring
	 *
	 * @author John Luetke <john@johnluetke.net>
	 *
	 * @package Ingress\Checkpoint
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

			$now = new \DateTime("now", new \DateTimeZone(TIMEZONE));
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
		 * Determines if the given cycle identifier is valid
		 *
		 * An example of an invalid cycle would be anything prior to 2014.2, since that was the "first" one.
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

		/**
		 * @ignore
		 */
		private $name;

		/**
		 * @ignore
		 */
		private $year;

		/**
		 * @ignore
		 */
		private $number;

		/**
		 * @ignore
		 */
		private $startTime;

		/**
		 * @ignore
		 */
		private $endTime;

		/**
		 * @ignore
		 */
		private $next;

		/**
		 * @ignore
		 */
		private $previous;

		/**
		 * @ignore
		 */
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
			$this->endTime->add(new \DateInterval("PT" . CYCLE_LENGTH_SECONDS . "S"));

			$this->checkpoints = array();
			$checkpoint = clone $this->startTime;

			do {
				$checkpoint->add(new \DateInterval("PT5H")); // 5 hours
				$this->checkpoint[] = new Checkpoint(clone $checkpoint);
			}
			while ($checkpoint < $this->endTime);
		}

		/**
		 * Gets the Cycle that sequentially follows this Cycle
		 *
		 * @return \Ingress\Cycle\Cycle|null The following Cycle, or null if there is not one
		 *
		 * @api
		 * @since 0.1.0
		 */
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

		/**
		 * Gets the Cycle that sequentially preceeds this Cycle
		 *
		 * @return \Ingress\Cycle\Cycle|null The following Cycle, or null if there is not one
		 *
		 * @api
		 * @since 0.1.0
		 */
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
			
			return $this->previous;
		}

		/**
		 * Determines if this is a valid cycle.
		 *
		 * @return boolean true if valid, false otherwise
		 *
		 * @see Cycle::isValidIdentifier() Cycle::isValidIdentifier()
		 */
		public function isValid() {
			return Cycle::isValidIdentifier($this->name);
		}

		/**
		 * Determines if this cycle is the present one, relative to the current time.
		 *
		 * @return boolean true if this is the present cycle, false otherwise
		 */
		public function isCurrent() {
			$now = time();
			return $this->startTime->getTimestamp() <= $now && $this->endTime->getTimestamp() >= $now;
		}

		/**
		 * Determines if this cycle is a past one, relative to the current time.
		 *
		 * @return boolean true if a past cycle, false otherwise
		 */
		public function isPast() {
			return $this->endTime->getTimestamp() < time();
		}


		/**
		 * Determines if this cycle is a future one, relative to the current time.
		 *
		 * @return boolean true if a future cycle, false otherwise
		 */
		public function isFuture() {
			return $this->startTime->getTimestamp() > time();
		}

		/**
		 * Gets the name of this Cycle. For example, 2014.2
		 *
		 * @return string the name of this Cycle
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * Gets the start DateTime for this Cycle
		 *
		 * @return \DateTime the DateTime representing the start of this Cycle
		 *
		 * @see http://www.php.net/manual/en/class.datetime.php DateTime
		 */
		public function getStartTime() {
			return $this->startTime;
		}

		/**
		 * Gets the end DateTime for this Cycle
		 *
		 * @return \DateTime the DateTime representing the end of this Cycle
		 *
		 * @see http://www.php.net/manual/en/class.datetime.php DateTime 
		 */
		public function getEndTime() {
			return $this->endTime;
		}

		/**
		 * Gets all Checkpoints in this Cycle
		 *
		 * @return array Array of Checkpoints for this Cycle
		 *
		 * @see \Ingress\Cycle\Checkpoint Checkpoint
		 * @uses \Ingress\Cycle\Checkpoint to emulate DateTimes
		 */
		public function getCheckpoints() {
			return $this->checkpoint;
		}
	
	}
}
?>
