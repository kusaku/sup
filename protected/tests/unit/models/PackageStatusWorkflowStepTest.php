<?php

class PackageStatusWorkflowStepTest extends CDbTestCase {
	public $fixtures=array(
		'package_status_workflow_step'=>':package_status_workflow_step',
	);

	public function testModel() {
		$obModel=PackageStatusWorkflowStep::model();
		$this->assertInstanceOf('PackageStatusWorkflowStep',$obModel);
	}

	public function testTableName() {
		$obModel=PackageStatusWorkflowStep::model();
		$this->assertEquals($obModel->tableName(),'package_status_workflow_step');
	}
}