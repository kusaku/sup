<?php
/*
	Класс таблицы
*/

class Site extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'site';
	}

	public function relations()
	{
		return array(
			'package'=>array(self::HAS_MANY, 'Package', 'site_id'),
			'client'=>array(self::BELONGS_TO,'People',	'client_id'),
			);
	}

	public static function getById($id)
	{
		return self::model()->find(array('condition'=>"id=$id", 'limit'=>1));
	}

	/**
	 *	Получение типа сайта по его ID. Типы сайта: визитка, официальный, корпоративный.
	 * @param int $id
	 * @return string
	 */
	public static function getTypeById($id)
	{
		$type = 'Тип сайта не определён';
		$dta = '0000-00-00 00:00:00';
		$site = self::model()->find(array('condition'=>"id=$id", 'limit'=>1));
		foreach ($site->package as $package) {
		 foreach ($package->servPack as $service) {
			if ( ($service->service->parent_id == 1) and ($dta < $service->dt_beg) )
			{
				$type = $service->service->name;
				$dta = $service->dt_beg;
			}
		 }
		}
		return $type;
	}

	public static function getAllByClient($id)
	{
		return self::model()->findAll(array('condition'=>"client_id = $id"));
	}

	public static function FindAllByUrl($url)
	{
		return self::model()->findAll(array('condition'=>"url like '%$url%'"));
	}

	public static function getAll()
	{
		return self::model()->findAll();
	}

	public static function getByUrl($url)
	{
		return self::model()->find(array('condition'=>"url like '$url'", 'limit'=>1));
	}
}
?>