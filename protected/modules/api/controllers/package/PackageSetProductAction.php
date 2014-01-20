<?php
/**
 * Класс выполняет обработку функции PackageSetProduct
 */
class PackageSetProductAction extends ApiUserAction implements IApiPostAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['packageID']) || !isset($_REQUEST['productID']))
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
		$obPackageModel=Package::model();
		$obPackage=$obPackageModel->findByAttributes($arFilter);
		if(!$obPackage)
			throw new ApiException(2,'no package');
		$obProduct=$obPackage->getProduct();
		if($obProduct)
			throw new ApiException(3,'already product');
		$obServices=new CFSServicesModel();
		//Если указан продукт, ищем его сервисы
		$iProductId=intval($_REQUEST['productID']);
		$arList=$obServices->getServices($iProductId);
		if(count($arList)==0)
			throw new ApiException(1,'wrong product id');
		$arProduct=array_pop($arList);
		if(!is_array($arProduct))
			throw new ApiException(1,'wrong product id');
		$obPackage->name=$arProduct['name'];
		$obProduct2Package=new Serv2pack();
		$obProduct2Package->serv_id=$iProductId;
		$obProduct2Package->pack_id=$obPackage->id;
		$obProduct2Package->quant=1;
		$obProduct2Package->price=$arProduct['price']; //TODO Выяснить стоит ли здесь использовать скидку
		$obProduct2Package->descr='';
		$obProduct2Package->master_id=0;
		$obProduct2Package->save();
		$obPackage->summ=$arProduct['price'];
		$obPackage->update(array('summ','name'));
		$arData=array(
			'id'=>$obPackage->id,
			'summ'=>$obPackage->summ,
			'product'=>array(
				'id'=>$arProduct['id'],
				'name'=>$arProduct['name'],
				'price'=>$arProduct['price'] 
			)
		);
		if($obPackage->site_id>0) {
			$arData['site']=array(
				'id'=>$obSite->id,
				'url'=>$obSite->url
			);
		}
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
			'data'=>$arData
		);
		$this->getController()->render('json',array('data'=>$arResult));
	}
}