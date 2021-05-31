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

		print_r($this->getGitHub()->update());
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