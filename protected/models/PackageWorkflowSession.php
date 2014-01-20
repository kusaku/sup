<?php 
/**
 * Класс реализует ActiveRecord для записей таблицы PackageWorkflowSession
 * @property integer $package_id
 * @property string $step_id
 * @property string $data
 */
class PackageWorkflowSession extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_workflow_session';
	}
}
