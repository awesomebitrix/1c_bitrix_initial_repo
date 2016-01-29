<?php

namespace totaldict\cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPopularCities extends Command
{
	protected function configure()
	{
		$this
			->setName('totaldict:import_popular_cities')
			->setDescription('Import popular cities from csv file')
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

		//собираем из файла популярные города
		$popular = [];
		while (($data = fgetcsv($fh)) !== false) {
			//без идентификатора не обрабатываем
			if (empty($data[0])) continue;
			//пробуем найти указанный город
			$popular[] = $data[0];
		}
		fclose($fh);

		$popular = array_unique($popular);
		$id = 0;

		//перебираем все города
		while (
			$list = \bxpimple\Locator::$item->get('cityFinder')->mergeFilterWith(['>ID' => $id])->setOrder(['id' => 'asc'])->setLimit(50)->all()
		){
			foreach ($list as $city) {
				$pop = in_array($city->name->value, $popular);
				$city->property_city_is_popular->value = $pop ? '1' : '';
				$city->save();
				$output->writeln('<info>Proceed city ' . $city->name->value . '. City is ' . ($pop ? 'popular' : 'not popular') . '</info>');
				$id = $city->id->value;
			}
		}
	}
}