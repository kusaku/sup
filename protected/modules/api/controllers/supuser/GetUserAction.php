<?php
/**
 * Класс выполняет обработку функции AddService
 */
class GetUserAction extends ApiUserActionDiffaccess implements IApiGetAction {
	/**
	 * Метод обеспечивает выполнение действие
	 */ 
	public function run() {
		$this->_checkProtocolRequirements();
		$this->checkAccess();
		
		if(isset($_GET['id']))
			$this->prepareAndCheckUser($_GET['id']);
		else
			$this->prepareAndCheckUser();
		
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
		if($this->obUser->manager && $obManager=People::model()->findByPk($this->obUser->manager->manager_id)) 
			$arResult['data']['manager']=array_merge(array(
				'name'=>$obManager->fio,
				'chat_link'=>'',
				'id'=>$obManager->id,
				'email'=>$obManager->mail,
			),$obManager->getAvatar());
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
