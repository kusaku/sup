<?php
/**
 * Класс выполняет обрботку функции getServices
 */
class GetServicesAction extends ApiApplicationAction implements IApiGetAction {
	function run() {
		//Обработаем родительский вызов
		$this->_checkProtocolRequirements();
		
		$this->checkAccess();
		
		$obServices=new CFSServicesModel();
		$arList=false;
		if(isset($_GET['all']) && $_GET['all']=='Y') {
			if(isset($_GET['filterParent']))
				$arList=$obServices->getServices(intval($_GET['filterParent']),true);
			else
				$arList=$obServices->getAllServices(true);
		} else {
			if(isset($_GET['filterParent']))
				$arList=$obServices->getServices(intval($_GET['filterParent']));
			else
				$arList=$obServices->getAllServices();
		}

		if(is_array($arList)) 
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'list'=>array_values($arList),
			);
		else
			throw new ApiException(1,'no services'); 
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
