<?php

use \HfCore\HtmlNode;

/**
 * WP-Tag für die Mitarbeiterliste
 * Trait CyclyFrontendEmployees
 */
trait CyclyFrontendEmployees {
	/**
	 * WP-Tag Mitarbeiterliste
	 * @param $atts
	 * @return HtmlNode
	 */
	public function tagEmployees($atts) {
		$branch = Query::param($atts, 'branch', HfCore\T_INT, 1);
		$body = HtmlNode::div()->id('cycly-employees')->data('branch', $branch);

		foreach ($this->getEmployees($branch) as $emplyee) {
			$item = HtmlNode::div()->addClass('employee')->appendTo($body);

			if ($emplyee->image)
				$item->append(HtmlNode::img()->attr('src', $emplyee->image->getUrlTiny())->attr('title', $emplyee->image->title));
			else
				$item->append(HtmlNode::img()->attr('src', $this->getPluginUrl().'/tpl/employee-empty.jpg')->attr('title', 'Kein Titel'));

			$item->append(HtmlNode::p($emplyee->firstname.' '.$emplyee->lastname)->addClass('name'));
		}

		return $body;
	}
}