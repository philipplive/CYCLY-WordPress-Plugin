<?php

use \HfCore\HtmlNode;

class CyclyFrontend extends CyclySystem  {
	use CyclyFrontendVehicles;
	use CyclyFrontendEmployees;

	public function __construct() {
		parent::__construct();

		$this->getTemplateController()->addCssFile('tpl/cycly.less');
		$this->getTemplateController()->addJsFile('tpl/cycly.js');

		// Wordpress Tags erfassen
		add_shortcode('show_vehicles', [$this, 'tagVehicles']); // Veloübersicht
		add_shortcode('show_employees', [$this, 'tagEmployees']); // Mitarbeiterübersicht

		// Api Endpunkte
		$this->getApi()->addEndpoint('bike/(?P<id>\d+)', [$this, 'apiGetBike']);
	}
}