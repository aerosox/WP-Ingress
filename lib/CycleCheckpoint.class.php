<?php
/*
 *
 */
if (!class_exists("CycleCheckpoint")) {

	class CycleCheckpoint {

		private $datetime;
		
		public function __construct($datetime) {
			$this->datetime = $datetime;
		}

		public function hasPassed() {
			return time() > $this->datetime->getTimestamp();
		}

		public function isNext() {
			$now = time();
			return ($this->datetime->getTimestamp() - CYCLE_LENGTH_SECONDS) < $now && $this->datetime->getTimestamp() >= $now;
		}

		public function getDateTime() {
			return $this->datetime;
		}

	}
}
