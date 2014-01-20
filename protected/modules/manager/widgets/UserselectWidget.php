<?php 
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 */

class UserselectWidget extends CWidget {
	public $index;
	public $selected;
	public $group_id;

	public function init() {
		if (Yii::app()->user->checkAccess('admin'))
			$this->group_id = 0;
	}

	public function run() {
		if ($this->group_id > 0)
			$arGroups = PeopleGroup::model()->findAllByPk($this->group_id);
		else
			$arGroups = PeopleGroup::model()->findAllByPk(array(
				1,2,3,4,5,8,9,11
			));
		$index = '';
		if ($this->index != '')
			$index = '['.$this->index.']';
		$this->render('Userselect', array(
			'groups'=>$arGroups,'index'=>$index,'selected'=>$this->selected
		));
	}
}
