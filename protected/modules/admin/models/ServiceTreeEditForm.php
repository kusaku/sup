<?php
/**
 * Класс обеспечивает работу формы редактирования элемента дерева услуг
 */
class ServiceTreeEditForm extends CFormModel {
	public $id;
	public $parent_id;
	public $service_id;
	public $order;
	public $hide_on_site;

	public function rules() {
		return array(
			array(
				'id,parent_id,service_id,order,hide_on_site','safe'
			),
		);
	}

	public function attributeLabels() {
		return array(
			'id'=>'ID',
			'parent_id'=>'Родительская услуга',
			'service_id'=>'Услуга',
			'order'=>'Порядок на ветке',
			'hide_on_site'=>'Скрывать на сайтах',
		);
	}

	/**
	 * Метод выполняет запрос на сохранение данных пользователя на сервере
	 */
	public function save() {
		if($this->id>0) {
			$obModel=ServiceTree::model()->findByPk($this->id);
		} else {
			$obModel=new ServiceTree();
		}
		$obModel->id=$this->id;
		$obModel->parent_id=$this->parent_id;
		$obModel->service_id=$this->service_id;
		$obModel->order=$this->order;
		$obModel->hide_on_site=$this->hide_on_site==1?1:0;
		return $obModel->save();
	}

	public function load() {
		if($this->id>0) {
			$obModel=ServiceTree::model()->findByPk($this->id);
			$this->attributes=$obModel->attributes;
			return true;
		}
		return false;
	}
}
