<?php
/**
 * Класс выполняет обрботку функции GetMyRekvizAction
 */
class GetRekvizAction extends ApiUserActionDiffaccess implements IApiGetAction {
	/**
	 * Метод выполняет поиск пользователя, проверку прав и возврат информации о реквизитах пользователя
	 */
	function run() {
		$this->_checkProtocolRequirements();
		$this->checkAccess();
		
		if(isset($_GET['userId']))
			$this->prepareAndCheckUser($_GET['userId']);
		else
			$this->prepareAndCheckUser();
		
		if($obRekviz=$this->obUser->jur_person) {
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$obRekviz->attributes
			);
		} else {
			throw new ApiException(3,'no rekviz');
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
