<?php 
/**
 * Класс реализует ActiveRecord для записей таблицы PackageWorkflowStepsTree
 */
class PackageWizzardMenu extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_wizzard_menu';
	}
	
	/**
	 * ограничение области запроса и порядка
	 * @return array
	 */
	public function scopes() {
		return array(
			'menu'=>array(
				'order'=>'`order` ASC'
			),
		);
	}
}
