<?php
class PartnerDefaultSettings extends CFormModel {
	public $min_withdrawal;
	public $partner_percent;
	public $consultant_percent;

	public function init() {
		$this->attributes = array_intersect_key(Setting::get(__CLASS__),array_flip($this->getSafeAttributeNames()));
	}

	public function rules() {
		return array(
			array('min_withdrawal','numerical','integerOnly'=>true,'min'=>100),
			array('partner_percent','numerical','integerOnly'=>true,'min'=>0,'max'=>20),
			array('consultant_percent','numerical','integerOnly'=>true,'min'=>0,'max'=>20),
		);
	}

	public function attributeLabels() {
		return array(
			'min_withdrawal'=>Yii::t('partner', 'Minimal withdrawal'),
			'partner_percent'=>Yii::t('partner', 'Partner\'s percent'),
			'consultant_percent'=>Yii::t('partner', 'Consultant\'s percent'),
		);
	}

	public function save(){
		if($this->validate()){
			Setting::set(__CLASS__, $this->attributes);
			return true;
		}
		return false;
	}
}
