<?php

namespace totaldict\components;

/**
 * Модель для геолокации.
 */
class GeoLocator
{
	/**
	 * @var string
	 */
	public $varName = 'city';
	/**
	 * @var int
	 */
	public $cookieTime = 2592000;
	/**
	 * @var int
	 */
	public $cacheTime = 2592000;
	/**
	 * @var array
	 */
	public $select = ['id', 'name'];
	/**
	 * @var int
	 */
	public $iblockId = -1;
	/**
	 * @var string
	 */
	public $idParam = 'id';
	/**
	 * @var \marvin255\bxcache\ICache
	 */
	protected $_cache = null;
	/**
	 * @var \bxar\IFinder
	 */
	protected $_finder = null;
	/**
	 * @var array
	 */
	protected $_list = null;
	/**
	 * @var string
	 */
	protected $_current = null;
	/**
	 * @var \totaldict\components\User
	 */
	protected $_user = null;



	/**
	 * Задает текущий город
	 * @param string $id
	 * @return bool
	 */
	public function setCurrentCity($id)
	{
		global $APPLICATION;

		$list = $this->getList();
		if (isset($list[$id])) {
			$this->_current = $id;
			$APPLICATION->set_cookie($this->varName, $id, time() + $this->cookieTime);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает текущий город
	 * @return string
	 */
	public function getCurrentCity()
	{
		global $APPLICATION;

		$current = null;
		if (!empty($this->_current)) {
			$current = $this->_current;
		} elseif ($cookie = $APPLICATION->get_cookie($this->varName)) {
			$current = $cookie;
		}

		$list = $this->getList();
		if (!isset($list[$current])) {
			$keys = array_keys($list);
			return reset($keys);
		} else {
			return $current;
		}
	}

	/**
	 * @return array
	 */
	public function getCurrentCityArray()
	{
		$current = $this->getCurrentCity();
		$list = $this->getList();
		return $list[$current];
	}



	/**
	 * @return array
	 */
	public function getList()
	{
		if ($this->_list !== null) return $this->_list;
		$this->_list = [];
		$cache = $this->getCache();
		$cId = get_class($this) . '_list';
		if (!$cache || ($this->_list = $cache->get($cId)) === false) {
			$res = \CIBlockElement::GetList(
				['name' => 'asc'],
				['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y'],
				false,
				false,
				$this->select
			);
			while ($ob = $res->GetNext()) {
				$arCity = [];
				foreach ($this->select as $key) {
					$key = str_replace('.', '_', strtoupper($key));
					$value = '';
					if (isset($ob[$key])) {
						$value = $ob[$key];
					} elseif (isset($ob[$key . '_VALUE'])) {
						$value = $ob[$key . '_VALUE'];
					}
					$arCity[strtolower($key)] = $value;
				}
				$asId = strtolower($this->idParam);
				if (isset($arCity[$asId])) {
					$this->_list[$arCity[$asId]] = $arCity;
				}
			}
			if ($cache) $cache->set($cId, $this->_list, $this->cacheTime);
		}
		return $this->_list;
	}



	/**
	 * @param \marvin255\bxcache\ICache $cache
	 */
	public function setCache(\marvin255\bxcache\ICache $cache)
	{
		$this->_cache = $cache;
		return $this;
	}

	/**
	 * @return \marvin255\bxcache\ICache
	 */
	public function getCache()
	{
		return $this->_cache;
	}
}