<?php
/**
 * Класс обеспечивает работу формы редактирования партнёрской информации
 */
class PartnerEditForm extends CFormModel {
	public $id;
	public $name;
	public $date_sign;
	public $status;
	public $agreement_num;
	public $manager_id;
	public $status_comment;

	public function rules() {
		return array(
			array(
				'id,name,date_sign,agreement_num,status_comment,manager_id','safe'
			),
			array(
				'status','in','range' => array_keys(Partner::getStatuses())
			),
		);
	}

	public function attributeLabels() {
		return array(
			'name'=>'Название партнёра',
			'date_sign'=>'Дата подписания договора',
			'status'=>'Статус партнёра',
			'agreement_num'=>'Номер договора',
			'status_comment'=>'Комментарий к изменению статуса',
			'manager_id'=>'Менеджер партнёра',
		);
	}

	/**
	 * Метод выполняет запрос на сохранение данных пользователя на сервере
	 */
	public function save() {
		$obPartner=Partner::model()->findByPk($this->id);
		if(!$obPartner)
			return false;
		$obPartner->name=$this->name;
		$obPartner->date_sign=$this->date_sign;
		$obPartner->status=$this->status;
		$obPartner->agreement_num=$this->agreement_num;
		$obPartner->sLogMessage=$this->status_comment;
		$obPartner->manager_id=$this->manager_id;
		return $obPartner->save();
	}

	/**
	 * Метод выполняет построение списка менеджеров
	 */
	public function getPeopleList() {
		return array_merge(array('0'=>' - Не указан -'),CHtml::listData(People::model()->findAllByAttributes(array('pgroup_id'=>array(1,2,3,4,12))),'id','fio','people_group.name'));
	}
}
