<?php
namespace HfCore;

class System {
	/**
	 * @var System
	 */
	protected static $instance;

	/**
	 * @var Cronjob
	 */
	private $cronjobs = null;

	/**
	 * @var Template
	 */
	private $template = null;

	/**
	 * @var Api
	 */
	private $api = null;
	/**
	 * @var Cache
	 */
	private $cache = null;

	public function __construct() {
		if (isset(self::$instance))
			throw new Exception('System-Instanz existiert bereits');

		// Datentypen
		define('HFCore\T_BOOL', 'b');
		define('HFCore\T_INT', 'i');
		define('HFCore\T_DOUBLE', 'd');
		define('HFCore\T_STR', 's');
		define('HFCore\T_ARR', 'a');

		// Core-Klassen laden
		require_once('Query.php');
		require_once('Price.php');
		require_once('Time.php');
		require_once('Image.php');
		require_once('Xml.php');
		require_once('Color.php');
		require_once('HtmlNode.php');
		require_once('Validator.php');
		require_once('Image.php');
		require_once('io/IO.php');
		require_once('io/FileInfo.php');
		require_once('io/FileAbstract.php');
		require_once('io/FileLocal.php');
		require_once('io/FolderAbstract.php');
		require_once('io/FolderLocal.php');
		require_once('CurlClient.php');
		require_once('Cache.php');

		// Libs
		require_once('libs/lessc.inc.php');

		// Instanz übertragen
		self::$instance = $this;
	}

	/**
	 * Gibt Instanz von System zurück
	 * @return self
	 */
	public static function getInstance(): self {
		if (!isset(self::$instance))
			die('System nicht instanziert');

		return self::$instance;
	}

	public function getCronjobController(): Cronjob {
		if (!$this->cronjobs) {
			require_once('Cronjob.php');
			$this->cronjobs = new Cronjob();
		}

		return $this->cronjobs;
	}

	public function getApi(): Api {
		if (!$this->api) {
			require_once('Api.php');
			$this->api = new Api();
		}

		return $this->api;
	}

	/**
	 * Widget registrieren
	 * @param string $name
	 */
	public function addWidget(string $name) {
		require_once($this->getPluginPath().'/'.$name.'.php');

		add_action('widgets_init', function () use ($name) {
			register_widget($name);
		});

	}

	public function getTemplateController(): Template {
		if (!$this->template) {
			require_once('Template.php');
			$this->template = new Template();
			$this->template->addJsFile('core/system.js');
		}

		return $this->template;
	}

	public function getCacheController(string $name = ''): Cache {
		if(!$this->cache){
			$this->cache = new Cache($name);
		}

		return $this->cache;
	}

	/**
	 * Name es Plugins
	 * @return string
	 */
	public function getPluginName(): string {
		return explode('/', plugin_basename(__FILE__))[0];
	}

	/**
	 * @return string Serverpfad /srv/wwww/blabla/....
	 */
	public function getPluginPath(): string {
		return WP_PLUGIN_DIR.'/'.$this->getPluginName().'/';
	}

	/**
	 * Pfad zum Cache-Ordner
	 * @param string $folder Cache Unterordner wählen
	 * @return string  /srv/wwww/blabla/..../cache/
	 */
	public function getPluginCachePath(string $folder = ''): string {
		if ($folder && substr($folder, -1) != '/')
			$folder .= '/';


		return $this->getPluginPath().'/cache/'.$folder;
	}

	/**
	 * Pfad zum Cache-Ordner
	 * @param string $folder Cache Unterordner wählen
	 * @return string  www.test.ch/blabla....
	 */
	public function getPluginCacheUrl(string $folder = ''): string {
		if ($folder && substr($folder, -1) != '/')
			$folder .= '/';

		return $this->getPluginUrl().'cache/'.$folder;
	}

	/**
	 * @return string www.test.ch/blabla....
	 */
	public function getPluginUrl(): string {
		return get_site_url().'/wp-content/plugins/'.$this->getPluginName().'/';
	}

	/**
	 * Aktuelle BenutzerID
	 * @return int
	 * @throws \Exception
	 */
	public function getCurrentUserId(): int {
		$userId = wp_validate_logged_in_cookie(false);

		if ($userId === false)
			throw new \Exception('User nicht eingeloggt');

		return $userId;
	}

	/**
	 * @return bool
	 */
	public function isAdmin(): bool {
		try {
			$user = get_userdata($this->getCurrentUserId());

			if (in_array('administrator', $user->roles))
				return true;
		} catch (\Exception $ex) {
			// Nicht eingeloggt
		}

		return false;
	}
}