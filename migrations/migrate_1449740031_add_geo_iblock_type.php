<?php

/*class migrate_1449740031_add_geo_iblock_type extends \marvin255\bxmigrate\migrate\Coded
{
	protected $name = 'totaldict_geo';


	public function up()
	{
		$this->IblockTypeCreate([
			'ID' => $this->name,
			'SORT' => 0,
			'LANG' => [
				'en' => [
					'NAME' => 'Geo',
				],
				'ru' => [
					'NAME' => 'Гео',
				],
			],
		]);
	}

	public function down()
	{
		$this->IblockTypeDelete($this->name);
	}
}*/