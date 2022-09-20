<?php

use \HfCore\HtmlNode;

/**
 * WP-Tag für den Blog
 * Trait CyclyFrontendBlog
 */
trait CyclyFrontendBlog {

	/**
	 * WP-Tag Veloliste
	 * @param $atts
	 * @return string
	 */
	public function tagBlog($atts): string {

		$this->getBlogEntrys();

		return '';
	}
}