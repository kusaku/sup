<?php 
/**
 * контроллер для таблицы people
 * пользоватли
 * клиенты
 * партнёты
 */
class PeopleController extends Controller {

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
				'allow','actions'=>array(
					'index','view','save','search','card','jur_reference','partnercard','contacts','contactsEdit','contactsDelete','newpassword'
				),'roles'=>array(
					'admin','moder','topmanager','manager','master'
				)
			),array(
				'allow','actions'=>array(
					'index','view','search','card'
				),'roles'=>array(
					'marketolog'
				)
			),array(
				'deny','users'=>array(
					'*'
				)
			)
		);
	}
	
	/**
	 * Метод обеспечивает вывод списка пользователей для редактирования
	 */
	public function actionIndex() {
		$this->renderPartial('/snippets/users');
	}
	
	/**
	 * форма редактирования человека
	 * @param object $id [optional]
	 */
	public function actionView($id = false, $parent = 0) {
		// если не указан id пользователя, создаем нового
		$obModel=new PeopleEditForm();
		if($id && $id>0) {
			if($obUser=People::model()->findByPk($id)) {
				$obModel->setScenario('safe');
				$obModel->attributes=$obUser->attributes;
				$obModel->fillAttributes($obUser);
			} else {
				throw new CHttpException(404,'People not found');
			}
		} else {
			$obModel->pgroup_id=7;
			$obModel->notice_email=1;
		}
		$this->renderPartial('view', array('model'=>$obModel));
	}
	
	/**
	 * обработчик формы редактирования человека
	 */
	public function actionSave() {
		$obModel=new PeopleEditForm();
		if(Yii::app()->getRequest()->isPostRequest && isset($_POST['PeopleEditForm'])) {
			$obModel->setScenario('form');
			$obModel->attributes=$_POST['PeopleEditForm'];
			$obModel->arAttributes=$_POST['attr'];
			$arResult=array();
			if($obModel->validate() && $obModel->save()) {
				// надо изменить текущуу сессию
				$obPeople=People::model()->findByPk($obModel->id);
				if (Yii::app()->user->id == $obPeople->id) {
					Yii::app()->user->setState('group_id', $obPeople->pgroup_id);
					Yii::app()->user->setState('login', $obPeople->login);
					Yii::app()->user->setState('fio', $obPeople->fio);
					Yii::app()->user->setState('mail', $obPeople->mail);
					Yii::app()->user->setState('rmToken', $obPeople->rm_token);
				}
				$arResult['save']='ok';
			}
			$arResult['model']=$obModel;
			$this->renderPartial('view', $arResult);
		} else {
			throw new CHttpException(401,'Wrong request');
		}
	}
	
	/**
	 * Метод выполняет глобальный поиск клиентов по переданным параметрам
	 */

	public function actionSearch($search = NULL) {
	
		isset($search) or $search = Yii::app()->request->getParam('term');
		
		$people = People::search($search, 0, 10);
		
		if (Yii::app()->request->isAjaxRequest) {
			echo json_encode($people);
		} else {
			var_dump($people);
		}
	}
	
	/**
	 * Метод обеспечивает вывод подробной карточки пользователя
	 * @param $id - ID пользователя
	 */

	public function actionCard($id = false) {
		if ($id) {
			$this->renderPartial('card', array(
				'client_id'=>Yii::app()->request->getParam('id')
			));
		}
	}

	public function actionPartnerCard($id) {
		$obModel = new PartnerEditForm();
		$obUser = People::model()->findByPk($id);
		$obModel->attributes = $obUser->partner_data->attributes;
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['PartnerEditForm'])) {
			$obModel->attributes = $_POST['PartnerEditForm'];
			if ($obModel->validate() && $obModel->save()) {

			}
		}
		$obLog = PartnerStatusLog::model();
		$arItems = $obLog->bydate()->findAllByAttributes(array(
			'partner_id'=>$id
		));
		$this->renderPartial('partnercard', array(
			'user'=>$obUser,'model'=>$obModel,'log'=>$arItems
		));
	}

	/**
	 * Метод отображает подробную информацию о юридическом лице связанном с пользователем
	 * @param numeric $id - номер пользователя, чьи реквизиты необходимо отобразить или отредактировать
	 *
	 * @throws CHttpException
	 * @return void
	 */
	public function actionJur_reference($id) {
		/**
		 * @var People $obUser
		 */
		if($obUser = People::model()->findByPk($id)) {
			$obForm=new JurPersonReferenceForm();
			$obForm->user_id=$obUser->id;
			if ($obUser->jur_person) {
				$obForm->setScenario('safe');
				$obForm->attributes=$obUser->jur_person->attributes;
			} else {
				$obForm->id=0;
				$obForm->type='ltd';
				$obForm->internal=0;
			}
			$arResult=array();
			try {
				if($obForm->internal==0 && Yii::app()->getRequest()->isPostRequest && isset($_POST['JurPersonReferenceForm'])) {
					if($_POST['JurPersonReferenceForm']['type']=='ltd') {
						$obForm->setScenario('ltd');
					} elseif($_POST['JurPersonReferenceForm']['type']=='ip') {
						$obForm->setScenario('ip');
					} else {
						$obForm->addError('type',Yii::t('rekvizform','Wrong type value'));
						throw new CException('Wrong type value');
					}
					$obForm->attributes=$_POST['JurPersonReferenceForm'];
					if($obForm->validate() && $obForm->save()) {
						$arResult['save']='ok';
					}

				}
			} catch (CException $e) {
				$arResult['error']=$e->__toString();
			}
			$arResult['model']=$obForm;
			$arResult['user']=$obUser;
			$this->renderPartial('jur_reference_page', $arResult);
		} else {
			throw new CHttpException(404, 'Client not found');
		}
	}

	/**
	 * Метод обеспечивает вывод окна с контактами пользователя
	 * @param $id
	 */
	public function actionContacts($id,$contact_id=0) {
		if($obPeople=People::getById($id)) {
			if(is_array($obPeople->contacts) && count($obPeople->contacts)>0) {
				if($contact_id>0) {
					$model=PeopleContacts::model()->findByAttributes(array('id'=>$contact_id,'people_id'=>$obPeople->id));
					if(is_null($model)) {
						throw new CHttpException(404, 'Client not found');
					}
				} elseif($contact_id<0) {
					$model=new PeopleContacts();
					$model->people_id=$obPeople->id;
				} else {
					$arContacts=$obPeople->contacts;
					$model=current($arContacts);
				}
			} else {
				$model=new PeopleContacts();
				$model->people_id=$obPeople->id;
			}
			$this->renderPartial('contacts',array('client'=>$obPeople,'model'=>$model));
		} else {
			throw new CHttpException(404, 'Client not found');
		}
	}

	/**
	 * Метод обеспечивает вывод формы редактирования определённого контакта пользователя
	 */
	public function actionContactsEdit($id,$contact_id=0) {
		if(Yii::app()->request->isPostRequest && isset($_POST['PeopleContacts'])) {
			if(isset($_POST['PeopleContacts']['id']) && $_POST['PeopleContacts']['id']>0) {
				$obModel=PeopleContacts::model()->findByPk($_POST['PeopleContacts']['id']);
				if(is_null($obModel)) {
					throw new CHttpException(404, 'Client contacts not found');
				}
				if($obModel->people_id!=$_POST['PeopleContacts']['people_id']) {
					throw new CHttpException(404, 'Wrong data state');
				}
				$obModel->setScenario('update');
			} else {
				$obModel=new PeopleContacts('form');
				$obPeople=People::model()->findByPk($_POST['PeopleContacts']['people_id']);
				if(is_null($obPeople)) {
					throw new CHttpException(404, 'Client not found');
				}
				$obModel->people_id=$obPeople->id;
			}
			$obModel->attributes=$_POST['PeopleContacts'];
			$obPeople=$obModel->people;
			if(is_null($obPeople)) {
				throw new CHttpException(404, 'Client not found');
			}
			if($obModel->validate() && $obModel->save()) {
				$obModel->refresh();
			}
			$this->renderPartial('contacts',array('client'=>$obPeople,'model'=>$obModel));
		} else {
			throw new CHttpException(400, 'Wrong request');
		}
	}

	/**
	 * Метод обеспечивает удаление контакта определённого пользователя
	 * @param $id
	 *
	 * @throws CHttpException
	 * @return void
	 */
	public function actionContactsDelete($id) {
		if(Yii::app()->request->isPostRequest && isset($_POST['contact_id'])) {
			if(isset($_POST['contact_id']) && $_POST['contact_id']>0) {
				$obModel=PeopleContacts::model()->findByPk($_POST['contact_id']);
				if(is_null($obModel)) {
					throw new CHttpException(404, 'Client contacts not found');
				}
				if($obModel->people_id!=$id) {
					throw new CHttpException(404, 'Wrong data state');
				}
				$obPeople=$obModel->people;
				$obModel->delete();
				if(is_array($obPeople->contacts) && count($obPeople->contacts)>0) {
					$arContacts=$obPeople->contacts;
					$obModel=current($arContacts);
				} else {
					$obModel=new PeopleContacts();
					$obModel->people_id=$obPeople->id;
				}
				$this->renderPartial('contacts',array('client'=>$obPeople,'model'=>$obModel));
			} else {
				throw new CHttpException(400, 'Wrong request');
			}
		} else {
			throw new CHttpException(400, 'Wrong request');
		}
	}

	/**
	 * @param $id
	 */
	public function actionNewpassword($id) {
		$obModel=new PeoplePasswordForm();
		if(Yii::app()->request->isPostRequest && isset($_POST['PeoplePasswordForm'])) {
			$obModel->attributes=$_POST['PeoplePasswordForm'];
			if($obPeople=People::getById($obModel->id)) {
				$arResult=array(
					'model'=>$obModel,'people'=>$obPeople
				);
				$arResult['save']=$obModel->save()?'ok':'';
				$this->renderPartial('password',$arResult);
			} else {
				throw new CHttpException(404,'People not found');
			}
		} else {
			if($obPeople=People::getById($id)) {
				$obModel->id=$obPeople->id;
				$obModel->notice_email=1;
				$this->renderPartial('password',array('model'=>$obModel,'people'=>$obPeople));
			} else {
				throw new CHttpException(404,'People not found');
			}
		}
	}
}
