<?php
namespace HfCore;

class Api {
	/**
	 * Wordpress API Endpunkt hinzufügen
	 * Z.B. http://cycly-wp.ch.185-117-170-94.srv04.webpreview.ch/wp-json/pluginname/methodenname/12
	 * @param $method methodenname/(?P<id>\d+)
	 * @param $callback [$this,'methodenname']
	 */
	public function addEndpoint(string $method, $callback) {
		add_action('rest_api_init', function () use ($callback, $method) {
			register_rest_route(System::getInstance()->getPluginName().'/', $method, array(
				'methods' => 'GET,POST',
				'callback' => $callback
			));
		});
	}
}