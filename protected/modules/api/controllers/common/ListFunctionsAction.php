<?php
/**
 * Класс выполняет обработку функции getServices
 * @todo Перенести код логики в модели
 */
class ListFunctionsAction extends ApiAction implements IApiGetAction {
	function run() {
		$this->_checkProtocolRequirements();
		
		$obModule=Yii::app()->getModule('api');
		$obApplicationAuth=$obModule->getApplicationAuth();
		$obToken=$obModule->getApplicationTokens();
		$bDescr=isset($_GET['getDescription']) && $_GET['getDescription']==1;
		//Для не авторизованных приложений всегда чтонибудь да доступно
		$arOperations=$obApplicationAuth->getAuthItem('guest_application')->getChildren();
		if($obToken->getIsStarted()) {
			//Приложение авторизовано, теперь надо выбрать все операции и посмотреть авторизован ли пользователь в приложении
			$obApplicationUser=$obModule->getApplicationUser();
			$arApplicationOperations=$this->__processAuthArray($obApplicationAuth->getAuthItems(NULL,$obApplicationUser->getId()));
			if(count($arApplicationOperations)>0) {
				//Если есть хотябы одна операция доступная для приложения
				$arApplicationUserOperations=$this->__processAuthArray($obApplicationAuth->getAuthItem('user_application')->getChildren());
				if($obToken->getUserId()==0) {
					//Если пользователь не авторизован то необходимо выбрать действия доступные только для пользователей и исключить
					//их из списка действий доступных приложению
					$arApplicationOperations=array_diff_key($arApplicationOperations, $arApplicationUserOperations);					
				} else {
					//Если пользователь авторизован, необходимо выбрать все действия доступные пользователю, пересечь их с действиями 
					//доступными только для пользователей, а затем добавить к действиям доступным приложениям
					$obUserAuth=$obModule->getUserAuth();
					$arUserOperations=$this->__processAuthArray($obUserAuth->getAuthItems(NULL,$obToken->getUserId()));
					$arApplicationUnavailableOperations=array_diff_key($arApplicationUserOperations,$arUserOperations);
					$arApplicationOperations=array_diff_key($arApplicationOperations,$arApplicationUnavailableOperations);
				}
			}
			$arOperations=array_merge($arOperations,$arApplicationOperations);
		}		
		$arList=$this->__prepareList($arOperations,$bDescr);
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
			'list'=>array_values($arList),
		); 
		$this->getController()->render('json',array('data'=>$arResult));
	}

	/**
	 * Метод обрабатывает массив уровней доступов и формирует из них
	 * массив доступов операций
	 */
	private function __processAuthArray(Array $arList) {
		$arTmpList=array();
		foreach($arList as $obAuthItem)
			$arTmpList=array_merge($arTmpList,$this->__traceAllOperations($obAuthItem));
		return $arTmpList;
	}

	/**
	 * Метод получает все операции привязанные к указанному объекту авторизации
	 */
	private function __traceAllOperations(CAuthItem $obAuthItem) {
		if($obAuthItem->type==CAuthItem::TYPE_OPERATION) return array($obAuthItem->name=>$obAuthItem);
		$arResult=array();
		$arChildren=$obAuthItem->getChildren();
		if(count($arChildren)>0) {
			foreach($arChildren as $sName=>$obItem) {
				if($obItem->type==CAuthItem::TYPE_OPERATION)
					$arResult[$sName]=$obItem;
				else
					$arResult=array_merge($arResult,$this->__traceAllOperations($obItem));
			}
		}
		return $arResult;
	}
	
	/**
	 * Метод подготавливает список авторизаций в требуемом формате
	 */
	private function __prepareList(array $arAuthItems,$bAddDescription=false) {
		$arList=array();
		foreach($arAuthItems as $sFunction=>$obAuthItem) {
			$arRow=array('action'=>$sFunction);
			if($bAddDescription)
				$arRow['description']=$obAuthItem->description;
			array_push($arList,$arRow);
		}
		return $arList;
	}
}
