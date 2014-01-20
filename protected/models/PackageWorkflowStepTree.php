<?php 
/**
 * Класс реализует ActiveRecord для записей таблицы PackageWorkflowStepsTree
 */
class PackageWorkflowStepTree extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_workflow_steps_tree';
	}
}
