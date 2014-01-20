<?php 
/**
 * Класс реализует ActiveRecord для записей таблицы PackageWorkflow
 * @property string $step_id
 * @property integer $package_id
 *
 * @property Package $package
 * @property PackageWorkflowStep $step
 * @property PackageWorkflowSession $session
 * @property PackageWorkflowSession[] $sessions
 * @property string $date_ping
 */
class PackageWorkflow extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package_workflow';
	}
	
	public function relations() {
		return array(
			'package'=>array(
				self::BELONGS_TO,
				'Package',
				'package_id'
			),
			'step'=>array(
				self::BELONGS_TO,
				'PackageWorkflowStep',
				'step_id'
			),
			'session'=>array(
				self::HAS_ONE,
				'PackageWorkflowSession',
				array('package_id'=>'package_id','step_id'=>'step_id'),
			),
			'sessions'=>array(
				self::HAS_MANY,
				'PackageWorkflowSession',
				'package_id'
			)
		);
	}
	
	/**
	 * Метод выполняется перед сохранения шага и обновляет дату последнего изменения записи о шаге
	 */
	protected function beforeSave() {
		if(parent::beforeSave()) {
			$this->date_ping=date('Y-m-d H:i:s');
			return true;
		}
		return false;
	}
	
	/**
	 * Метод ищет и возвращает данные сессии по номеру шага
	 */
	protected function getSession($step_id) {
		return PackageWorkflowSession::model()->findByPk(array('package_id'=>$this->package_id,'step_id'=>$step_id));
	}
	
	/**
	 * Метод позволяет записать абстрактные данные для текущего шага
	 */
	public function saveData($arData,$sStep=false) {
		if(!$sStep) {
			if($obData=$this->session) {
				$obData->data=json_encode($arData);
				return $obData->update(array('data'));
			} else {
				$obData=new PackageWorkflowSession();
				$obData->package_id=$this->package_id;
				$obData->step_id=$this->step_id;
				$obData->data=json_encode($arData);
				return $obData->save();
			}
		} else {
			if($obData=$this->getSession($sStep)) {
				$obData->data=json_encode($arData);
				return $obData->update(array('data'));
			} elseif($obStep=PackageWorkflowStep::model()->findByPk($sStep)) {
				$obData=new PackageWorkflowSession();
				$obData->package_id=$this->package_id;
				$obData->step_id=$obStep->primaryKey;
				$obData->data=json_encode($arData);
				return $obData->save();
			}
		}
		return false;
	}
	
	/**
	 * Метод позволяет получить данные для текущего шага
	 */
	public function getData($sStep=false) {
		if($sStep==false)
			$obData=$this->session;
		else 
			$obData=$this->getSession($sStep);
		if(!is_null($obData)) {
			return json_decode($obData->data,true);
		} else {
			return null;
		}
	}
}
