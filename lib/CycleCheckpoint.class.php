<?php
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.htm GPLv3
 *
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
 */
namespace Ingress\Cycle;

if (!class_exists("Checkpoint")) {

	/**
	 * The format in which all Checkpoint dates will be shown
	 */
	define ('CHECKPOINT_DATE_FORMAT', "D, M j H:i");
	
	/**
	 * The length of a Checkpoint, in seconds
	 */
	define ('CHECKPOINT_LENGTH_SECONDS', 18000);

	/**
	 * Represents a single Checkpoint marker in a cycle.
	 * 
	 * @author John Luetke <john@johnluetke.net>
	 *
	 * @api
	 * @package Ingress\Checkpoint
	 * @used-by \Ingress\Cycle\Cycle
	 * @version 0.1.0
	 */
	class Checkpoint {

		/**
		 * @ignore
		 */
		private $datetime;
		
		/**
		 * Constructs a new Checkpoint around the given DateTime
		 *
		 * @param \DateTime $datetime the DateTime to emulate as a Checkpoint
		 *
		 * @api
		 * @see http://www.php.net/manual/en/class.datetime.php DateTime
		 * @since 0.1.0
		 */
		public function __construct($datetime) {
			$this->datetime = $datetime;
		}

		/**
		 * Determines if this Checkpoint has already passed.
		 *
		 * @return boolean true if this Checkpoint has passed, false otherwise
		 *
		 * @api
		 * @since 0.1.0
		 */
		public function hasPassed() {
			return time() > $this->datetime->getTimestamp();
		}

		/**
		 * Determines if this Checkpoint is the next one, relative to the current time
		 *
		 * @return boolean true if this Checkpoint is next, false otherwise
		 *
		 * @api
		 * @since 0.1.0
		 */
		public function isNext() {
			$now = time();
			return ($this->datetime->getTimestamp() - CYCLE_LENGTH_SECONDS) < $now && $this->datetime->getTimestamp() >= $now;
		}

		/**
		 * Returns the DateTime object that this Checkpoint emulates
		 *
		 * @return \DateTime the DateTime object
		 *
		 * @api
		 * @see http://www.php.net/manual/en/class.datetime.php DateTime
		 * @since 0.1.0
		 */
		public function getDateTime() {
			return $this->datetime;
		}
	}
}
