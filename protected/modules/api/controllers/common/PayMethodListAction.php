<?php
/**
 * Класс выполняет обрботку функции getServices
 */
class PayMethodListAction extends ApiApplicationAction implements IApiGetAction {
	/**
	 * Метод выполняет преобразование дерева оплаты в список
	 */
	private function __parseTree($arTree) {
		$arResult=array(
			'id'=>$arTree['item']->id,
			'title'=>$arTree['item']->title,
			'text_ident'=>$arTree['item']->text_ident,
		);
		if(isset($arTree['children'])) {
			foreach($arTree['children'] as $arCategory) {
				$arResult['children'][]=$this->__parseTree($arCategory);
			}
		}
		if(isset($arTree['methods']) && count($arTree['methods'])>0) {
			$arResult['methods']=array();
			foreach($arTree['methods'] as $obItem) {
				$arResult['methods'][]=array(
					'id'=>$obItem->id,
					'title'=>$obItem->title,
					'text_ident'=>$obItem->text_ident,
					'payer_type'=>$obItem->payer_type,
				);
			}
		}
		return $arResult;
	}
	
	function run() {
		$this->_checkProtocolRequirements();
		
		$this->checkAccess();

		if($_GET['onlyMethods']) {
			$obMethods=PayMethod::model();
			$arList=$obMethods->findAllByAttributes(array('active'=>1));
			if(is_array($arList)) {
				$arResult=array();
				foreach($arList as $obItem) {
					$arResult[]=array(
						'id'=>$obItem->id,
						'title'=>$obItem->title,
						'text_ident'=>$obItem->text_ident,
						'payer_type'=>$obItem->payer_type,
						'category_id'=>$obItem->category_id
					);
				}
				$arResult=array(
					'result'=>200,
					'resultText'=>'ok',
					'list'=>$arResult,
				);
			} else {
				throw new ApiException(1,'no paytypes'); 
			}
		} else {
			$obMethods=PayMethodCategory::model();
			$arTree=$obMethods->getFullTree();
			if(is_array($arTree)) {
				$arResult=array(
					'result'=>200,
					'resultText'=>'ok',
					'list'=>$this->__parseTree($arTree),
				);
			} else
				throw new ApiException(1,'no paytypes'); 
		}
		/*echo "<pre>";
		print_r($arResult);*/
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
