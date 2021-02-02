<?php

use \HfCore\HtmlNode;

/**
 * WP-Tag für die Mitarbeiterliste
 * Trait CyclyFrontendEmployees
 */
trait CyclyFrontendEmployees{
	/**
	 * WP-Tag Mitarbeiterliste
	 * @param $atts
	 * @return HtmlNode
	 */
	public function tagEmployees($atts) {
		$body = HtmlNode::div()->id('cycly-employees')->data('branch', isset($atts['branch']) ? $atts['branch'] : 1);

		foreach ($this->getEmployees(isset($atts['branch']) ? $atts['branch'] : 1) as $emplyee) {
			$item = HtmlNode::div()->addClass('employee')->appendTo($body);
			$item->append(HtmlNode::img()->attr('src', $emplyee->image->getUrlTiny())->attr('title', $emplyee->image->title));
			$item->append(HtmlNode::p($emplyee->firstname.' '.$emplyee->lastname)->addClass('name'));
		}

		return $body;
	}
}