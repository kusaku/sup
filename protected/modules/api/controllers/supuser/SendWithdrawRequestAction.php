<?php
/**
 * Класс выполняет обработку функции PackageSetProduct
 */
class SendWithdrawRequestAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		if(!isset($_POST['summ'])){
			throw new CHttpException(400,'Bad request',400);
		}

		$this->checkAccess();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0){
			throw new CHttpException(403,'Auth required',403);
		}

		//обрабатываем входные параметры
		$summ = (float)$_POST['summ'];


		$obWithdraw = new Withdraw('add');

		$obWithdraw->summ = $summ;
		$obWithdraw->id_partner = $obToken->getUserId();

		if(!$obWithdraw->save()){
			if($arError = $obWithdraw->getErrors()){
				throw new ApiException(1,$arError[0]);
			}
			throw new ApiException(1,'some error');
		}

		foreach (People::getByFilter('topmanagers') as $topmanager) {
			$event = new Calendar();
			$event->people_id = $topmanager->id;
			$event->date = date('Y-m-d 00:00:00');
			$event->message = 'Партнер #' . $obToken->getUserId() . ' создал <a href="'.Yii::app()->createUrl('manager/withdraw').'" target="_blank">запрос на вывод средств</a>';
			$event->interval = 0;
			$event->status = 1;
			$event->save();
		}

		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
		);
		$this->getController()->renderPartial('/layouts/json',array('data'=>$arResult));
	}
}
