<?php
/**
 * Класс выполняет обработку функции PackageSetPromocode
 */
class PackageSetPromocodeAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();

		if(!isset($_REQUEST['packageID']) || !isset($_REQUEST['promocode']))
			throw new CHttpException(400,'Bad request',400);

		$this->checkAccess();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$arResult=array();
		$arFilter=array(
			'client_id'=>$obToken->getUserId(),
			'id'=>intval($_REQUEST['packageID'])
		);
		//Получаем модель
		/**
		 * @var Package $obPackage
		 */
		$obPackage=Package::model()->findByAttributes($arFilter);
		if(!$obPackage)
			throw new ApiException(1,'wrong package id');
		if($obPackage->promocode_id>0 && $obPackage->promocode->code!='0002')
			throw new ApiException(2,'package already have promocode');
		// ищем промокод
		$obPromocode = Promocode::model()->findByAttributes(array('code'=>$_REQUEST['promocode']));
		if(!$obPromocode) {
			// если не нашли - создаем новый
			$obPromocode = new Promocode();
			$obPromocode->code = $_REQUEST['promocode'];
			$obPromocode->save();
		}
		$obPackage->promocode_id = $obPromocode->primaryKey;
		$obPackage->save();
		$arData=array(
			'id'=>$obPackage->id,
			'promocode'=>$obPackage->promocode->code,
		);
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
			'data'=>$arData
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}