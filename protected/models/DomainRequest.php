<?php
/**
 * Модель обеспечивающая сохранение заявок на регистрацию доменов
 * @property integer $id
 * @property integer $client_id
 * @property integer $package_id
 * @property integer $site_id
 * @property string  $domain
 * @property string  $zone
 * @property string  $mode
 * @property string  $date_add
 * @property string  $date_change
 * @property string  $status
 *
 * @property Package $package
 */
class DomainRequest extends CActiveRecord {
	private $bDenyOtherRequests;
	/**
	 * @param string $className
	 *
	 * @return CActiveRecord
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'domain_request';
	}

	public function rules() {
		return array(
			array(
				'client_id,package_id,site_id,domain,zone,mode,la_country,la_state,la_postcode,la_city,la_address,pa_state,pa_postcode,pa_city,pa_address,pa_addressee,phone,fax,mobile,email',
				'safe',
				'on'=>'new'
			),
			array(
				'site_id,domain,zone,mode,la_country,la_state,la_postcode,la_city,la_address,pa_state,pa_postcode,pa_city,pa_address,pa_addressee,phone,fax,mobile,email',
				'safe',
				'on'=>'edit',
			)
		);
	}

	public function relations() {
		return array(
			'company'=>array(
				self::HAS_ONE,'DomainRequestCompany','request_id'
			), 'person'=>array(
				self::HAS_ONE,'DomainRequestPerson','request_id'
			), 'statusLog'=>array(
				self::HAS_MANY,'DomainRequestStatusLog','request_id'
			), 'client'=>array(
				self::BELONGS_TO,'People','client_id'
			), 'raw'=>array(
				self::HAS_ONE,'DomainRequestRaw','request_id'
			), 'package'=>array(
				self::BELONGS_TO,'Package','package_id'
			)
		);
	}

	/**
	 * Метод возвращает список допустимых типов заявок
	 * @return array
	 */
	public static function GetModes() {
		return array(
			'company'=>Yii::t('domainRequest','mode_company'),
			'person'=>Yii::t('domainRequest','mode_person'),
			'generic'=>Yii::t('domainRequest','mode_generic')
		);
	}

	public function beforeSave() {
		if(parent::beforeSave()) {
			$this->bDenyOtherRequests=false;
			if($this->isNewRecord && $this->package_id>0) {
				if(DomainRequest::model()->countByAttributes(array('package_id'=>$this->package_id,'status'=>'submited'))>0) {
					throw new CException('Can\'t create new request if there is valid request for this package');
				}
				$this->bDenyOtherRequests=true;
			}
			return true;
		}
		return false;
	}

	public function afterSave() {
		if($this->bDenyOtherRequests) {
			if($arRequests=DomainRequest::model()->findAllByAttributes(array('package_id'=>$this->package_id,'status'=>'new'))) {
				foreach($arRequests as $obReq) {
					if($obReq->id==$this->id) continue;
					$obReq->status='denied';
					$obReq->update(array('status'));
				}
			}
			$this->bDenyOtherRequests=false;
		}
	}

	/**
	 * Метод возвращает список доступных статусов заявок
	 * @return array
	 */
	public static function GetStatuses() {
		return array(
			'new'=>Yii::t('domainRequest','status new'),
			'submited'=>Yii::t('domainRequest','status submited'),
			'denied'=>Yii::t('domainRequest','status denied')
		);
	}
}
