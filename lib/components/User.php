<?php

namespace totaldict\components;

class User
{
	/**
	 * @var \bxar\IFinder
	 */
	protected $_finder = null;
	/**
	 * @var \bxar\IActiveRecord
	 */
	protected $_model = null;



	/**
	 * Магия. Пробуем вызвать метод сначала с битриксового объекта, а потом со встроенного
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$global = $this->getGlobalUser();
		if (method_exists($global, $name)) {
			return call_user_func_array([$global, $name], $arguments);
		} else {
			$model = $this->getUserModel();
			if ($model && method_exists($model, $name)) {
				return call_user_func_array([$model, $name], $arguments);
			} else {
				throw new \Exception('Method ' . $name . ' doesn\'t exist');				
			}
		}
	}



	/**
	 * @return \bxar\IActiveRecord
	 */
	public function getUserModel()
	{
		if ($this->_model !== null) return $this->_model;
		$this->_model = false;
		$globalUser = $this->getGlobalUser();
		$id = $globalUser->GetID();
		if ($id) {
			$user = $this->getFinder()->mergeFilterWith(['ID' => $id])->one();
			$this->_model = $user ? $user : false;
		}
		return $this->_model;
	}


	/**
	 * @param \bxar\IFinder $finder
	 */
	public function setFinder(\bxar\IFinder $finder)
	{
		$this->_finder = $finder;
		return $this;
	}

	/**
	 * @return \bxar\IFinder
	 */
	public function getFinder()
	{
		return $this->_finder;
	}


	/**
	 * Возвращает ссылку на глобального пользователя битрикса
	 * @return mixed
	 */
	protected function getGlobalUser()
	{
		global $USER;
		return $USER;
	}

	/**
	 * Возвращает админский статус для города
	 * @param int $city
	 * @return string
	 */
	public function getCityAdminStatus($city = null, $page = null)
	{
		if ($this->IsAdmin()) {
			//админ видит все в любом случае
			return true;
		} elseif ($page === 'menu') {
			//меню видят те юзеры, которые есть хоть в одной из городских групп
			$cityGroups = $this->getCityGroups();
			return empty($cityGroups) ? null : true;
		} elseif ($city) {
			//для конкретных городов уже проверяем доступы
			//список групп пользователя
			$cityGroups = $this->getCityGroups();
			//список городов пользователя
			$cities = $this->getAllCitiesId();
			//пользователь должен быть в группе с правами редактирования страниц и привязан к этому городу
			return (!empty($cityGroups) && in_array($city, $cities) && $this->getAttribute('UF_TD_CITY')->value) ? true : null;
		} else {
			//остальные не видят ничего
			return null;			
		}
	}
}