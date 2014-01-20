<?php
/**
 * Класс выполняет обрботку функции UpdateMe
 */
class UpdateUserAction extends ApiUserActionDiffaccess implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		if(isset($_REQUEST['userId']))
			$this->prepareAndCheckUser(intval($_REQUEST['userId']));
		else
			$this->prepareAndCheckUser();

		$arErrors=array();
		if(isset($_POST['data']['fio']))
			$this->obUser->fio=htmlspecialchars($_POST['data']['fio'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['state']))
			$this->obUser->state=htmlspecialchars($_POST['data']['state'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['phone']))
			$this->obUser->phone=htmlspecialchars($_POST['data']['phone'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['firm']))
			$this->obUser->firm=htmlspecialchars($_POST['data']['firm'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['descr']))
			$this->obUser->descr=htmlspecialchars($_POST['data']['descr'],ENT_QUOTES,'utf-8',false);
		if(isset($_POST['data']['mail'])) {
			//Проверка поля Email
			$obMailValidator=new CEmailValidator();
			if($_POST['data']['mail']=='') {
				$arErrors['mail']['error']=2;
				$arErrors['mail']['errorText']='Email cant be empty';
			} elseif(!$obMailValidator->validateValue($_POST['data']['mail'])) {
				$arErrors['mail']['error']=3;
				$arErrors['mail']['errorText']='Email not valid';
			} else {
				$obPeople = People::model()->findByAttributes(array('mail'=>$_POST['data']['mail']));
				if($obPeople && !$obPeople->equals($this->obUser)) {
					$arErrors['mail']['error']=1;
					$arErrors['mail']['errorText']='Email already in use';
				} else {
					$this->obUser->mail=$_POST['data']['mail'];
				}
			}
		}
		if(isset($_POST['data']['pass'])) {
			//Проверка поля пароль
			if(strlen($_POST['data']['pass'])>5) {
				$this->obUser->psw=People::hashPassword($_POST['data']['pass']);
			} else {
				$arErrors['pass']['error']=1;
				$arErrors['pass']['errorText']='Password too short';
			}
		}

		if(count($arErrors)==0) {
			//Если нет никаких ошибок, то надо попробовать обновить учётку
			if($this->obUser->save()) {
				$arResult=array(
					'result'=>200,
					'resultText'=>'ok',
					'data'=>array(
						'mail'=>$this->obUser->mail,
						'login'=>$this->obUser->login,
						'id'=>$this->obUser->id,
						'fio'=>$this->obUser->fio,
						'state'=>$this->obUser->state,
						'phone'=>$this->obUser->phone,
						'firm'=>$this->obUser->firm,
						'descr'=>$this->obUser->descr,
						'regdate'=>$this->obUser->regdate,
					)
				);
			} else {
				throw new ApiException(3,'User update error');
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