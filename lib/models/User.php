<?php

namespace totaldict\models;

/**
 * Модель для пользователей
 */
class User extends \bxar\user\User
{
	/**
	 * @var array
	 */
	protected $_cityGroups = null;


	/**
	 * Возвращает основной город пользователя
	 * @return \bxar\IActiveRecord
	 */
	public function getCityId()
	{
		return $this->getAttribute('UF_TD_CITY_IB')->getValue();
	}

	/**
	 * Возвращает все города пользователя
	 * @return array
	 */
	public function getAllCitiesId()
	{
		$return = [$this->getCityId()];
		$add = $this->getAttribute('UF_TD_CT_ADD')->getValue();
		if (!empty($add) && is_array($add)) {
			$return = array_merge($return, $add);
		}
		return array_unique($return);
	}

	/**
	 * Возвращает список групп пользователя по отношению к городам
	 * @return array
	 */
	public function getCityGroups()
	{
		if ($this->_cityGroups === null && !$this->isNew()) {
			$ids = \CUser::GetUserGroup($this->getAttribute('id')->getValue());
			$this->_cityGroups = \bxpimple\Locator::$item->get('groupFinder')->mergeFilterWith([
				'ID' => implode(' | ', $ids),
				'STRING_ID' => 'totaldict_city_%',
			])->all();
		}
		return $this->_cityGroups;
	}

	/**
	 * Задает список групп пользователя по отношению к городу
	 * @param array $groups
	 */
	public function setCityGroups(array $groups)
	{
		$this->_cityGroups = $groups;
	}

	/**
	 * Возвращает ссылку на пользователя в админке
	 * @return string
	 */
	public function getAdminUrl()
	{
		if (!$this->isNew()) {
			return '/bitrix/admin/user_edit.php?&ID=' . $this->getAttribute('id')->getValue();
		}
		return null;
	}

	/**
	 * Возвращает полное имя пользователя
	 * @return string
	 */
	public function getFullName()
	{
		$return = '';
		$return .= trim($this->getAttribute('LAST_NAME')->getValue());
		$return .= ($return !== '' ? ' ' : '') . trim($this->getAttribute('NAME')->getValue());
		$return .= ($return !== '' ? ' ' : '') . trim($this->getAttribute('SECOND_NAME')->getValue());
		return trim($return);
	}
}