<?php

class workflowTest extends CDbTestCase {
	public $fixtures=array(
		'package_workflow_session'=>':package_workflow_session',
		'package_status_workflow_step'=>':package_status_workflow_step',
		'package_workflow_steps'=>':package_workflow_steps',
		'package_workflow_steps_tree'=>':package_workflow_steps_tree',
		'package_workflow'=>':package_workflow',
	);

	/**
	 * Метод тестирует правильность инциализации таблиц данных
	 */
	function testUpdate() {
		//Обновляем кэш после обновления БД
		Yii::app()->getDb()->getSchema()->refresh();
		//Инициализируем данные
		$obWorkflowSteps=PackageWorkflowStep::model();
		$obWorkflowStepsTree=PackageWorkflowStepTree::model();
		$obPackageWorkflow=PackageWorkflow::model();
		$obStatusWorkflowStep=PackageStatusWorkflowStep::model();
		$obWorkflowSession=PackageWorkflowSession::model();
		//Проверка существования шага ожидания
		$obStep=$obWorkflowSteps->findByPk(9);
		$this->assertInstanceOf('PackageWorkflowStep',$obStep);
		if($obStep) {
			$this->assertTrue($obStep->text_ident=='waiting');
		}
		//Проверка ветки дерева
		$obBranch=$obWorkflowStepsTree->findByAttributes(array('from_step_id'=>3,'to_step_id'=>14));
		$this->assertInstanceOf('PackageWorkflowStepTree',$obBranch);
		if($obBranch) {
			$this->assertTrue($obBranch->primaryKey==26);
		}
		//Проверка состояния заказа
		$obPack=$obPackageWorkflow->findByPk(1);
		$this->assertInstanceOf('PackageWorkflow',$obPack);
		if($obPack) {
			$this->assertTrue($obPack->step_id==9);
		}
		//Проверка перехода между режимами
		$obStatus2Step=$obStatusWorkflowStep->findByPk(3);
		$this->assertInstanceOf('PackageStatusWorkflowStep',$obStatus2Step);
		if($obStatus2Step) {
			$this->assertTrue($obStatus2Step->step_id==9);
		}
		//Проверка сессии
		$obSession=$obWorkflowSession->findByPk(array('package_id'=>3,'step_id'=>5));
		$this->assertInstanceOf('PackageWorkflowSession',$obSession);
		if($obSession) {
			$this->assertTrue($obSession->step_id==5);
		}
		/**
		 * @var $obUpdate FSDbUpdate
		 */
		$obUpdate=Yii::app()->getComponent('dbUpdate');
		$obUpdate->addFile(Yii::getPathOfAlias('application.data.updates.v1_2_1').'/workflow.sql');
		$obUpdate->up();
		//Обновляем кэш после обновления БД
		Yii::app()->getDb()->getSchema()->refresh();
		$obWorkflowSteps->refreshMetaData();
		$obWorkflowStepsTree->refreshMetaData();
		$obPackageWorkflow->refreshMetaData();
		$obStatusWorkflowStep->refreshMetaData();
		//Проверка обновления дерева
		$obStep=$obWorkflowSteps->findByPk('waiting');
		$this->assertInstanceOf('PackageWorkflowStep',$obStep);
		if($obStep) {
			$this->assertTrue($obStep->primaryKey=='waiting');
		}
		//Проверка обновления ветки
		$obBranch=$obWorkflowStepsTree->findByPk(array('from_step_id'=>'paytype','to_step_id'=>'robokassa_payment'));
		$this->assertInstanceOf('PackageWorkflowStepTree',$obBranch);
		if($obBranch) {
			$this->assertTrue($obBranch->comment=='Переход со способа оплаты на оплату в Robokassa');
		}
		//Проверка обновления состояния заказа
		$obPack=$obPackageWorkflow->findByPk(2);
		$this->assertInstanceOf('PackageWorkflow',$obPack);
		if($obPack) {
			$this->assertTrue($obPack->step_id=='select_product');
		}
		//Проверка перехода между режимами
		$obStatus2Step=$obStatusWorkflowStep->findByPk(1);
		$this->assertInstanceOf('PackageStatusWorkflowStep',$obStatus2Step);
		if($obStatus2Step) {
			$this->assertTrue($obStatus2Step->step_id=='ready');
		}
		//Проверка сессии
		$obSession=$obWorkflowSession->findByPk(array('package_id'=>3,'step_id'=>'fill_rekviz'));
		$this->assertInstanceOf('PackageWorkflowSession',$obSession);
		if($obSession) {
			$this->assertTrue($obSession->step_id=='fill_rekviz');
		}
	}
}