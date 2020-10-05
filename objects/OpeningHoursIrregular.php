<?php
namespace Cycly;

class OpeningHoursIrregular extends Item {
	/**
	 * @var string
	 */
	public $title = '';
	/**
	 * @var string
	 */
	public $description = '';
	/**
	 * @var \DateTime
	 */
	public $dateStart;
	/**
	 * @var \DateTime
	 */
	public $dateEnd;
	/**
	 * @var OpeningHoursHour[]
	 */
	public $hours = [];

	/**
	 * @return string
	 */
	public function getDateFormated(): string {
		if ($this->dateStart == $this->dateEnd)
			return $this->dateStart->format($this->lang->format['date']);

		return sprintf('%s - %s', $this->dateStart->format($this->lang->format['date']), $this->dateEnd->format($this->lang->format['date']));
	}

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->title = $data->title;
		$this->description = $data->description;
		$this->dateStart = new \DateTime($data->dateStart);
		$this->dateEnd = new \DateTime($data->dateEnd);
		$this->hours = [];

		foreach ($data->hours as $hdata) {
			$item = new OpeningHoursHour();
			$item->fromData($hdata);
			$this->hours[] = $item;
		}

		return $this;
	}
}