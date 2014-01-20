<?php
/**
 * Класс реализует ActiveRecord для записей таблицы PackageInvoice
 */
class PackageInvoiceRekvizJur extends CActiveRecord {
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'package_invoice_rekviz_jur';
	}

	public function relations() {
		return array('invoice'=> array(
				self::BELONGS_TO,
				'PackageInvoice',
				'package_invoice_id'
			));
	}

	public function rules() {
		return array(
			array(
				'title,inn,address,real_address,settlement_account,correspondent_account,bank_title,bank_bik,director_fio',
				'required'
			),
			array(
				'kpp,director_position,director_source',
				'required',
				'on'=>'ltd'
			),
			array(
				'egrip',
				'required',
				'on'=>'ip'
			),
			array(
				'type',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^ltd|ip$/'
			),
			array(
				'director_source',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^charter|warrant|order|protocol$/',
				'on'=>'ltd'
			),
			array(
				'inn',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^\d{10}(\d{2})?$/'
			),
			array(
				'settlement_account,correspondent_account',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^\d{20}?$/'
			),
			array(
				'bank_bik',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^\d{9}?$/'
			),
			array(
				'kpp',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^\d{9}?$/',
				'on'=>'ltd'
			),
			array(
				'director_source_info',
				'validatorSourceInfo',
				'on'=>'ltd'
			),
			array(
				'egrip',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^\d{15}$/',
				'on'=>'ip'
			),
		);
	}

	public function attributeLabels() {
		return array(
			'title'=>Yii::t('rekvizform', 'Company title'),
			'type'=>Yii::t('rekvizform','Type'),
			'inn'=>Yii::t('rekvizform', 'INN'),
			'kpp'=>Yii::t('rekvizform', 'KPP'),
			'egrip'=>Yii::t('rekvizform','EGRIP'),
			'address'=>Yii::t('rekvizform', 'Address'),
			'real_address'=>Yii::t('rekvizform', 'Real address'),
			'settlement_account'=>Yii::t('rekvizform', 'Settlement account'),
			'correspondent_account'=>Yii::t('rekvizform', 'Correspondent account'),
			'bank_title'=>Yii::t('rekvizform', 'Bank title'),
			'bank_bik'=>Yii::t('rekvizform', 'BIK'),
			'director_fio'=>Yii::t('rekvizform', 'Name of director'),
			'director_position'=>Yii::t('rekvizform', 'Director position'),
			'director_source'=>Yii::t('rekvizform', 'Director rights source'),
		);
	}

	/**
	 * Валидатор для поля director_source_info, проверяет наличие текста в поле, если
	 * director_source!=charter
	 */
	public function validatorSourceInfo($attribute, $params) {
		if ($this->director_source!=''&&$this->director_source!='charter') {
			if ($this->director_source_info=='') {
				$this->addError($attribute, Yii::t('rekvizform', 'You should fill {attribute} field', array('{attribute}'=>$this->getAttributeLabel($attribute))));
			}
		}
	}

}
