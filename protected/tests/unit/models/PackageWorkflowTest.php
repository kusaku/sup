<?php

class PackageWorkflowTest extends CDbTestCase {
	public $fixtures=array(
		'package_workflow_session'=>':package_workflow_session',
		'package_workflow_steps'=>':package_workflow_steps',
		'package_workflow_steps_tree'=>':package_workflow_steps_tree',
		'package_workflow'=>':package_workflow',
	);

	public function testModel() {
		$obModel=PackageWorkflow::model();
		$this->assertInstanceOf('PackageWorkflow',$obModel);
	}

	public function testTableName() {
		$obModel=PackageWorkflow::model();
		$this->assertEquals($obModel->tableName(),'package_workflow');
	}

	public function testRelationStep() {
		//Обновляем кэш после обновления БД
		Yii::app()->getDb()->getSchema()->refresh();
		$obRecord=PackageWorkflow::model()->findByPk(1);
		$this->assertInstanceOf('PackageWorkflow',$obRecord);
		$obStep=$obRecord->step;
		$this->assertInstanceOf('PackageWorkflowStep',$obStep);
		$this->assertEquals('waiting',$obStep->text_ident);
	}

	public function testRelationPackage() {

	}

	public function testRelationSession() {
		//Обновляем кэш после обновления БД
		Yii::app()->getDb()->getSchema()->refresh();
		$obRecord=PackageWorkflow::model()->findByPk(3);
		$this->assertInstanceOf('PackageWorkflow',$obRecord);
		$obSession=$obRecord->session;
		$this->assertInstanceOf('PackageWorkflowSession',$obSession);
		$this->assertEquals($obRecord->step_id,$obSession->step_id);
	}

	public function testRelationSessions() {

	}
}