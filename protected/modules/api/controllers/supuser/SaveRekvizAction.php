<?php
/**
 * Класс выполняет обрботку функции SaveMyRekviz
 */
class SaveRekvizAction extends ApiUserActionDiffaccess implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		if($_SERVER['REQUEST_METHOD']!='POST' || !isset($_POST['data']))
			throw new CHttpException(400,'Bad request',400);

		$this->checkAccess();

		if(isset($_REQUEST['userId']))
			$this->prepareAndCheckUser($_REQUEST['userId']);
		else
			$this->prepareAndCheckUser();

		$obJurPerson=$this->obUser->jur_person;
		if(!$obJurPerson)
			$obJurPerson=new JurPersonReference();
		$obJurPerson->setScenario('ltd');
		if(isset($_POST['data']['type']))
			if($_POST['data']['type']=='ip')
				$obJurPerson->setScenario('ip');
			if($_POST['data']['type']=='fiz')
				$obJurPerson->setScenario('fiz');
		$obJurPerson->attributes = $_POST['data'];
		if($obJurPerson->validate()) {
			if($obJurPerson->internal==0) {
				if($obJurPerson->save()) {
					$this->obUser->jur_person_id=$obJurPerson->id;
					$this->obUser->update(array('jur_person_id'));
					//Уведомляем менеджера если у пользователя есть менеджер и пользователь внёс изменения самостоятельно
					if($this->obUser->manager && $this->obUser->id==$this->obToken->getUserId()) {
						$obNotify=new ManagerNotifier();
						$obNotify->log='[auto] Пользователь обновил реквизиты';
						$obNotify->calendar='[auto] Пользователь '.$this->obUser->mail.' ['.$this->obUser->id.'] обновил реквизиты';
						$obNotify->mail=$this->getController()->renderPartial('mail/rekviz',array(
							'user'=>$this->obUser,
							'jurPerson'=>$obJurPerson,
							'manager'=>People::getById($this->obUser->manager->manager_id)
						),true);
						$obNotify->manager_id=$this->obUser->manager->manager_id;
						$obNotify->client_id=$this->obUser->id;
						$obNotify->Send();
					}
					//Сохраним связные данные
					$obJurPerson->_saveAttributes();
					$arResult=array('result'=>'200','resultText'=>'ok');
				} else
					throw new ApiException(2,'user save error');
			} else
				throw new ApiException(1,'internal rekviz');
		} else {
			$arResult=array('result'=>'501','resultText'=>'Function error','error'=>3,'errorText'=>'field error','data'=>$obJurPerson->errors);
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
