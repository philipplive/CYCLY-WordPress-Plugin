<?php
namespace Cycly;

class OpeningHoursHour extends Item {
	public $weekdays = 0;
	public $weekdaysFormated;
	public $timeOpening = '';
	public $timeClosing = '';

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->weekdays = $data->weekdays;
		$this->weekdaysFormated = $data->weekdaysFormated;
		$this->timeOpening = $data->timeOpening;
		$this->timeClosing = $data->timeClosing;
		return $this;
	}

	public function getWeekdays(): array {
		$list = [];
		foreach ([1, 2, 4, 8, 16, 32, 64] as $i => $value)
			if ($this->weekdays & $value)
				$list[] = $i + 1;

		return $list;
	}
}