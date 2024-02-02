<?php
use \HfCore\HtmlNode;

/**
 * Öffnunfszeiten Widget
 * Class CyclyWidget
 */
class CyclyWidget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'cylcywidget',
			'Öffnungszeiten',
			array('description' => __('Cycly Widget', 'cycly_widget'),)
		);
	}

	public function widget($args, $instance) {
		extract($args);
		$title = $instance['title'];

		echo $before_widget;

		if (!empty($title)) {
			echo $before_title.$title.$after_title;
		}

		try {
			echo $this->drawOpeningHours($instance['branch']);
		}catch (\Exception $ex){
			echo "Keine Öffnungezeiten hinterlegt";
		}

		echo $after_widget;
	}

	public function form($instance) {
		echo HtmlNode::p()
			->append(HtmlNode::label('Titel')->attr('for', $this->get_field_name('title')))
			->append(HtmlNode::input()->id($this->get_field_id('title'))->attr('name', $this->get_field_name('title'))->value(esc_attr($instance['title']))->addClass('widefat')->attr('type', 'text'));

		$select = HtmlNode::select()->id($this->get_field_id('branch'))->attr('name', $this->get_field_name('branch'))->addClass('widefat')->attr('type', 'text');

		foreach (\HfCore\System::getInstance()->getBranches() as $branch) {
			$option = HtmlNode::option($branch->name)->value($branch->id);

			if (esc_attr($instance['branch']) == $branch->id)
				$option->attr('selected','');

			$select->append($option);
		}

		echo HtmlNode::p()
			->append(HtmlNode::label('Geschäftsstelle')->attr('for', $this->get_field_name('branch')))
			->append($select);
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['branch'] = (!empty($new_instance['branch'])) ? strip_tags($new_instance['branch']) : '';

		return $instance;
	}

	public function getOpeningHours(int $branch): \Cycly\OpeningHours {
		$this->openinghours = new \Cycly\OpeningHours();
		return $this->openinghours->fromData(\Cycly\CyclyApi::cacheRequest(['extension', 'openinghours', 'branch', $branch]));
	}

	protected function drawOpeningHours(int $branch): HtmlNode {
		$openinghours = $this->getOpeningHours($branch);
		$element = HtmlNode::div();

		// Ausnahmen aktuell
		$current = $openinghours->getIrregularsCurrent();
		if (count($current)) {
			HtmlNode::div()->addClass('irregulars irregulars-current')
				->append(self::drawOpeningHoursIrregular($current))
				->appendTo($element);
		}

		foreach ($openinghours->getRegularFormated() as $days => $times) {
			$div = HtmlNode::div()
				->appendTo($element);

			$div->append(HtmlNode::strong()->setText($days.':'))
				->appendText(' '.implode(' / ', $times));
		}

		// Ausnahmen demnächst
		$pending = $openinghours->getIrregularsPending();
		if (count($pending)) {
			\HtmlNode::div()->addClass('irregulars irregulars-pending')
				->append(self::drawOpeningHoursIrregular($pending))
				->appendTo($element);
		}

		return $element->unwrap();
	}

	protected static function drawOpeningHoursIrregular(array $irregulars): \HtmlNode {
		$element = \HtmlNode::div();

		foreach ($irregulars as $irregular) {
			$div = \HtmlNode::div()
				->appendTo($element);

			$div->append(\HtmlNode::strong()->setText($irregular->getDateFormated().':'))
				->appendText(' '.$irregular->title);
		}

		return $element->unwrap();
	}
}