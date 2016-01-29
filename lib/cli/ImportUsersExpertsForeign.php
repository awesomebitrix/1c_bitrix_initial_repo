<?php

namespace totaldict\cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportUsersExpertsForeign extends Command
{
	protected function configure()
	{
		$this
			->setName('totaldict:import_users_experts_foreign')
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

		//выбираем группу
		$group = 0;
		$res = \CGroup::GetList($by = '', $order = '', ['STRING_ID' => 'totaldict_chairman_experts']);
		if ($ob = $res->Fetch()) {
			$group = $ob['ID'];
		}

		//выбираем список статусов
		$statusList = [];
		$rsData = \CUserTypeEntity::GetList([], [
			'ENTITY_ID' => 'USER',
			'FIELD_NAME' => 'UF_TD_ORG_STATUS',
		]);
		if ($fob = $rsData->GetNext()) {
			$ar = ['newby' => 'НОВИЧКИ', 'pretty_boy' => 'КРАСАВЧИКИ', 'catching' => 'ДОГОНЯЮЩИЕ'];
			$res = \CUserFieldEnum::GetList([], ['USER_FIELD_ID' => $fob['ID']]);
			while ($ob = $res->Fetch()) {
				if (empty($ar[$ob['XML_ID']])) continue;
				$statusList[$ar[$ob['XML_ID']]] = $ob['ID'];
			}
		}

		//список стран для заполнения
		$countries = GetCountryArray();
		$countries = array_combine($countries['reference'], $countries['reference_id']);

		$currentStatus = null;
		while (($data = fgetcsv($fh)) !== false) {

			//если заполнена только вторая колонка то это статус
			if (empty($data[0]) && !empty($data[1]) && isset($statusList[$data[1]])) {
				$currentStatus = $statusList[$data[1]];
				continue;
			}

			//массив для загрузки данных пользователя
			$arLoad = [
				'ACTIVE' => 'Y',
				'LAST_NAME' => $data[5],
				'PERSONAL_MOBILE' => $data[7],
				'WORK_POSITION' => $data[6],
				'PERSONAL_COUNTRY' => isset($countries[$data[0]]) ? $countries[$data[0]] : null,
				'PERSONAL_CITY' => $data[1],
				'PERSONAL_STREET' => $data[9],
				'EMAIL' => trim($data[8]),
				'PASSWORD' => 'Test,user,passwr0d',
				'CONFIRM_PASSWORD' => 'Test,user,passwr0d',
				'UF_TD_ORG_STATUS' => $currentStatus,
				'GROUP_ID' => [2, $group],
			];

			//пробуем разбить фамилию и имя
			if (strpos($data[4], ' ')) {
				$explode = explode(' ', trim($data[4]));
				$arLoad['NAME'] = $explode[0];
				$arLoad['SECOND_NAME'] = $explode[1];
			} else {
				$arLoad['NAME'] = trim($data[4]);
			}

			//пробуем завести дату в базу
			if (($time = strtotime($data[10])) !== false) {
				$arLoad['PERSONAL_BIRTHDAY'] = ConvertTimeStamp($time, 'SHORT', 'ru');
			}

			//пробуем найти страну, чтобы привязать пользователя к городу
			$country = \bxpimple\Locator::$item->get('countryFinder')->mergeFilterWith([
				'NAME' => trim($data[0]),
			])->one();
			if ($country) {
				//если нашли страну, то пробуем найти и город
				$city = \bxpimple\Locator::$item->get('cityFinder')->mergeFilterWith([
					'NAME' => trim($data[1]),
					'PROPERTY_TOTALDICT_GEO_CITIES_COUNTRY' => $country->getAttribute('ID')->getValue(),
				])->one();
				if ($city) {
					//если нашли город, то привязываем пользователя
					$arLoad['UF_TD_CITY_IB'] = $city->getAttribute('ID')->getValue();					
				}
			}

			//задаем логин, по умолчанию используем email
			$arLoad['LOGIN'] = $arLoad['EMAIL'];

			//пробуем найти пользователя по его логину
			$user = new \CUser;
			$res = \CUser::GetList(($by = ''), ($order = ''), [
				'LOGIN_EQUAL' => $arLoad['LOGIN'],
			]);
			if ($ob = $res->Fetch()) {
				$arGroups = \CUser::GetUserGroup($ob['ID']);
				if (is_array($arGroups)) {
					$arLoad['GROUP_ID'] = array_unique(array_merge($arLoad['GROUP_ID'], $arGroups));
				}
				//обновляем
				if (!$user->Update($ob['ID'], $arLoad)) {
					$output->writeln('<error>User ' . $arLoad['LOGIN'] . ' error: ' . $user->LAST_ERROR . '</error>');
				} else {
					$output->writeln('<info>User ' . $arLoad['LOGIN'] . ' updated</info>');
				}
			} else {
				//создаем нового
				if (!$user->Add($arLoad)) {
					$output->writeln('<error>User ' . $arLoad['LOGIN'] . ' error: ' . $user->LAST_ERROR . '</error>');
				} else {
					$output->writeln('<info>New user ' . $arLoad['LOGIN'] . ' added</info>');
				}
			}

		}

		fclose($fh);
	}
}