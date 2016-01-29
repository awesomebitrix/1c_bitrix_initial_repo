<?php

namespace totaldict\components;

/**
 * Модель, которая возвращает дату начала тотального диктанта.
 */
class Timer
{
	/**
	 * Функция, которая фозвращает отформатрированную дату начала диктанта для указанного города
	 * @param \bxar\element\Element $city
	 * @param string $format
	 * @return string
	 */
	public function getStartTimeForCity(\bxar\element\Element $city, $format = 'd.m.Y H:i')
	{
		$time = strtotime('2016-04-10 16:00');
		return \FormatDate($format, $time);
	}
}