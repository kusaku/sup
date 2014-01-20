<?php
/**
 * Модель работы с партнёрами
 *
 * @property int $id Id
 * @property string $name ?Название партнера?
 * @property string $type Тип партнера ('consultant', 'default')
 * @property string $date_sign дата подписания контракта
 * @property string $status статус партнера
 * @property string $agreement_num ?Номер соглашения?
 * @property int $manager_id Id менеджера партнера
 * @property int $infocode_id Id инфокода партнера
 * @property int $percent Процент бонуса партнера
 * @property int $min_withdrawal Минимальная сумма для вывода
 */
class Partner extends CActiveRecord {

	private $bStatusChange=false;
	public $sLogMessage='';

	const STAT_NEW = 'new';
	const STAT_NEGOTIATIONS = 'negotiations';
	const STAT_ACTIVE = 'active';
	const STAT_CLOSED = 'closed';
	const STAT_DECLINED = 'declined';

	const TP_CONSULTANT = 'Consultant';
	const TP_DEFAULT = 'Partner';

	private static $_arStatuses = array();

	public static function getStatuses(){
		return
			self::$_arStatuses ? self::$_arStatuses :
			self::$_arStatuses = array(
				self::STAT_NEW => Yii::t('partner',self::STAT_NEW),
				self::STAT_NEGOTIATIONS => Yii::t('partner',self::STAT_NEGOTIATIONS),
				self::STAT_ACTIVE => Yii::t('partner',self::STAT_ACTIVE),
				self::STAT_CLOSED => Yii::t('partner',self::STAT_CLOSED),
				self::STAT_DECLINED => Yii::t('partner',self::STAT_DECLINED),
			);
	}

	private static $_arTypes = array();

	public static function getTypes(){
		return
			self::$_arTypes ? self::$_arTypes :
			self::$_arTypes = array(
				self::TP_CONSULTANT => Yii::t('partner',self::TP_CONSULTANT),
				self::TP_DEFAULT => Yii::t('partner',self::TP_DEFAULT),
			);
	}

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'partner';
	}

	public function rules() {
		return array(
			array('status,percent', 'safe', 'on' => 'edit'),
			array('percent', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 100, 'on' => 'edit'),
			array('min_withdrawal', 'numerical', 'min' => 100, 'on' => 'edit'),
			array('status', 'in', 'range' => array_keys(self::getStatuses()), 'on' => 'edit'),
			array('type', 'in', 'range' => array_keys(self::getTypes()), 'on' => 'new'),
			array('id,type', 'unsafe'),
		);
	}

	public function relations() {
		return array(
			'user_data'=>array(
				self::BELONGS_TO,'People','id',
			),
			'manager'=>array(
				self::BELONGS_TO,'People','manager_id',
			),
			'infocode'=>array(
				self::BELONGS_TO,'Infocode','infocode_id',
			),
			'withdraws'=>array(
				self::HAS_MANY,'Withdraw','id_partner'
			),
		);
	}

	public function setAttribute($name,$value) {
		$methodname='set'.$name;
		if(method_exists($this, $methodname))
			return $this->$methodname($value);
		else
			return parent::setAttribute($name,$value);
	}

	public function getTotalBonus(){
		$sql = '
SELECT
  SUM(package.summ * package.partner_percent / 100) AS total_bonus
FROM
    partner_people
        JOIN
    people AS client ON partner_people.id_client = client.id
        JOIN
    package ON partner_people.id_client = package.client_id
WHERE
    TIMESTAMPADD(YEAR, 1, client.regdate) >= package.dt_beg
	AND
	partner_people.id_partner = :id_partner
GROUP BY partner_people.id_partner
';
		$command=Yii::app()->db->createCommand($sql);
		$rows=$command->query(array(':id_partner'=>$this->id))->readAll();
		return isset($rows[0]) ? $rows[0]['total_bonus'] : 0;

	}

	public function getTotalWithdrawed(){
		$sql = '
SELECT
    SUM(summ) AS total_withdrawed
FROM
    withdraw
WHERE
	id_partner = :id_partner
	AND
	status IN(:approved,:requested)
GROUP BY id_partner
';
		$command=Yii::app()->db->createCommand($sql);
		$rows=$command->query(array(
			':id_partner'=>$this->id,
			':approved'=>Withdraw::STAT_APPROVED,
			':requested'=> Withdraw::STAT_REQUESTED
		))->readAll();
		return isset($rows[0]) ? $rows[0]['total_withdrawed'] : 0;

	}

	/**
	 * Перекрываем функцию установки статуса
	 */
	public function setstatus($value) {
		if($value!=$this->status) {
			$this->bStatusChange=true;
		}
		$this->status=$value;
		return true;
	}

	public function setdate_sign($value) {
		if(preg_match('#^\d\d\d\d-\d\d-\d\d \d\d\:\d\d:\d\d$#',$value))
			$this->date_sign=$value;
		return true;
	}

	public function setInfocode($sInfocode){
		$sValue = htmlspecialchars($sInfocode,ENT_QUOTES,'utf-8',false);

		if($this->infocode){
			if ($this->infocode['value'] != $sValue) {
				throw new Exception(Yii::t('partner','Infocode couldn\'t be changed'));
			}
		} else {
			$obInfocode = Infocode::model()->with('partner')->findByAttributes(array('value'=>$sValue));

			if($obInfocode && $obInfocode->type !== Infocode::TYPE_PARTNER){
				throw new Exception(Yii::t('partner','Infocode has incompatible type'));
			}

			// Есть ли уже партнер с таким инфокодом
			if($obInfocode && $obInfocode->partner){
				throw new Exception(Yii::t('partner','Infocode is taken by partnerId: {partnerId}', array('{partnerId}'=>$obInfocode->partner['id'])));
			}

			//Существует ли вообще такой инфокод
			if(!$obInfocode) {
				$obInfocode = new Infocode();
				$obInfocode->type = Infocode::TYPE_PARTNER;
				$obInfocode->value = $sValue;
				$obInfocode->descr = Yii::t('partner','Has been created automatically while editing partner\'s info');
				if(!$obInfocode->save()){
					$arErrors = $obInfocode->getErrors();
					throw new Exception(Yii::t('partner',$arErrors[0]));
				}
			}

			$this->infocode_id = $obInfocode->primaryKey;
		}

	}

	/**
	 * Метод привязывает партнера к менеджеру, если партнер еще не привязан
	 *
	 * @param integer $iManagerId ID менеджера для привязки
	 * @return boolean привязан партнер или нет
	 */
	public function bindToManager($iManagerId){
		if($this->manager_id == 0){
			$this->manager_id = (int)$iManagerId;
			return $this->save();
		} else {
			$this->addError('manager_id', Yii::t('partner','Partner is already bent'));
			return false;
		}
	}

	/**
	 * Метод выполняет записл статуса в лог после изменения статуса партнёра
	 */
	protected function afterSave() {
		parent::afterSave();
		if($this->bStatusChange) {
			$obLog=new PartnerStatusLog();
			$obLog->partner_id=$this->id;
			$obLog->manager_id=Yii::app()->user->id;
			$obLog->status=$this->status;
			$obLog->comment=$this->sLogMessage;
			$obLog->date=date('Y-m-d H:i:s');
			$obLog->save();
		}
	}

	protected function beforeSave() {
		if($this->isNewRecord){
			$obPartnerDefaultSettings = new PartnerDefaultSettings();
			if(!$this->type){
				$this->type = self::TP_DEFAULT;
			}
			if(!$this->percent){
				switch($this->type){
					case self::TP_CONSULTANT:
						$this->percent = $obPartnerDefaultSettings->consultant_percent;
						break;
					default:
						$this->percent = $obPartnerDefaultSettings->partner_percent;
				}
			}
			if(!$this->min_withdrawal){
				$this->min_withdrawal = $obPartnerDefaultSettings->min_withdrawal;
			}
		}
		return parent::beforeSave();
	}
}
