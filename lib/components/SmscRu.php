<?php

namespace totaldict\components;

/**
 * Сервис для отправки смс-сообщений
 */
class SmscRu
{
	/**
	 * @var int
	 */
	protected $_timeout = 20;
	/**
	 * @var string
	 */
	protected $_login = null;
	/**
	 * @var string
	 */
	protected $_password = null;
	/**
	 * @var array
	 */
	protected $_additionalParams = [];
	/**
	 * @var array
	 */
	protected $_actions = [
		'send' => [
			'method' => 'POST',
			'url' => 'http://smsc.ru/sys/send.php',
		],
	];



	/**
	 * Шорткат для отправки сообщения на один номер
	 * @param string $to
	 * @param string $message
	 * @return mixed
	 */
	public function sendMessage($to, $message)
	{
		return $this->doRequest('send', [
			'phones' => $to,
			'mes' => iconv('UTF-8', 'CP1251', trim($message)),
		]);
	}

	/**
	 * Шорткат для отправки сообщения на несколько номеров
	 * @param array $to
	 * @param string $message
	 * @return mixed
	 */
	public function sendMessageAll(array $to, $message)
	{
		return $this->doRequest('send', [
			'phones' => implode(',', $to),
			'mes' => iconv('UTF-8', 'CP1251', trim($message)),
		]);
	}



	/**
	 * Отправляем запрос к api
	 * @param \antares\api\IRestRequest $request
	 * @return mixed
	 */
	protected function doRequest($action, $params)
	{
		$description = $this->getActionDescription($action);
		if ($description === null) {
			throw new \Exception("Undefined api method: {$action}");
		}

		//заполняем параметры запроса
		$params = is_array($params) ? $params : [];
		//по убыванию приоритетности: параметры из запроса, дополнительные параметры из настроек компонента
		$params = array_merge($this->getAdditionalParams(), $params);
		//вносим данные по умолчанию
		$params['login'] = $this->getLogin();
		$params['psw'] = md5($this->getPassword());
		//пусть ответ всегда будет в json
		$params['fmt'] = 3;

		//заголовок запроса
		$method = empty($description['method']) ? 'GET' : strtoupper($description['method']);

		//ссылка на запрос
		if (!empty($description['url'])) {
			$url = $description['url'];
		} else {
			throw new \Exception("Undefined url for method: {$action}");
		}

		//инициируем curl
		$ch = curl_init();

		switch ($method) {
			case 'GET':
				if (!empty($params)) {
					$url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($params);
				}
				break;
			case 'POST':
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
				if (!empty($params)) {
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
				}
				break;
			default:
				throw new \Exception('Unknown request method');
				break;
		}

		//дополнительные опции curl
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());

		//исполняем запрос
		$output = curl_exec($ch);

		if ($output === false) {
			$error = curl_error($ch);
			curl_close($ch);
			throw new \Exception("Curl error: {$error}");
		} else {
			curl_close($ch);
			list($headers, $body) = explode("\r\n\r\n", $output, 2);
			return $this->parseResponse($headers, $body);
		}
	}

	/**
	 * Парсит данные из ответа и возвращает их
	 * @param string $headers
	 * @param string $body
	 */
	protected function parseResponse($headers, $body)
	{
		return json_decode($body, true);
	}

	/**
	 * Возвращает описание действия для запроса к api
	 * @param string $name
	 * @return array
	 */
	protected function getActionDescription($name)
	{
		$name = trim($name);
		return isset($this->_actions[$name]) ? $this->_actions[$name] : null;
	}



	/**
	 * @param string $val
	 */
	public function setLogin($val)
	{
		$this->_login = trim($val);
	}

	/**
	 * @return string
	 */
	public function getLogin()
	{
		return $this->_login;
	}


	/**
	 * @param string $val
	 */
	public function setPassword($val)
	{
		$this->_password = trim($val);
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->_password;
	}


	/**
	 * Дополнительные параметры, которые будут уходить с каждым запросом
	 * @param array $val
	 */
	public function setAdditionalParams(array $val)
	{
		$this->_additionalParams = $val;
	}

	/**
	 * @return array
	 */
	public function getAdditionalParams()
	{
		return $this->_additionalParams;
	}


	/**
	 * Таймаут на запрос в секундах
	 * @param int $val
	 */
	public function setTimeout($val)
	{
		$this->_timeout = (int) $val;
	}

	/**
	 * @return int
	 */
	public function getTimeout()
	{
		return $this->_timeout > 0 ? $this->_timeout : 20;
	}
}