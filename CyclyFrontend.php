<?php

use \HfCore\HtmlNode;

class CyclyFrontend extends CyclySystem  {
	use CyclyFrontendVehicles;
	use CyclyFrontendEmployees;
	use CyclyFrontendBlog;

	public function __construct() {
		parent::__construct();

		$this->getTemplateController()->addCssFile('tpl/cycly.less');
		$this->getTemplateController()->addJsFile('tpl/cycly.js');

		// Wordpress Tags erfassen
		add_shortcode('show_vehicles', [$this, 'tagVehicles']); // Veloübersicht
		add_shortcode('show_employees', [$this, 'tagEmployees']); // Mitarbeiterübersicht
		add_shortcode('show_blog', [$this, 'tagBlog']); // Blog

		// Api Endpunkte
		$this->getApi()->addEndpoint('bike/(?P<id>\d+)', [$this, 'apiGetBike']);
	}

	/**
	 * @param Exception $ex
	 * @return string
	 * @throws Exception
	 */
	public function show_error(Exception $ex){
		if(\HfCore\System::getInstance()->isAdmin())
			throw $ex;

		if($ex instanceof \Cycly\ApiException)
			return 'Unbekannter CYCLY API Fehler';
		if($ex instanceof \Cycly\AccessException)
			return 'CYCLY Berechtigungsfehler';

		throw $ex;
	}
}