<?php
/**
 * Wrapper for DateTie to keep time consistent.
 * User: feryaz
 * Date: 02.09.2018
 * Time: 14:49
 */

//Prevent direct access to file
if(!defined('ABSPATH'))
	exit;

class ER_DateTime extends DateTime {
	public static function createFromFormat($format, $time, $add = false){
        $date = false;
		if(version_compare(PHP_VERSION, '5.3.0') >= 0){
			$date = parent::createFromFormat($format, $time);
			$date->setTime(0,0,0);
		}
		if(!$date){
            $format = str_replace(array('d', 'm', 'Y', 'H', 'i', 's'), array('%d', '%m', '%Y', '%H', '%M', '%S'), $format);
            $ugly = strptime($time, $format);
            $ymd = sprintf(
                '%04d-%02d-%02d %02d:%02d:%02d',
                $ugly['tm_year'] + 1900,  // This will be "111", so we need to add 1900.
                $ugly['tm_mon'] + 1,      // This will be the month minus one, so we add one.
                $ugly['tm_mday'],
                $ugly['tm_hour'],
                $ugly['tm_min'],
                $ugly['tm_sec']
            );

            $date = new DateTime($ymd);
            $date->setTime(0,0,0);
        }
        if(is_numeric($add)){
            return ER_DateTime::addSeconds($date, $add);
        }
		return $date;
	}

	public function getTimestamp(){
		if(version_compare(PHP_VERSION, '5.3.0') >= 0){
			return parent::getTimestamp();
		}
		return $this->format('U');
	}

	public static function addSeconds(DateTime $date, $seconds){
        $new_date = clone $date;
        $new_date->add(new DateInterval('PT'.$seconds.'S'));
        $offset = $date->getOffset() - $new_date->getOffset();

        if($offset > 0){
            return $new_date->getTimestamp() + $offset;
        }
        return $new_date->getTimestamp();
    }

}
