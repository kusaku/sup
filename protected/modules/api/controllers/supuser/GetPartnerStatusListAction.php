<?php
/**
 * Класс выполняет обработку функции AddService
 */
class GetPartnerStatusListAction extends ApiApplicationAction implements IApiGetAction {
	/**
	 * Метод обеспечивает выполнение действие
	 */
	public function run() {
		$this->_checkProtocolRequirements();
		$this->checkAccess();

		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
			'list'=>Partner::getStatuses(),
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
