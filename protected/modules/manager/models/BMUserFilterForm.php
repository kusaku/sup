<?php

class BMUserFilterForm extends CFormModel {
	public $email;
	public $name;
	public $realname;
	public $disabled;
	public $superuser;
	public $id;
	public $clear;

	private $bIsEmpty;

	public function attributeNames() {
		return array('email','name','realname','disabled','superuser','id','clear');
	}

	public function attributeLabels() {
		return array(
			'email'=>'Email',
			'name'=>'Логин',
			'realname'=>'ФИО',
			'disabled'=>'Неактивен',
			'superuser'=>'Администратор',
			'id'=>'ID',
			'clear'=>'Очистить фильтр'
		);
	}

	public function rules() {
		return array(
			array('email,name,realname,disabled,superuser,id,clear','safe')
		);
	}

	public function init() {
		$this->bIsEmpty=true;
		$this->clear=0;
	}

	public function isActive() {
		return !$this->bIsEmpty;
	}

	public function getFilter() {
		$arResult=array();
		$this->bIsEmpty=true;
		foreach($this->getAttributes() as $sField=>$sValue) {
			if($sValue!='') {
				$arResult[$sField]=$sValue;
				if($sField!='clear')
					$this->bIsEmpty=false;
			}
		}
		if($this->clear==1) {
			$arResult['clear']=1;
		}
		return $arResult;
	}
}