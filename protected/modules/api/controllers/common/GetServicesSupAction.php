<?php
/**
 * Класс выполняет обрботку функции getServices
 */
class GetServicesSupAction extends ApiApplicationAction implements IApiGetAction {
	function run() {
		//Обработаем родительский вызов
		$this->_checkProtocolRequirements();
		
		$this->checkAccess();

		$obCriteria = new CDbCriteria();
		$obCriteria->scopes = "actual";
		$obCriteria->order = "t.parent_id ASC, t.sort_order ASC";

		$arModels = Service::model()->findAll($obCriteria);

		$arList = array();
		foreach($arModels as $obModel){
			$arList[$obModel->id]=$obModel->attributes;
		}

		if(!empty($arList)) {
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'list'=>$arList,
			);
		} else {
			throw new ApiException(1,'no services');
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
