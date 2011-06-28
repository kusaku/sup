<?php
/*
	Класс таблицы
*/

class Serv2pack extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'serv2pack';
    }
    
    public function relations() 
    {
		return array(
			'package'=>array(self::BELONGS_TO,	'Package',	'pack_id'),
			'service'=>array(self::BELONGS_TO,	'Service',	'serv_id')
			);
	}
	
	/*
	 * Получить пакет по составному ключу
	 */
	public function getByIds($serv_id, $pack_id) {
         return self::model()->findByPk(array('serv_id'=>$serv_id, 'pack_id'=>$pack_id)); 
	}

	/*
	 * Удаляем все записи из Ser2Pack, относящиеся к указанному Заказу ($pack_id)
	 */
	public static function delByPack($pack_id)
    {
		if ($pack_id) return self::model()->deleteAll(array('condition'=>"pack_id = $pack_id"));
		else return FALSE;
    }

}
?>