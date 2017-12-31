<?php

class Clock {
	private $hours, $minutes;

	/**
	 * Clock constructor.
	 *
	 * @param int $hours
	 * @param int $minutes
	 */
	public function __construct( $hours, $minutes = 0 ) {
		$this->hours   = $hours;
		$this->minutes = $minutes;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->format( $this->hours ) . ":" . $this->format( $this->minutes );
	}

	/**
	 * @param $time
	 *
	 * @return string
	 */
	public function format( $time ): string {
		return $time < 10 ? '0' . $time : $time;
	}

	public function sub( $int ) {
		return $this->add( - $int );
	}

	public function add( $minutes ) {
		$m_sum   = $this->minutes + $minutes;
		$minutes = ( $m_sum % 60 + 60 ) % 60;
		$h_sum   = $this->hours + ( $m_sum / 60 );
		$hours   = ( $h_sum % 24 + 24 ) % 24;

		return new Clock( $hours, $minutes );
	}

}