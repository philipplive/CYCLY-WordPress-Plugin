<?php
namespace Cycly;

class Image extends Item {
	/**
	 * ID
	 * @var int
	 */
	public $id = 0;

	/**
	 * URL zum Bild
	 * @var string
	 */
	public $url = '';

	/**
	 * MD5 Hash
	 * @var string
	 */
	public $md5 = '';

	/**
	 * Bezeichnung
	 * @var string
	 */
	public $title = '';

	/**
	 * Sortierung
	 * @var int
	 */
	public $sort = 0;

	/**
	 * Link zum Bild, max 1000px Höhe/Breite
	 * @return string
	 */
	public function getUrlFull(): string {
		return $this->getResizedImageLink(900, 900);
	}

	/**
	 * Link zum Bild, max 600px Höhe/Breite
	 * @return string
	 */
	public function getUrlSmall(): string {
		return $this->getResizedImageLink(600, 600);
	}

	/**
	 * Link zum Bild, max 200px Höhe/Breite
	 * @return string
	 */
	public function getUrlTiny(): string {
		return $this->getResizedImageLink(200, 200);
	}

	/**
	 * Pfad zum gecachten Bild
	 * @param int $width
	 * @param int $height
	 * @param int $type
	 * @return string|null
	 */
	public function getResizedImageLink(int $width, int $height, int $type = \HfCore\Image::RESIZE_MAX): string {
		$name = 'cacheimage-'.$this->id.'-'.$this->md5.'-'.$width.'-'.$height.'.jpeg';

		$file = \HfCore\IO::getFile(\HfCore\System::getInstance()->getPluginPath().'cache/'.$name);

		if (!$file->exists()) {
			$image = new \HfCore\Image($this->getFile()->getPath());
			$image->resize($width, $height, $type);
			$file->write($image->getString(IMAGETYPE_JPEG, 75));
		}

		return \HfCore\System::getInstance()->getPluginCacheUrl().$name;
	}

	/**
	 * Pfad zum gecachten Bild
	 * @return \FileLocal
	 */
	public function getFile(): \HfCore\FileLocal {
		$file = \HfCore\IO::getFile(\HfCore\System::getInstance()->getPluginCachePath().'/cacheimage-'.$this->id.'-'.$this->md5.'.png');

		if (!$file->exists())
			$file->write(\HfCore\CurlClient::create($this->url)->exec()->getBody());

		return $file;
	}

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data): self {
		parent::fromData($data);
		$this->id = $data->id;
		$this->url = $data->url;
		$this->md5 = $data->md5;
		$this->title = $data->title;
		$this->sort = $data->sort;
		return $this;
	}

	public function __toString() {
		return $this->getFile()->getPath();
	}
}