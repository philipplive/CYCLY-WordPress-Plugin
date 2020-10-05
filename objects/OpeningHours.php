<?php
namespace Cycly;

class OpeningHours extends Item {
	/**
	 * @var OpeningHoursHour[]
	 */
	public $regular = [];

	/**
	 * @var OpeningHoursIrregular[]
	 */
	public $irregulars = [];

	/**
	 * @param string $hourFormat
	 * @return array string[]
	 */
	public function getRegularFormated($hourFormat = '%s - %s'): array {
		$weekdays = [];
		foreach ($this->regular as $hour) {
			if (!isset($weekdays[$hour->weekdays]))
				$weekdays[$hour->weekdays] = [];

			$weekdays[$hour->weekdays][] = $hour;
		}

		$list = [];
		foreach ($weekdays as $hours) {
			$weekdaysFormated = null;
			$hoursFormated = [];
			foreach ($hours as $hour) {
				$weekdaysFormated = $hour->weekdaysFormated;
				$hoursFormated[] = sprintf($hourFormat, $hour->timeOpening, $hour->timeClosing);
			}
			$list[$weekdaysFormated] = $hoursFormated;
		}

		return $list;
	}

	/**
	 * Aktuelle Ausnahmen
	 * @return OpeningHoursIrregular[]
	 */
	public function getIrregularsCurrent(): array {
		$today = \HfCore\Time::today();

		$list = [];
		foreach ($this->irregulars as $irregular) {
			if ($irregular->dateEnd >= $today && $irregular->dateStart <= $today)
				$list[] = $irregular;
		}
		return $list;
	}

	/**
	 * Ausnahmen demnächst
	 * @return OpeningHoursIrregular[]
	 */
	public function getIrregularsPending(): array {
		$today = \HfCore\Time::today();
		$limit = \HfCore\Time::goForward('P3W');

		$list = [];
		foreach ($this->irregulars as $irregular) {
			if ($irregular->dateStart > $today && $irregular->dateStart < $limit)
				$list[] = $irregular;
		}
		return $list;
	}

	/**
	 * @return string
	 */
	public function getCurrentHours(): string {
		foreach ($this->getIrregularsCurrent() as $irregular) {
			if ($irregular->dateStart == $irregular->dateEnd)
				return 'Heute: '.$irregular->title;

			return 'bis '.$irregular->dateEnd->format($this->lang->format['date']).': '.$irregular->title;
		}

		$today = [];
		foreach ($this->regular as $hour) {
			if (in_array(date('N'), $hour->getWeekdays())) {
				$hour->opening = strtotime($hour->timeOpening);
				$hour->closing = strtotime($hour->timeClosing);
				$today[] = $hour;
			}
		}

		usort($today, function (OpeningHoursHour $a, OpeningHoursHour $b) {
			return $a->opening > $b->opening;
		});

		$now = time();
		foreach ($today as $hour) {
			if ($hour->opening < $now && $hour->closing > $now)
				return 'geöffnet bis '.$hour->timeClosing;
		}

		foreach ($today as $hour) {
			if ($hour->opening > $now)
				return 'öffnet um '.$hour->timeOpening;
		}

		foreach (array_reverse($today) as $hour) {
			if ($hour->closing < $now)
				return 'geschlossen seit '.$hour->timeClosing;
		}

		return 'heute geschlossen';
	}

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->regular = [];

		foreach ($data->regular as $hdata) {
			$item = new OpeningHoursHour();
			$item->fromData($hdata);
			$this->regular[] = $item;
		}

		$this->irregulars = [];

		foreach ($data->irregulars as $idata) {
			$item = new OpeningHoursIrregular();
			$item->fromData($idata);
			$this->irregulars[] = $item;
		}

		return $this;
	}
}