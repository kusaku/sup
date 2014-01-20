<?php
/**
 * Класс реализует ActiveRecord для записей таблицы PackageStatusWorkflowStep
 */
class PackageStatusWorkflowStep extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_status_workflow_step';
	}
}
