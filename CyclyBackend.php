<?php

class CyclyBackend extends CyclySystem {

	public function __construct() {
		parent::__construct();
		add_action('admin_menu', [$this, 'addOptionPage']);
		add_action('admin_init', [$this, 'adminInit']);

		$this->getTemplateController()->addCssFile('tpl/backend.less');
	}

	/**
	 * Eintrag im Hauptmenü
	 */
	public function addOptionPage() {
		add_menu_page(
			'CYCLY Optionen',
			'CYCLY-Connector',
			'manage_options',
			'cycly',
			function () {
				include('tpl/tpl_settings.php');
			},
			'dashicons-external', 100
		);
	}

	public function adminInit() {
		// Einstellungen API
		add_settings_section(
			'cylcy_settings_section',
			'API Einstellungen',
			function () {
				echo HfCore\HtmlNode::p('Verbindungseinstellungen für die REST-API');
			},
			'cylcy_settings'
		);


		add_settings_field(
			'cycly_url',
			'URL',
			function () {
				echo HfCore\HtmlNode::input()->attr('name', 'cycly_url')->attr('type', 'text')->addClass('large')->value(get_option('cycly_url'));
			},
			'cylcy_settings',
			'cylcy_settings_section'
		);

		add_settings_field(
			'cycly_key',
			'Öffentlicher Schlüssel',
			function () {
				echo HfCore\HtmlNode::input()->attr('name', 'cycly_key')->attr('type', 'text')->addClass('large')->value(get_option('cycly_key'));
			},
			'cylcy_settings',
			'cylcy_settings_section'
		);

		add_settings_field(
			'cycly_secret',
			'Geheimer Schlüssel',
			function () {
				echo HfCore\HtmlNode::input()->attr('name', 'cycly_secret')->attr('type', 'text')->addClass('large')->value(get_option('cycly_secret'));
			},
			'cylcy_settings',
			'cylcy_settings_section'
		);

		register_setting('cylcy_settings', 'cycly_key');
		register_setting('cylcy_settings', 'cycly_secret');
		register_setting('cylcy_settings', 'cycly_url');

		// Erweiterte Einstellungen
		add_settings_section(
			'cylcy_settings_more_section',
			'Einstellungen',
			function () {
				echo HfCore\HtmlNode::p('Anwendungsspezifisch Einstellungen');
			},
			'cylcy_settings_more'
		);

		add_settings_field(
			'cycly_hide_vehicle_without_image',
			'Fahrzeuge ohne Bild ausblenden',
			function () {
				echo HfCore\HtmlNode::input()->attr('name', 'cycly_hide_vehicle_without_image')->attr('type', 'checkbox')->attr('checked', get_option('cycly_hide_vehicle_without_image') ? true : null);
			},
			'cylcy_settings_more',
			'cylcy_settings_more_section'
		);

		register_setting('cylcy_settings_more', 'cycly_hide_vehicle_without_image');

		add_settings_field(
			'cycly_cache_age',
			'Cachedauer (in Stunden)',
			function () {
				echo HfCore\HtmlNode::input()->attr('name', 'cycly_cache_age')->attr('type', 'number')->attr('min','1')->attr('step','1')->addClass('large')->value(get_option('cycly_cache_age') ? get_option('cycly_cache_age') : '1');
			},
			'cylcy_settings_more',
			'cylcy_settings_more_section'
		);

		register_setting('cylcy_settings_more', 'cycly_cache_age');
		register_setting('cylcy_settings_more', 'cycly_hide_vehicle_without_image');

		// Debug
		add_settings_section(
			'cylcy_debug',
			'',
			function () {
				echo '
				<div class="card" style="opacity: 0.5;">
				<h3>Debug</h3>
					<p>Files im Cache: '.count(\HfCore\IO::getFolder(\HfCore\System::getInstance()->getPluginCachePath())->getFiles()).'</p>
				</div>
				';
			},
			'cylcy_debug'
		);
	}
}