<?php 
/**
 * Класс реализует ActiveRecord для записей таблицы PackageWorkflowSteps
 */
class PackageWorkflowStep extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_workflow_steps';
	}
	
	public function relations() {
		return array(
			'menu'=>array(
				self::HAS_ONE,
				'PackageWizzardMenu',
				'wizzard_menu_id'
			),
			'steps'=>array(
				self::HAS_MANY,
				'PackageWorkflowStepTree',
				'from_step_id'
			)
		);
	}
}
