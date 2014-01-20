<?php
/**
 * Класс обеспечивает взаимодействие с таблицей платежей
 * @property integer $id
 * @property string $name
 * @property integer $ptype_id
 * @property integer $billing_id
 * @property string $dt
 * @property integer $package_id
 * @property float $amount
 * @property integer $debit
 * @property integer $rekviz_id
 * @property string $description
 * @property string $dt_pay
 *
 * @property Package $package
 */
class Payment extends CActiveRecord {

	// используется для подсчета, SELECT SUMM(amount) as `summ` ...
	public $summ = 0;

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'payment';
	}

	public function relations() {
		return array(
			'package'=>array(
				self::BELONGS_TO,'Package','package_id'
			),'rekviz'=>array(
				self::BELONGS_TO,'Rekviz','rekvizit_id'
			),
		);
	}

	/**
	 * ограничение области запроса
	 * @return array
	 */
	public function scopes() {
		return array(
			'rec'=>array(
				'condition'=>'ptype_id = 0'
			),'pay'=>array(
				'condition'=>'ptype_id = 1'
			),'recpay'=>array(
				'condition'=>'ptype_id IN (0, 1)'
			),'percent'=>array(
				'condition'=>'ptype_id = 2'
			),'return'=>array(
				'condition'=>'debit = -1'
			),
		);
	}

	public function attributeLabels() {
		return array(
			'description'=>'Реквизиты платежа (плательщик)',
			'amount'=>'Сумма платежа',
			'dt'=>'Дата платежа',
			'ptype_id'=>'Платёж подтверждён',
			'dt_pay'=>'Дата поступления на Р/С компании'
		);
	}

	public function rules() {
		return array(
			array(
				'name,ptype_id,billing_id,dt,dt_pay,package_id,amount,debit,rekviz_id,description',
				'safe'
			),
			array(
				'amount',
				'type',
				'type'=>'float'
			),
			array(
				'dt,dt_pay',
				'date',
				'format'=>'dd.MM.yyyy'
			)
		);
	}

	protected function beforeSave() {
		if(parent::beforeSave()) {
			if($this->ptype_id==1 && $this->dt_pay==NULL) {
				$this->dt_pay=date('Y-m-d H:i:s');
			}
			return true;
		}
		return false;
	}

	/**
	 * Метод выполняется после сохранения записи о пакете и обновляет состояние мастера управления пакетом
	 */
	protected function afterSave() {
		parent::afterSave();
		if($this->package_id>0) {
			if(!is_null($this->package)) {
				$this->package->recountPayments();
			}
		}
	}

	/**
	 * Метод выполняется после сохранения записи о пакете и обновляет состояние мастера управления пакетом
	 */
	protected function afterDelete() {
		parent::afterDelete();
		if($this->package_id>0) {
			if(!is_null($this->package)) {
				$this->package->recountPayments();
			}
		}
	}
}
