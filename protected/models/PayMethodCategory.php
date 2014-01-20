<?php 
/**
 * Класс реализует ActiveRecord для записей таблицы PayMethodCategory
 */
class PayMethodCategory extends CActiveRecord {

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public static function getRoot() {
		$obRoot=new PayMethodCategory();
		$obRoot->id=0;
		$obRoot->title='Root';
		$obRoot->setIsNewRecord(false);
		return $obRoot;
	}
	
	public function tableName() {
		return 'pay_method_category';
	}
	
	/**
	 * Метод позволяет получить часть дерева способов оплаты
	 */
	public function getSubTree() {
		$arResult=array(
			'item'=>$this,
			'children'=>array(),
			'methods'=>$this->methods
		);
		$arChildren=$this->children;
		if(count($arChildren)>0) {
			foreach($arChildren as $obCategory) {
				$arResult['children'][]=$obCategory->getSubTree();
			}
		}
		return $arResult;
	}
	
	/**
	 * Метод позволяет получить всё дерево способов оплаты
	 * TODO Сделать оптимизацию
	 */
	public function getFullTree() {
		return self::getRoot()->getSubTree();
	}
	
	public function relations() {
		return array(
			'methods'=>array(
				self::HAS_MANY,
				'PayMethod',
				'category_id',
				'condition'=>'methods.active=1',
				'order'=>'methods.order ASC'
			),
			'children'=>array(
				self::HAS_MANY,
				'PayMethodCategory',
				'parent_id',
				'condition'=>' active=1 ',
				'order'=>'children.order ASC'
			),
		);
	}
}
