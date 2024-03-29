<?php
namespace Cycly;

class CyclyApi {

	/**
	 * API-Request
	 * @param string|array $method (ohne / am Anfang und Schluss!)
	 * @param string $type
	 * @param array $data
	 * @return mixed
	 */
	public static function request($method = 'account', string $type = 'GET', array $data = []) {
		if (is_array($method))
			$method = implode('/', $method);

		$content = json_encode($data);
		$hash = base64_encode(hash_hmac('SHA256', $type."\n".$method."\n".$content, get_option('cycly_secret')));
		$result = \HfCore\CurlClient::create(get_option('cycly_url').$method)
			->setTimeout(60)
			->setPOSTData($content)
			->setOption(CURLOPT_CUSTOMREQUEST, $type)
			->setHTTPHeader(['Content-type: application/json',
				'X-Public-Key: '.get_option('cycly_key'),
				'X-Signed-Request-Hash: '.$hash
			])->exec();

		$jsonResult = $result->getFromJSON();

		if ($result->httpCode == 401)
			throw new AccessException();
		if ($result->httpCode == 403)
			throw new AccessException();
		if ($result->httpCode != 200)
			throw new ApiException();

		return $jsonResult;
	}

	/**
	 * API-Request (nur neu durchführen, falls maxAge nicht überschritten ist und somit die Informationen im Cache veraltet sind)
	 * @param string $method
	 * @param string $type
	 * @param array $data
	 * @param int $maxAge in Sekunden
	 * @return mixed
	 */
	public static function cacheRequest($method = 'account', string $type = 'GET', array $data = [], ?int $maxAge = null) {
		if (!$maxAge)
			$maxAge = get_option('cycly_cache_age') ? get_option('cycly_cache_age') * 3600 : 3600;

		//$maxAge = 0;
		$identifier = [];
		$identifier[] = is_array($method) ? implode('/', $method) : $method;
		$identifier[] = $type;
		$identifier[] = print_r($data, true);
		$id = md5(implode('|', $identifier));

		$result = \HfCore\System::getInstance()->getCacheController()->get($id);

		if ($result)
			return $result;

		$result = self::request($method, $type, $data);
		\HfCore\System::getInstance()->getCacheController()->set($id, $result, $maxAge);

		return $result;
	}
}

class AccessException extends \Exception {

}

class ApiException extends \Exception {

}