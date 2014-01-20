<?php
/**
 * Модель работы с заказами на вывод средств
 *
 * @property int $id ID
 * @property string $status Статус запроса ('requested','approved','rejected')
 * @property int $id_partner ID партнера из таблицы People
 * @property string $summ Сумма вывода (в БД представлена как decimal(10,0))
 * @property int $ts_add Дата запроса вывода
 * @property int $ts_process Дата обработки (подтверждения, отклонения)
 */
class Withdraw extends CActiveRecord {

	const STAT_REQUESTED = 'requested';
	const STAT_APPROVED = 'approved';
	const STAT_REJECTED = 'rejected';

	private $_toNotifyManager = false;
	private $_iMinWithdrawal;
	private $_iMaxWithdrawal;

	public function beforeValidate() {
		$obPartner = Partner::model()->findByPk($this->id_partner);
		$this->_iMinWithdrawal = (int)$obPartner->min_withdrawal;
		$this->_iMaxWithdrawal = $obPartner->getTotalBonus() - $obPartner->getTotalWithdrawed();
		return true;
	}

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'withdraw';
	}

	public function rules() {
		return array(
			array('status', 'in', 'range' => array_keys(self::getStatuses()), 'allowEmpty'=>false),
			array('summ','numerical', 'on'=>'add', 'allowEmpty'=>false, 'min' => $this->_iMinWithdrawal, 'max'=>$this->_iMinWithdrawal),
			array('id, ts_add, ts_process', 'unsafe'),
		);
	}

	public function relations() {
		return array(
			'partner_data'=>array(
				self::BELONGS_TO,'Partner','id_partner',
			),
		);
	}

	private static $_arStatuses = array();

	public static function getStatuses(){
		return
			self::$_arStatuses ? self::$_arStatuses :
			self::$_arStatuses = array(
				self::STAT_REQUESTED => Yii::t('withdraw',self::STAT_REQUESTED),
				self::STAT_APPROVED => Yii::t('withdraw',self::STAT_APPROVED),
				self::STAT_REJECTED => Yii::t('withdraw',self::STAT_REJECTED),
			);
	}


	public function attributeLabels() {
		return array(
			'id'=>Yii::t('withdraw', 'ID'),
			'id_partner'=>Yii::t('withdraw', 'Partner\'s ID'),
			'summ'=>Yii::t('withdraw', 'Summ'),
			'ts_add'=>Yii::t('withdraw', 'Creation date'),
			'ts_process'=>Yii::t('withdraw', 'Procession date'),
			'status'=>Yii::t('withdraw', 'Status'),
		);
	}

	public function beforeSave() {
		if($this->isNewRecord){
			$this->ts_add = time();
			$this->status = self::STAT_REQUESTED;
			$this->_toNotifyManager = true;
		} else {
			//сохраняем время обновления, если обновлен статус
			$obOldInfo = Withdraw::model()->findByPk($this->id);
			if($obOldInfo && $obOldInfo->status == self::STAT_REQUESTED && ($this->status == self::STAT_REJECTED || $this->status == self::STAT_APPROVED)){
				$this->ts_process = time();
			}
		}
		return parent::beforeSave();
	}

	public function afterSave() {
		if($this->_toNotifyManager){
			try {
				$obManager=$this->partner_data->manager;
				if($obManager){
					Yii::app()->getComponent('documents')->createEmailPartnerManagerWithdrawNotification($this)->send($obManager->mail, $obManager->fio);
				} else {
					Yii::log('There is no manager for this partner', CLogger::LEVEL_ERROR);
				}
			} catch(exception $e) {}
		}
		parent::afterSave();
	}

	public function approve(){
		$this->status = self::STAT_APPROVED;
		if($this->save()){
			$obManager=$this->partner_data->manager;
			$obPartner=$this->partner_data->user_data;
			Yii::app()->getComponent('documents')->createEmailPartnerWithdrawProcessed($this)->send($obPartner->mail, $obPartner->fio);
			if($obManager){
				Yii::app()->getComponent('documents')->createEmailPartnerManagerWithdrawProcessed($this)->send($obManager->mail, $obManager->fio);
			} else {
				Yii::log('There is no manager for this partner', CLogger::LEVEL_ERROR);
			}
		}
	}

	public function reject(){
		$this->status = self::STAT_REJECTED;
		if($this->save()){
			$obManager=$this->partner_data->manager;
			$obPartner=$this->partner_data->user_data;
			Yii::app()->getComponent('documents')->createEmailPartnerWithdrawProcessed($this)->send($obPartner->mail, $obPartner->fio);
			if($obManager){
				Yii::app()->getComponent('documents')->createEmailPartnerManagerWithdrawProcessed($this)->send($obManager->mail, $obManager->fio);
			} else {
				Yii::log('There is no manager for this partner', CLogger::LEVEL_ERROR);
			}
		}
	}

}
