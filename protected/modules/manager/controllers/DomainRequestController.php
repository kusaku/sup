<?php

class DomainRequestController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return array
	 */
	public function filters() {
		return array('accessControl');
	}

	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */
	public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner',
		// 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
		return array(
			array(
				'allow',
				'actions'=>array('index','form','BMrequest'),
				'roles'=>array('admin','moder','topmanager','manager','marketolog')
			),
			array(
				'deny',
				'users'=>array('*')
			)
		);
	}

	/**
	 * Метод выводит список заявок связанных с пользователем или заказом или и тем и другим
	 * отображать
	 */
	public function actionIndex() {
		if(!(isset($_REQUEST['packageId']) || isset($_REQUEST['userId']))) {
			throw new CHttpException(404,'Wrong request');
		}
		$arCriteria=array();
		$arResult=array();
		if(isset($_REQUEST['packageId'])) {
			$arResult['package']=Package::model()->findByPk($_REQUEST['packageId']);
			if(!$arResult['package']) {
				throw new CHttpException(404,'Wrong request, package not found');
			}
			$arCriteria['package_id']=$arResult['package']->id;
		}
		if(isset($_REQUEST['userId'])) {
			$arResult['client']=People::model()->findByPk($_REQUEST['userId']);
			if(!$arResult['client']) {
				throw new CHttpException(404,'Wrong request, client not found');
			}
			$arCriteria['client_id']=$arResult['client']->id;
		}
		if(isset($arResult['client']) && isset($arResult['package']) && $arResult['client']->id!=$arResult['package']->client_id) {
			throw new CHttpException(404,'Wrong request, package not belong to client found');
		}
		$obDomainRequestModel=DomainRequest::model();
		$arRequests=$obDomainRequestModel->findAllByAttributes($arCriteria);
		$arResult['requests']=$arRequests;
		$this->renderPartial('index',$arResult);
	}

	/**
	 * Метод выполняет генерацию и вывод формы просмотра/редактирования заявки на домен
	 * @throws CHttpException
	 */
	public function actionForm() {
		$obForm=new DomainRequestForm();
		$arResult=array();
		if(isset($_REQUEST['type'])) {
			if($_REQUEST['type']=='package') {
				$obPackage=Package::model()->findByPk(intval($_REQUEST['packageId']));
				if($obPackage) {
					$obForm->package_id=$obPackage->id;
					$obForm->client_id=$obPackage->client_id;
					$obForm->site_id=$obPackage->site_id;
					$obForm->status='new';
					$obForm->mode='company';
					$arResult['package']=$obPackage;
					$arResult['client']=$obPackage->client;
				} else {
					throw new CHttpException(404,'Package not found');
				}
			} elseif($_REQUEST['type']!='') {
				throw new CHttpException(404,'Unsupported type');
			}
		}
		if(isset($_REQUEST['requestId']) && $_REQUEST['requestId']>0) {
			$obForm->id=intval($_REQUEST['requestId']);
			if($obForm->load()) {
				$arResult['package']=Package::model()->findByPk($obForm->package_id);
				$arResult['client']=People::model()->findByPk($obForm->client_id);
			} else {
				throw new CHttpException(404,'Domain request not found');
			}
		}
		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['DomainRequestForm'])) {
			$obForm->setScenario('form');
			$obForm->attributes=$_POST['DomainRequestForm'];
			$obForm->setScenario('save_'.$obForm->mode);
			if($arResult['validateOk']=$obForm->validate()) {
				$obForm->date_change=date('Y-m-d H:i:s');
				$arResult['saveOk']=$obForm->save();
			} else {
				$arResult['errors']=$obForm->getErrors();
				$arResult['error']=1000;
			}
			if(Yii::app()->getRequest()->isAjaxRequest) {
				$arJsonAnswer=array(
					'form'=>$obForm->attributes,
					'validateOk'=>$arResult['validateOk'],
					'saveOk'=>isset($arResult['saveOk'])?$arResult['saveOk']:false
				);
				if(!isset($arResult['validateOk']) || $arResult['validateOk']==false) {
					$arJsonAnswer['error']=1000;
					$arJsonAnswer['errors']=$obForm->getErrors();
				}
				echo json_encode($arJsonAnswer);
				Yii::app()->end();
			}
		}
		$arResult['model']=$obForm;
		$this->renderPartial('form',$arResult);
	}

	/**
	 * Метод выполняет запрос на регистрацию домена в billManager
	 */
	public function actionBmrequest() {
		$obForm=new DomainRequestForm();
		$arResult=array();
		try {
			$obForm->id=intval($_REQUEST['id']);
			if(!$obForm->load()) {
				throw new CHttpException(404,'Request not found',404);
			}
			$obForm->setScenario('save_'.$obForm->mode);
			if(!$obForm->validate()) {
				$arResult['errors']=$obForm->getErrors();
				throw new CException('Wrong form fill',1000);
			}
			if($obForm->client_id==0) {
				if($obForm->package_id==0) {
					throw new CException('Unassigned domain request. Should have assignment to package or client.',1);
				} elseif($obPackage=Package::model()->findByPk($obForm->package_id)) {
					$obForm->client_id=$obPackage->client_id;
				} else {
					throw new CException('Package assigned to request not found.',2);
				}
			}
			/**
			 * @var $obClient People
			 */
			$obClient=People::model()->findByPk($obForm->client_id);
			if(!$obClient) {
				throw new CException('Client assigned to request not found',3);
			}
			/**
			 * @var $obConnection ISPConnection
			 */
			$obConnection=Yii::app()->getComponent('billManager');
			if($obClient->bm_user_data==NULL) {
				throw new CException('Assigned client not registered in Bill Manager.',4);
			}
			$obConnection->loginUser($obClient->bm_user_data->user->name);
			$obDomainContact=null;
			$obDomainContacts=$obConnection->createDomainContactModel();
			if($arContacts=$obDomainContacts->getList()) {
				foreach($arContacts as $obContact) {
					$obContact->load();
					if($obContact->ctype==$obForm->mode) {
						switch($obContact->ctype) {
							case 'person':
								//Если регистрация в заявке на частное лицо и в базе частное лицо, смотрим поля ФИО ру
								if($obContact->passport_series==$obForm->passport_series &&
								   $obContact->passport==$obForm->passport) {
									$obDomainContact=$obContact;
									break 2;
								}
							break;
							case 'company':
								if($obContact->inn==$obForm->inn && $obContact->kpp==$obForm->kpp) {
									$obDomainContact=$obContact;
									break 2;
								}
							break;
							case 'general':
								if($obContact->inn==$obForm->inn) {
									$obDomainContact=$obContact;
									break 2;
								}
							break;
						}
					}
				}
			}
			if($obDomainContact==null) {
				$obDomainContact=$obConnection->createDomainContactModel();
				$obDomainContact->setScenario('safe');
				$obDomainContact->attributes=$obForm->attributes;
				$obDomainContact->ctype=$obForm->mode;
				switch($obDomainContact->ctype) {
					case 'company':
						$obDomainContact->name=$obForm->company_ru.'_('.$obForm->company.')';
						break;
					case 'person':
						$obDomainContact->name=$obForm->firstname_ru.' '.$obForm->lastname_ru.'_('.$obForm->firstname.' '.$obForm->lastname.')';
						break;
					case 'general':
						$obDomainContact->name=$obForm->company_ru.'_('.$obForm->company.')';
						break;
				}
				if(!($obDomainContact->validate() && $obDomainContact->save())) {
					$arResult['errors']=$obDomainContact->getErrors();
					throw new CException('Wrong fields in domain data',1000);
				}
			}
			die();
			$obRequest=$obConnection->createDomainRegisterModel();
			//$obRequest->setDomainZone()
			$obRequest->attributes=$obForm->attributes;
			if($obRequest->validate()) {
				try {
					$obRequest->register();
				} catch (ISPAnswerException $e) {
					$arAnswer['error']=2;
					$arAnswer['errorText']=$e->getMessage();
					$arAnswer['errorCode']=$e->getCode();
				}
			} else {
				$arResult['errors']=$obRequest->getErrors();
			}
			die();

			/*$package = Package::model()->findByPk($package_id);

			// клиент и сайт
			$client = $package->client;
			$username = isset($client->attr['username']) ? $client->attr['username']->values[0]->value : '';

			// от имени менеджера
			$bmr = new BMRequest(true);
			$result = $bmr->getAuthKey(array(
			                           'username'=>$username
			                           ));

			// при ошибке - выход
			if (!$result['success']) {
				$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
				print(json_encode($result));
				return;
			}

			// от имени пользователя
			$bmr = new BMRequest();
			$result = $bmr->login(array(
			                      'username'=>$username,'key'=>$result['key']
			                      ));

			// при ошибке авторизации - выход
			if (!$result['success']) {
				$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
				print(json_encode($result));
				return;
			}

			// опрос списка контактов домена
			$result = $bmr->listItems('domaincontact');

			// если есть контакты домена, используем последний, иначе - создаем новый
			if (! empty($result['cdata'])) {
				$lastdc = array_pop($result['cdata']);
				$lastdcid = $lastdc['id'];$serv2pack = Serv2pack::getByIds($service_id, $package_id);
			} else {
				$data = array(
				);
				foreach ($client->attr as $name=>$attr) {
					$data[$name] = $attr->values[0]->value;
				}
				$data['name'] = "AutoContact from SUP";

				$result = $bmr->saveItem($data, 'domaincontact');

				// при ошибке создания - выход
				if (!$result['success']) {
					$result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
					print(json_encode($result));
					return;
				}

				$lastdcid = $result['cdata']['domaincontact.id'];
			}

			// данные для конкретного тарифа - с периодом и дополнениями
			$prices = array(
				84=>array(
					'price'=>54,
					'period'=>30,
					'autoprolong'=>30
				),
				85=>array(
					'price'=>55,
					'period'=>34,
					'autoprolong'=>30
				),
				86=>array(
					'price'=>56,
					'period'=>38,
					'autoprolong'=>30
				),
				87=>array(
					'price'=>57,
					'period'=>42,
					'autoprolong'=>30
				),
				88=>array(
					'price'=>38,
					'period'=>16,
					'autoprolong'=>30
				),
				89=>array(
					'price'=>58,
					'period'=>48,
					'autoprolong'=>30
				),
				90=>array(
					'price'=>59,
					'period'=>52,
					'autoprolong'=>30
				),
				91=>array(
					'price'=>60,
					'period'=>56,
					'autoprolong'=>30
				),
				92=>array(
					'price'=>61,
					'period'=>60,
					'autoprolong'=>30
				),
				93=>array(
					'price'=>62,
					'period'=>64,
					'autoprolong'=>30
				),
				94=>array(
					'price'=>63,
					'period'=>68,
					'autoprolong'=>30
				),
				95=>array(
					'price'=>64,
					'period'=>72,
					'autoprolong'=>30
				)
			);

			// использование промокода
			if ($use_promo) {
				$manager = People::model()->findByPk(Yii::app()->user->id);
				$promocode = isset($manager->attr['promocode']) ? $manager->attr['promocode']->values[0]->value : NULL;
			} else {
				$promocode = NULL;
			}

			$serv2pack = Serv2pack::getByIds($service_id, $package_id);

			$data = array_merge($prices[$service_id], array(
			                                          'customer'=>$lastdcid,'subjnic'=>$lastdcid,'domain'=>$serv2pack->descr,'elid'=>$lastdcid,'customertype'=>'person','promocode'=>$promocode
			                                          ));

			$result = $bmr->orderDomain($data);

			// при успехе
			if ($result['success']) {
				// обновим дату создания и истечения услуги
				$serv2pack->dt_beg = date('Y-m-d H:i:s');
				$serv2pack->dt_end = date('Y-m-d H:i:s', strtotime('now +12 month'));
				$serv2pack->save();
				// добавим напоминалку
				$event = new Calendar();
				$event->people_id = Yii::app()->user->id;
				$event->date = date('Y-m-d', strtotime('now +12 month'));
				$event->message = "У $client->fio заканчивается срок регистрация домена";
				$event->status = 1;
				$event->save();
			}

			// вывод результатов
			!$result['success'] and $result['code'] == 4 and $result['msg'] = Attributes::getByType($result['val'])->name;
			print(json_encode($result));*/
		} catch(Exception $e) {
			$arResult['error']=$e->getCode();
			$arResult['errorText']=$e->getMessage();
		}
		echo json_encode($arResult);
		Yii::app()->end();
	}
}
