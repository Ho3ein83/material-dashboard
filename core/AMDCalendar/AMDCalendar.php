<?php

/** @var AMDCalendar $amdCal */
$amdCal = null;

class AMDCalendar{

	/**
	 * Date mode. e.g: "g" for Gregorian, "j" for Jalali (solar date)
	 * @var string
	 * @since 1.0.0
	 */
	protected $dateMode;

	/**
	 * Calendar core
	 */
	public function __construct(){

		$this->dateMode = "j"; # Jalali
		// $this->dateMode = "g"; # Gregorian

	}

	/**
	 * Change date mode (e.g: Jalali, gregorian)
	 *
	 * @param string $mode
	 * <ul>
	 * <li><b>j</b> for jalali</li>
	 * <li><b>g</b> for gregorian</li>
	 * </ul>
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function setDateMode( $mode ){

		$this->dateMode = $mode;

	}

	/**
	 * Get date (server date)
	 *
	 * @param string $format
	 * Date format
	 * @param int $time
	 * Unix timestamp
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function date( $format, $time = null ){

		return apply_filters( "amd_core_calendar_date", date( $format, $time ) );

	}

	/**
	 * Get date based on date-mode (e.g: Jalali, Gregorian)
	 *
	 * @param string $format
	 * Date format
	 * @param int $time
	 * Unix timestamp
	 *
	 * @return array|false|string|string[]
	 * @since 1.0.0
	 */
	public function realDate( $format, $time = null ){

		if( empty( $time ) )
			$time = time();

		# $locale = get_locale();
		# $mode = $locale == "fa_IR" ? "j" : "g";
		$mode = $this->dateMode;

		if( $mode == "g" )
			return $this->date( $format, $time );

		$yy = self::date( "Y", $time );
		$mm = self::date( "m", $time );
		$dd = self::date( "d", $time );

		require_once( "calendar.php" );

		return amd_jdate( $format, $time );

	}

	/**
	 * Get real date from date
	 *
	 * @param string $format
	 * Date format
	 * @param string $date
	 * Base date
	 *
	 * @return array|false|string|string[]
	 * @since 1.0.0
	 */
	public function realDateFromDate( $format, $date ){

		$time = strtotime( $date );

		return $this->realDate( $format, $time );

	}

}