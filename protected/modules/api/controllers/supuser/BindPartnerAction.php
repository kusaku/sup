<?php

/**
 * Класс выполняет обработку функции AddService
 */
class BindPartnerAction extends ApiUserAction implements IApiPostAction {

	function run() {
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		$obToken = $this->getController()->getModule()->getApplicationTokens();
		if ($obToken->getUserId() == 0) {
			throw new CHttpException(403, 'Auth required', 403);
		}

		if(!isset($_POST['partner_id'])){
			throw new CHttpException(400,'Bad request',400);
		}

		$obPartner = Partner::model()->findByPk((int)$_POST['partner_id']);

		if($obPartner->bindToManager($obToken->getUserId())){
			$arResult=array(
				'result' => 200,
				'resultText' => 'ok',
			);
		} else {
			$arErrors = $obPartner->getError('manager_id');
			$arResult=array(
				'result'=>501,
				'resultText'=>'Function error',
				'error'=>1,
				'errorText'=>isset($arErrors[0]) ? $arErrors[0] : 'Other error',
			);
		}
		$this->getController()->render('json', array('data' => $arResult));
	}

}
