<?php

/**
 * Класс выполняет обработку функции AddService
 */
class GetPartnerBalanceAction extends ApiUserAction implements IApiGetAction {

	function run() {
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		$obToken = $this->getController()->getModule()->getApplicationTokens();
		if ($obToken->getUserId() == 0) {
			throw new CHttpException(403, 'Auth required', 403);
		}

		$obPartner = Partner::model()->findByPk($obToken->getUserId());
		$arResult['data']['total_bonus'] = $obPartner->getTotalBonus();
		$arResult['data']['total_withdrawed'] = $obPartner->getTotalWithdrawed();
		$arResult['data']['min_withdrawal'] = $obPartner->min_withdrawal;

		$arResult['result'] = 200;
		$arResult['resultText'] = 'ok';

		$this->getController()->render('json', array('data' => $arResult));
	}

}
