<?php

namespace totaldict\models;

/**
 * Модель для чек-листов для городов.
 */
class CityChecklist extends \bxar\element\Element
{
	/**
	 * @var \bxar\IActiveRecord
	 */
	protected $_city = null;


	/**
	 * Возвращает подробную информацию о готовности чек-листа
	 * @param bool $checkAutomaticallyStates
	 * @return array
	 */
	public function getStates($checkAutomaticallyStates = false)
	{
		if ($checkAutomaticallyStates) $this->checkAutoStates();
		$return = [];
		$attributes = $this->getAttributes();
		foreach ($attributes as $key => $attr) {
			$params = $attr->getParams();
			if (empty($params['PROPERTY_TYPE']) || ($params['PROPERTY_TYPE'] !== 'S' && $params['PROPERTY_TYPE'] !== 'L')) continue;
			switch ($params['PROPERTY_TYPE']) {
				case 'L':
					$value = (bool) $attr->getXmlId();
					break;
				default:
					$value = (bool) $attr->getValue();
					break;
			}
			$arState = [
				'code' => $params['CODE'],
				'label' => $params['NAME'],
				'value' => $value,
				'value_text' => $value ? 'Готово' : 'Не готово',
			];
			$return[] = $arState;
		}
		return $return;
	}


	/**
	 * Возвращает статус готовности чек-листа
	 * @param bool $checkAutomaticallyStates
	 * @return bool
	 */
	public function isChecked($checkAutomaticallyStates = false)
	{
		$states = $this->getStates($checkAutomaticallyStates);
		$return = !empty($states);
		foreach ($states as $state) {
			if (!isset($state['value']) || $state['value'] !== true) {
				$return = false;
				break;
			}
		}
		return $return;
	}

	/**
	 * Проверяет пункты чек-листа, которые можно проверить автоматически
	 * @return null
	 */
	public function checkAutoStates()
	{
		//проверяем добавлены ли площадки
		$stages = \bxpimple\Locator::$item->get('cityStageFinder')->mergeFilterWith([
			'PROPERTY_CITIES_STAGES_CITY' => $this->property_totaldict_cities_checklists_city->value,
		])->count();
		$this->property_totaldict_cities_checklists_stages->value = $stages ? '1' : '';
		//проверяем опубликована ли городская страница
		$page = $this->getCity()->detail_text->value;
		$this->property_totaldict_cities_checklists_is_page->value = !empty($page) ? '1' : '';
	}


	/**
	 * Задает город
	 * @param \bxar\IActiveRecord $city
	 * @return \totaldict\models\CityChecklist
	 */
	public function setCity(\bxar\IActiveRecord $city)
	{
		$this->_city = $city;
		return $this;
	}

	/**
	 * Возвращает чек-лист
	 * @return \bxar\IActiveRecord
	 */
	public function getCity()
	{
		if ($this->_city === null) {
			$this->_city = \bxpimple\Locator::$item->get('cityFinder')->mergeFilterWith([
				'ID' => $this->getAttribute('PROPERTY_TOTALDICT_CITIES_CHECKLISTS_CITY')->getValue(),
			])->one();
			if (!$this->_city) $this->_city = false;
		}
		return $this->_city;
	}
}