<?php
/**
 * Класс выполняет обработку функции PackageSetProduct
 */
class SetPartnerInfoAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		if(!(
			isset($_POST['partnerId'])
			&& isset($_POST['data'])
			&& (
				isset($_POST['data']['infocode'])
				|| isset($_POST['data']['status'])
				|| isset($_POST['data']['percent'])
				|| isset($_POST['data']['min_withdrawal'])
			)
		) ){
			throw new CHttpException(400,'Bad request',400);
		}

		$this->checkAccess();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0){
			throw new CHttpException(403,'Auth required',403);
		}

		if(!$obToken->hasRole('PartnerSuperManager')){
			unset($_POST['data']['percent']);
			unset($_POST['data']['min_withdrawal']);
		}

		//обрабатываем входные параметры
		$partnerId = intval($_POST['partnerId']);

		$sInfocode = '';
		if (isset($_POST['data']['infocode'])){
			$sInfocode = $_POST['data']['infocode'];
			unset($_POST['data']['infocode']);
		}

		if(!$obPartner = Partner::model()->with('infocode')->findByPk($partnerId)) {
			throw new ApiException(1,'partner not found');
		}
		$obPartner->setScenario('edit');

		$obPartner->attributes = $_POST['data'];

		if ($sInfocode !== ''){
			try {
				$obPartner->setInfocode($sInfocode);
			} catch (Exception $exc) {
				throw new ApiException(3, $exc->getMessage());
			}
		}

		if(!$obPartner->save()){
			if($sError = $obPartner->getErrors('min_withdrawal')){
				throw new ApiException(6,$sError[0]);
			}
			if($sError = $obPartner->getErrors('status')){
				throw new ApiException(5,$sError[0]);
			}
			if($sError = $obPartner->getErrors('percent')){
				throw new ApiException(4,$sError[0]);
			}
			if($sError = $obPartner->getErrors()){
				throw new ApiException(2,$sError[0]);
			}
		}

		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
		);
		$this->getController()->renderPartial('/layouts/json',array('data'=>$arResult));
	}
}