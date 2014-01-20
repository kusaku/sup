<?php
/**
 * Класс выполняет обработку функции GetCommercialAgreement
 * @todo Выяснить зачем нужна эта функция (действие)
 */
class GetCommercialAgreementAction extends ApiUserAction {
	function run() {
		$this->checkAccess();
		throw new CHttpException(503,'Not implemented',503);
	}
}
