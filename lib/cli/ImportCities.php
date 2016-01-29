<?php

namespace totaldict\cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCities extends Command
{
	protected function configure()
	{
		$this
			->setName('totaldict:import_cities')
			->setDescription('Import cities from csv file')
			->addArgument(
				'file',
				InputArgument::REQUIRED,
				''
			);
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$file = $input->getArgument('file');
		
		if (!file_exists($file) || !($fh = fopen($file, 'r'))) {
			$output->writeln('<error>Can\'t read data from file</error>');
			return null;
		}

		while (($data = fgetcsv($fh)) !== false) {
			//без идентификатора не обрабатываем
			if (empty($data[0])) continue;
			//наполняем данные по городу
			$arLoad = [
				'xml_id' => $data[0],
				'name' => trim(preg_replace('/^(.+)\s+\(\d+\)$/i', '$1', $data[1])),
				'code' => trim(strtolower($data[2])),
				'property_totaldict_geo_cities_name_en' => ucfirst($data[2]),
				'property_totaldict_geo_cities_timezone' => '+0:00',
				'property_totaldict_geo_cities_td_timezone' => '+0:00',
			];
			if (preg_match('/^([^\/]+)\/#place_.+$/i', $data[2], $matches)) {
				$arLoad['code'] = $data[0] . '-' . strtolower($this->translit($arLoad['name']));
				$arLoad['property_totaldict_geo_cities_name_en'] = ucfirst($this->translit($arLoad['name']));
				$baseCity = \bxpimple\Locator::$item->get('cityFinder')->mergeFilterWith(['CODE' => $matches[1]])->one();
				if ($baseCity) {
					$arLoad['property_totaldict_geo_cities_base_city'] = $baseCity->getAttribute('ID')->getValue();
				}
			}
			if (!empty($data[3])) {
				//пробуем найти страну
				$country = \bxpimple\Locator::$item->get('countryFinder')->mergeFilterWith(['NAME' => $data[3]])->one();
				if (!$country) {
					//если не нашли,то создаем новую
					$country = \bxpimple\Locator::$item->get('countryElement');
					$country->setValues([
						'name' => $data[3],
						'code' => strtolower($this->translit($data[3])),
					]);
					if (!$country->save()) {
						throw new \Exception(json_encode($country->getErrors()));
					} else {
						$output->writeln('<info>Add country ' . $data[3] . '</info>');
					}
				}
				//добавляем страну в модель для загрузки
				$arLoad['property_totaldict_geo_cities_country'] = $country->getAttribute('ID')->getValue();
				if (!empty($data[4])) {
					//пробуем найти область
					$state = \bxpimple\Locator::$item->get('stateFinder')->mergeFilterWith([
						'NAME' => $data[4],
						'PROPERTY_TOTALDICT_GEO_STATES_COUNTRY' => $country->getAttribute('ID')->getValue(),
					])->one();
					if (!$state) {
						//если не нашли область, то создаем новую
						$state = \bxpimple\Locator::$item->get('stateElement');
						$state->setValues([
							'name' => $data[4],
							'code' => strtolower($this->translit($data[4])),
							'property_totaldict_geo_states_country' => $country->getAttribute('ID')->getValue(),
						]);
						if (!$state->save()) {
							throw new \Exception(json_encode($state->getErrors()));
						} else {
							$output->writeln('<info>Add state ' . $data[4] . '</info>');
						}
					}
					//добавляем область в модель для загрузки
					$arLoad['property_totaldict_geo_cities_state'] = $state->getAttribute('ID')->getValue();
				}
			}
			//пробуем найти указанный город
			$city = \bxpimple\Locator::$item->get('cityFinder')->mergeFilterWith(['XML_ID' => $arLoad['xml_id']])->one();
			if (!$city) {
				//если не нашли город, то создаем новый
				$city = \bxpimple\Locator::$item->get('cityElement');
			}
			$city->setValues($arLoad);
			if (!$city->save()) {
				throw new \Exception(json_encode($city->getErrors()));
			} else {
				$output->writeln('<info>Proceed city ' . $data[1] . '</info>');
			}
		}

		fclose($fh);
	}


	protected function translit($str)
	{
		$rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
		$lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
		return str_replace($rus, $lat, $str);
	}
}