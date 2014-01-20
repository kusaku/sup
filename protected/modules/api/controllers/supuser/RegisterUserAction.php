<?php
/**
 * Класс выполняет обрботку функции RegisterUser
 */
class RegisterUserAction extends ApiApplicationAction implements IApiPostAction{
	const PASSWORD_BASE_STRING='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()';

	protected $_toRegisterPartner = false;
	protected $_partnerType = '';
	protected $_registerOneself = false;

	function run() {
		$this->_checkProtocolRequirements();

		if(!isset($_REQUEST['login']) && !isset($_REQUEST['mail']))
			throw new CHttpException(400,'Bad request',400);

		$this->checkAccess();

		$arErrors=array();
		$obNewUser=new People;

		//Определяем пользователя, предполагая, что он Партнёр или менеджер
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		$iUserId=$obToken->getUserId();
		$bPartnerCreate=false;
		$bManagerCreate=false;
		if($iUserId>0) {
			$obUser=People::model()->findByPk($iUserId);
			if(!$obUser)
				throw new CHttpException(403,'Auth required',403);
			if($obToken->checkUserAccess('createPartnerUser'))
				if($obUser->isActivePartner())
					$bPartnerCreate=true;
				else
					throw new ApiException(6,'partner not active');
			elseif($obToken->checkUserAccess('createManagerUser'))
				$bManagerCreate=true;
			else
				throw new CHttpException(403,'Auth required',403);
		}

		if(isset($_REQUEST['login']))
			$obNewUser->login=$_REQUEST['login'];
		if(isset($_REQUEST['mail']))
			$obNewUser->mail=$_REQUEST['mail'];
		if($obNewUser->login=='')
			$obNewUser->login=$obNewUser->mail;
		if($obNewUser->login=='' && $obNewUser->mail=='')
			throw new ApiException(1,'login or mail required');
		if(isset($_REQUEST['password']) && $_REQUEST['password']!='')
			$obNewUser->psw=$_REQUEST['password'];
		elseif(isset($_REQUEST['genPass']) && $_REQUEST['genPass']==1)
			$obNewUser->psw=People::genPassword();
		//Проверка пароля на соответствие требованиям
		if($obNewUser->psw=='')
			throw new ApiException(2,'password required');
		if(strlen($obNewUser->psw)<6)
			throw new ApiException(3,'password wrong');
		//Убираем проверку режима, т.к. в новом протоколе она не нужна, а старому наплевать
		if(isset($_POST['data']['fio']))
			$obNewUser->fio=htmlspecialchars($_POST['data']['fio'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['state']))
			$obNewUser->state=htmlspecialchars($_POST['data']['state'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['phone']))
			$obNewUser->phone=htmlspecialchars($_POST['data']['phone'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['firm']))
			$obNewUser->firm=htmlspecialchars($_POST['data']['firm'],ENT_QUOTES,'utf-8',false);
		$obNewUser->regdate=date('Y-m-d H:i:s');
		//Проверка логина на соответствие правилам
		if($obNewUser->login=='') {
			$arErrors['login']['error']=2;
			$arErrors['login']['errorText'] = 'login is empty';
		}
		elseif(!preg_match('#^[^<>]+$#',$obNewUser->login)) {
			$arErrors['login']['error']=3;
			$arErrors['login']['errorText'] = 'login contains inapropriate symbols';
		}
		elseif(People::getByLogin($obNewUser->login)) {
			$arErrors['login']['error']=1;
			$arErrors['login']['errorText'] = 'There is user with this login';
		}
		//Проверка поля Email
		$obMailValidator=new CEmailValidator();
		if($obNewUser->mail=='') {
			$arErrors['mail']['error']=2;
			$arErrors['mail']['errorText'] = 'email is empty';
		}
		elseif(!$obMailValidator->validateValue($obNewUser->mail)) {
			$arErrors['mail']['error']=3;
			$arErrors['mail']['errorText'] = 'email is invalid';
		}
		elseif(People::model()->findByAttributes(array('mail'=>$obNewUser->mail))) {
			$arErrors['mail']['error']=1;
			$arErrors['mail']['errorText'] = 'There is user with this email';
		}

		if(count($arErrors)==0) {
			//Если нет никаких ошибок, то надо попробовать создать учётку
			$obAPIModule=Yii::app()->getModule('api');
			//хардкод для создания партнера
			//сама запись в модели Partner создается после сохранения модели People c параметром pgroup_id == 6
			$obNewUser->pgroup_id = $this->_toRegisterPartner ? 6 : $obAPIModule->getApplicationUser()->getParameter('new_people_pgroup_id');
			$sOpenPassword=$obNewUser->psw;
			$obNewUser->psw=People::hashPassword($obNewUser->psw);
			if($this->_toRegisterPartner){
				$obNewUser->partner_type = $this->_partnerType;
				$obJurPerson = new JurPersonReference('fiz');
				$obJurPerson->setScenario('fiz');
				if(!$obJurPerson->save()){
					throw new ApiException(6,'create jurperson error');
				}
				$obNewUser->jur_person_id = $obJurPerson->id;
			}
			if($obNewUser->save()) {
				//Устанавливаем авторизацию
				if(!$this->_toRegisterPartner){
					$obAPIModule->getUserAuth()->assign($obAPIModule->getApplicationUser()->getParameter('new_people_auth_item'),$obNewUser->id);
				} elseif(!$this->_registerOneself) {
					$obPartner = Partner::model()->findByPk($obNewUser->id);
					$obPartner->manager_id = $obUser->id;
					$obPartner->save();
				}
				if($bPartnerCreate) {
					//Создаём данные партнёра
					$obPartnerPeople=new PartnerPeople();
					$obPartnerPeople->id_client=$obNewUser->id;
					$obPartnerPeople->id_partner=$obUser->id;
					$obPartnerPeople->save();
					unset($_REQUEST['autoAuth']);
				}
				//Отправляем письмо и сохраняем его в лог
				try {
					if(!$this->_toRegisterPartner){
						Yii::app()->getComponent('documents')->createEmailRegister($obNewUser,$sOpenPassword)->send($obNewUser->mail,$obNewUser->fio);
					}
					if($this->_toRegisterPartner && $this->_registerOneself){
						Yii::app()->getComponent('documents')->createEmailPartnerRegister($obNewUser,$sOpenPassword)->send($obNewUser->mail,$obNewUser->mail);
						$sql = '
SELECT people.id
FROM api_auth_user_assignment
JOIN people ON api_auth_user_assignment.userid=people.id
WHERE api_auth_user_assignment.itemname = "PartnerManager"
';
						$command=Yii::app()->db->createCommand($sql);
						$rows=$command->query()->readAll();
						foreach ($rows as $arRow) {
							$obManager = People::model()->findByPk($arRow['id']);
							Yii::app()->getComponent('documents')->createEmailPartnerManagerRegister($obNewUser,$obManager)->send($obManager->mail,$obManager->fio);
						}
						if(!$rows){
							Yii::log('There isn\'t any partner-managers', CLogger::LEVEL_WARNING);
						}
					}
				} catch(exception $e) {
					Yii::log($e->getMessage(), CLogger::LEVEL_WARNING);
				}
				$arResult=array(
					'result'=>200,
					'resultText'=>'ok',
					'data'=>array(
						'id'=>$obNewUser->id,
						'info'=>'Учётная запись пользователя успешно создана'
					)
				);
				if(isset($_REQUEST['getPass']) && $_REQUEST['getPass']==1) {
					$arResult['data']['pass']=$sOpenPassword;
				}
				if(isset($_REQUEST['autoAuth']) && $_REQUEST['autoAuth']==1) {
					$obAPIModule->getApplicationTokens()->setUserId($obNewUser->id);
				}
			} else {
				throw new ApiException(5,'create error');
			}

		} else {
			$arResult=array(
				'result'=>501,
				'resultText'=>'Function error',
				'error'=>4,
				'errorText'=>'fields error',
				'data'=>$arErrors
			);
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}