<?php
class ServiceTreeController extends Controller {
	/**
	 * Использовать фильтр прав доступа
	 * @return array
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
		$this->breadcrumbs['Дерево услуг']=array('serviceTree/index');
	}

	public function actionIndex() {
		$arRoot=ServiceTree::model()->findAllByAttributes(array('parent_id'=>0));
		$this->render('index',array('root'=>$arRoot));
	}

	public function actionUp($id) {
		if($obLeaf=ServiceTree::model()->findByPk($id)) {
			if($obPrevLeaf=$obLeaf->prevSibling()) {
				$tmp=$obLeaf->order;
				$obLeaf->order=$obPrevLeaf->order;
				$obPrevLeaf->order=$tmp;
				$obLeaf->save();
				$obPrevLeaf->save();
			}
		}
		$this->redirect($this->createUrl('serviceTree/index'));
	}

	public function actionDown($id) {
		if($obLeaf=ServiceTree::model()->findByPk($id)) {
			if($obNextLeaf=$obLeaf->nextSibling()) {
				$tmp=$obLeaf->order;
				$obLeaf->order=$obNextLeaf->order;
				$obNextLeaf->order=$tmp;
				$obLeaf->save();
				$obNextLeaf->save();
			}
		}
		$this->redirect($this->createUrl('serviceTree/index'));
	}

	public function actionEdit($id,$parent=0) {
		$obModel=new ServiceTreeEditForm();
		if($id>0) {
			$obModel->id=$id;
			if(!$obModel->load())
				throw new CHttpException(404,'Service tree leaf not found');
		} else {
			$obModel->parent_id=$parent;
			$sSQL='SELECT MAX(`order`) FROM service_tree WHERE parent_id='.$parent;
			$obModel->order=Yii::app()->db->createCommand($sSQL)->queryScalar()+1;
		}
		if(Yii::app()->getRequest()->isPostRequest && isset($_POST['ServiceTreeEditForm'])) {
			$obModel->attributes=$_POST['ServiceTreeEditForm'];
			if($obModel->validate() && $obModel->save()) {
				$this->redirect('/admin/serviceTree');
			}
		}
		$this->render('edit',array('model'=>$obModel));
	}

	public function actionDelete($id) {
		if($obLeaf=ServiceTree::model()->findByPk($id)) {
			$obLeaf->delete();
		}
		$this->redirect($this->createUrl('serviceTree/index'));
	}
}
