<?php
/**
 * Класс выполняет поиск и обработку данных по списку продуктов и услуг
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 12.05.12
 */
class CFSServicesModel extends CModel
{
	public function __construct() {
	}

	public function attributeNames() {
		return array();
	}

	/**
	 * Метод выполняет поиск сервисов указанного продукта
	 */
	public function getServices($iProductId,$bReallyAll=false) {
		return $this->_getServices($iProductId,$bReallyAll);
	}

	/**
	 * Метод выполняет поиск сервисов указанного продукта
	 * @todo убрать или зарефакторить этод метод, так как он является грубым хаком
	 */
	public function getServicesSup() {

		$obCriteria = new CDbCriteria();
		$obCriteria->scopes = array('actual');
		$obCriteria->condition = 't.parent_id <> 0';
		$obCriteria->with = array(
			'descriptions',
			'parent',
		);
		// hack - site products will be first
		$obCriteria->order = "t.parent_id ASC";

		$arProduct = array();
		foreach(Service::model()->findAll($obCriteria) as $obLeaf){
			$arLeaf=array(
				'id'=>$obLeaf->id,
				'parent_id'=>$obLeaf->parent_id,
				'name'=>$obLeaf->name,
				'full_name'=>$obLeaf->name,
				'description'=>$obLeaf->descr,
				'price'=>$obLeaf->price,
				'discount'=>$obLeaf->discount,
				'duration'=>$obLeaf->duration,
				'sort_order'=>$obLeaf->sort_order,
				'exclusive'=>$obLeaf->parent['exclusive'],
				'icon'=>'',
			);
			if($arDescriptions=$obLeaf->descriptions) {
				$arLeaf['full_name']=$arDescriptions[0]->title;
				$arLeaf['description']=$arDescriptions[0]->description;
				$arLeaf['link']=$arDescriptions[0]->link;
				$arLeaf['icon']=$arDescriptions[0]->icon;
			}
			$arProduct[]=$arLeaf;
		}

		return $arProduct;
	}

	/**
	 * Метод загружает продукты и услуги из БД
	 */
	private function _getServices($iProductId=0,$bReallyAll=false) {
		$arFilter=array('parent_id'=>0);
		if($iProductId>0) $arFilter['service_id']=$iProductId;
		if(!$bReallyAll) {
			$arFilter['hide_on_site']=0;
		}
		$arServicesRoot=ServiceTree::model()->findAllByAttributes($arFilter);
		$arResult=array();
		foreach($arServicesRoot as $obServiceBranch) {
			if($obService=$obServiceBranch->service) {
				$arRow=array(
					'id'=>$obService->id,
					'parent_id'=>$obService->id,
					'name'=>$obService->name,
					'descr'=>$obService->descr,
					'price'=>round($obService->price,2),
					'discount'=>$obService->discount,
					'duration'=>$obService->duration,
					'sort_order'=>$obService->sort_order,
					'icon'=>''
				);
				if($arDescriptions=$obService->descriptions) {
					$arRow['full_name']=$arDescriptions[0]->title;
					$arRow['description']=$arDescriptions[0]->description;
					$arRow['link']=$arDescriptions[0]->link;
					$arRow['icon']=$arDescriptions[0]->icon;
				}
				if($arChildren=$obServiceBranch->childs) {
					$arRow['services']=array();
					foreach($arChildren as $obServiceLeaf) {
						if(!(!$bReallyAll && $obServiceLeaf->hide_on_site==1) && $obLeaf=$obServiceLeaf->service) {
							$arLeaf=array(
								'id'=>$obLeaf->id,
								'parent_id'=>$obLeaf->id,
								'name'=>$obLeaf->name,
								'full_name'=>$obLeaf->name,
								'description'=>$obLeaf->descr,
								'price'=>round($obLeaf->price,2),
								'discount'=>$obLeaf->discount,
								'sort_order'=>$obServiceLeaf->order,
								'icon'=>''
							);
							if($arDescriptions=$obLeaf->descriptions) {
								$arLeaf['full_name']=$arDescriptions[0]->title;
								$arLeaf['description']=$arDescriptions[0]->description;
								$arLeaf['link']=$arDescriptions[0]->link;
								$arLeaf['icon']=$arDescriptions[0]->icon;
								$arLeaf['category']=$arDescriptions[0]->category;
							}
							$arRow['services'][]=$arLeaf;
						}
					}
				}
				$arResult[]=$arRow;
			}
		}
		return $arResult;
	}

	/**
	 * Метод выполняет поиск и возврат всех продуктов и услуг доступных на сайте
	 * На данный момент просто заглушка оформляющая данные из БД в другом формате
	 */
	public function getAllServices($bReallyAll=false) {
		return $this->_getServices(0,$bReallyAll);
	}
}