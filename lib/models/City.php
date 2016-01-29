<?php

namespace totaldict\models;

/**
 * Модель для городов.
 */
class City extends \bxar\element\Element
{
	/**
	 * @var \bxar\IActiveRecord
	 */
	protected $_checkList = null;
	/**
	 * @var array
	 */
	protected $_team = null;


	/**
	 * Проверяет существует ли чек-лист для города и, если не существует, то создает новый
	 * @return string
	 */
	public function createCheckList()
	{
		$checkList = $this->getCheckList();
		if (!$checkList) {
			$model = \bxpimple\Locator::$item->get('checklistElement');
			$model->setValues([
				'name' => 'Чек-лист для ' . $this->getAttribute('name')->getValue(),
				'code' => 'check_list_' . $this->getAttribute('code')->getValue(),
				'property_totaldict_cities_checklists_city' => $this->getAttribute('id')->getValue(),
			]);
			if ($model->save()) {
				$this->setCheckList($model);
				return $model->getAttribute('id')->getValue();
			} else {
				return null;
			}
		} else {
			return $checkList->getAttribute('id')->getValue();
		}
	}


	/**
	 * Задает чек-лист
	 * @param \bxar\IActiveRecord $list
	 * @return \totaldict\models\City
	 */
	public function setCheckList(\bxar\IActiveRecord $list)
	{
		$this->_checkList = $list;
		return $this;
	}

	/**
	 * Возвращает чек-лист
	 * @return \bxar\IActiveRecord
	 */
	public function getCheckList()
	{
		if ($this->_checkList === null && !$this->isNew()) {
			$this->_checkList = \bxpimple\Locator::$item->get('checklistFinder')->mergeFilterWith([
				'PROPERTY_TOTALDICT_CITIES_CHECKLISTS_CITY' => $this->getAttribute('id')->getValue(),
			])->one();
			if (!$this->_checkList) $this->_checkList = false;
		}
		return $this->_checkList;
	}


	/**
	 * Задает команду для города
	 * @param array $team
	 */
	public function setTeam(array $team)
	{
		$this->_team = $team;
	}

	/**
	 * Возвращает команду для города
	 * @return array
	 */
	public function getTeam()
	{
		if ($this->_team === null && !$this->isNew()) {
			$this->_team = \bxpimple\Locator::$item->get('userFinder')->mergeFilterWith([
				'ACTIVE' => 'Y',
				'UF_TD_CITY_IB' => $this->getAttribute('id')->getValue(),
			])->all();
		}
		return $this->_team;
	}

	/**
	 * Возвращает команду для города, разбитую по группам
	 * @return array
	 */
	public function getTeamGrouped()
	{
		$return = [];
		$team = $this->getTeam();
		if (!empty($team)) {
			foreach ($team as $member) {
				$groups = $member->getCityGroups();
				foreach ($groups as $group) {
					$return[$group->getAttribute('id')->getValue()]['group'] = $group;
					$return[$group->getAttribute('id')->getValue()]['list'][] = $member;
				}
			}
		}
		return $return;
	}
}