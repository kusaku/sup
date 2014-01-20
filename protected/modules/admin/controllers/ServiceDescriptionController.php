<?php
class ServiceDescriptionController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */
	public function filters() {
		return array(
			'accessControl'
		);
	}

	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */
	public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
		return array(
			array(
				'allow', 'roles'=>array('admin')
			),
			array(
				'deny', 'users'=>array('*')
			)
		);
	}

	public function init() {
		parent::init();
		$this->breadcrumbs['Описание услуг']=array('serviceDescription/index');
	}

	public function actionIndex() {
		$obModel=Service::model();
		$arServices=$obModel->findAllByAttributes(array('parent_id'=>0));
		$this->render('index',array('models'=>$arServices));
	}

	public function actionEdit($id) {
		$obService=Service::model()->findByPk($id);
		if(!$obService)
			throw new CHttpException(404,'Service not found',404);
		$obModel=new ServiceDescriptionEditForm();
		$obModel->service_id=$obService->id;
		if($obDescription=$obService->description) {
			$obModel->attributes=$obDescription->attributes;
		}
		if(Yii::app()->getRequest()->isPostRequest && isset($_POST['ServiceDescriptionEditForm'])) {
			$obModel->attributes=$_POST['ServiceDescriptionEditForm'];
			if($obModel->validate() && $obModel->save()) {
				$this->redirect('/admin/serviceDescription');
			}
		}
		$this->render('edit',array('model'=>$obModel,'service'=>$obService));
	}
}
