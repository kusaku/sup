<?php
class PromocodeController extends Controller {

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

    public function actionIndex() {
		$obModel=new Infocode('search');
		$obModel->unsetAttributes();  // clear any default values
		if(isset($_GET['Infocode'])){
			$obModel->attributes=$_GET['Infocode'];
		}
		$this->render('index',array(
			'obModel'=>$obModel,
		));
    }

    public function actionEdit($id) {
		$obInfocode = Infocode::model()->findByPk($id);
        if(!$obInfocode){
            throw new CHttpException(404,'Infocode not found',404);
		}

		$obInfocode->scenario = 'edit';
		if (isset($_POST['Infocode'])) {
			$obInfocode->attributes = $_POST['Infocode'];
			if ($obInfocode->save())
				Message::setMessage(Message::SUCCESS, Yii::t('infocode','Infocode was successfully modified'));
				$this->redirect(array('index'));
		}

		$this->render('edit',array('model'=>$obInfocode));
    }

	public function actionAdd() {
		$obInfocode = new Infocode('add');
		if (isset($_POST['Infocode'])) {
			$obInfocode->attributes = $_POST['Infocode'];
			if ($obInfocode->save()){
				Message::setMessage(Message::SUCCESS, Yii::t('infocode','Infocode was successfully added'));
			}
		}

		$this->render('add',array('model'=>$obInfocode));
    }

}
