<?php
/**
 * Класс обеспечивает управление пакетами заказов
 * @property PackageWorkflow $workflow
 * @property integer $id
 * @property string $name
 * @property integer $site_id
 * @property integer $promocode_id
 * @property integer $status_id
 * @property integer $payment_id
 * @property string $descr
 * @property integer $client_id
 * @property integer $manager_id
 * @property string $dt_beg
 * @property string $dt_end
 * @property string $dt_change
 * @property float $summ
 * @property float $paid
 * @property float $period
 * @property integer $redmine_proj
 * @property boolean $external
 * @property integer $source_id
 * @property integer $infocode_id
 * @property Serv2pack[] servPack
 *
 * @property People $manager
 * @property PackagePayment $pay_status
 * @property Payment[] $payments
 * @property Infocode $infocode
 */
class Package extends EventableModel {

	// используется для подсчета, SELECT SUMM(amount) as `amount` ...
	public $amount = 0;

	// хранит копию записи БД
	private $cleanRecord;

	/**
	 * @param string $className
	 *
	 * @return Package
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'package';
	}

	public function relations() {
		return array(
			'manager'=>array(
				self::BELONGS_TO,'People','manager_id'
			),'client'=>array(
				self::BELONGS_TO,'People','client_id'
			),'services'=>array(
				self::MANY_MANY,'Service','serv2pack(pack_id, serv_id)'
			),'servPack'=>array(
				self::HAS_MANY,'Serv2pack','pack_id','with'=>'service','index'=>'serv_id'
			),'payments'=>array(
				self::HAS_MANY,'Payment','package_id'
			),'site'=>array(
				self::BELONGS_TO,'Site','site_id'
			),'promocode'=>array(
				self::BELONGS_TO,'Promocode','promocode_id'
			),'infocode'=>array(
				self::BELONGS_TO,'Infocode','infocode_id'
			),'wf_status'=>array(
				self::BELONGS_TO,'PackageStatus','status_id'
			),'pay_status'=>array(
				self::BELONGS_TO,'PackagePayment','payment_id'
			),'workflow'=>array(
				self::HAS_ONE,'PackageWorkflow','package_id'
			),'invoice'=>array(
				self::HAS_ONE,'PackageInvoice','package_id'
			),'invoices'=>array(
				self::HAS_MANY,'PackageInvoice','package_id'
			),'jur_person'=>array(
				self::BELONGS_TO,'JurPersonReference','jur_person_id'
			),'questionnaire'=>array(
				self::HAS_MANY,'PackageQuestionnaire','package_id','order'=>'questionnaire.date_filled DESC'
			),'history'=>array(
				self::HAS_MANY,'PackageStatusLog','package_id','order'=>'history.date DESC'
			),'documents'=>array(
				self::MANY_MANY,'Documents','document_package(package_id,document_id)'
			),'domainRequests'=>array(
				self::HAS_MANY,'DomainRequest','package_id'
			)
		);
	}

	/**
	 * ограничение области запроса и порядка
	 * @return array
	 */

	public function scopes() {
		return array(
			'byclient'=>array(
				'order'=>'client_id ASC'
			),'bychanged'=>array(
				'order'=>'dt_change ASC'
			),'lastmonth'=>array(
				'condition'=>'dt_change > SUBDATE(NOW(), INTERVAL 1 MONTH)'
			),'lastyear'=>array(
				'condition'=>'dt_change > SUBDATE(NOW(), INTERVAL 1 YEAR)'
			),'active'=>array(
				'condition'=>'status_id NOT IN(15, 999)'
			),'finished'=>array(
				'condition'=>'status_id = 999'
			),'not_denied'=>array(
				'condition'=>'status_id <> 15'
			),'virtuallypaid'=>array(
				'condition'=>'payment_id = 20'
			),'reallypaid'=>array(
				'condition'=>'payment_id = 30'
			),'paid'=>array(
				'condition'=>'payment_id IN (20, 30)'
			),'not_paid'=>array(
				'condition'=>'payment_id < 20'
			),'external'=>array(
				'condition'=>'external = 1 OR source_id=2'
			),'internal'=>array(
				'condition'=>'external = 0'
			),
		);
	}

	/**
	 * Метод инициализирует объект
	 */
	public function init() {
		parent::init();
		$this->addEventHandler('PartnersNotify');
		$this->addEventHandler('ClientPaymentStatusNotify');
		$this->addEventHandler('InfocodeUpdate');
		$this->addEventHandler('PackageLog');
	}

	/**
	 * Метод подготавливает код иконки для оформления в списке
	 */
	public function getIcon() {
		$arServices = $this->servPack;
		if (count($arServices) == 0) {
			return 'empty';
		}
		foreach ($arServices as $obServ2Pack) {
			$obService = $obServ2Pack->service;
			if ($obService->mandatory == 1 && count($obService->descriptions) > 0){
				return $obService->descriptions[0]->icon;
			}
		}
		foreach ($arServices as $obServ2Pack) {
			$obService = $obServ2Pack->service;
			if (count($obService->descriptions) > 0){
				return $obService->descriptions[0]->icon;
			}
		}
		return 'misc';
	}

	/**
	 * Метод возвращает главный продукт заказа
	 * return Service
	 */
	public function getProduct() {
		$arServices = $this->servPack;
		foreach ($arServices as $obServ2Pack) {
			$obService = $obServ2Pack->service;
			if ($obService->mandatory == 1)
				return $obService;
		}
		return NULL;
	}

	/**
	 * Метод возвращает запись продукта в заказе, при этом может
	 * вернуть информацию о связи
	 */
	public function getProductEx($bParent = false) {
		$arServices = $this->servPack;
		foreach ($arServices as $obServ2Pack) {
			if ($obServ2Pack->service->mandatory == 1) {
				if ($bParent)
					return $obServ2Pack;
				else
					return $obServ2Pack->service;
			}
		}
		return NULL;
	}

	/**
	 * Метод инициализирует работу мастера в случае, если он не был запущен ранее
	 */
	public function initWorkflow() {
		$obWorkflow = $this->workflow;
		if (!$obWorkflow) {
			$obProduct = $this->getProduct();
			//Нет состояния обработки заказа, такое может быть для старых заказов
			$obStatus = $this->wf_status;
			if (!$obStatus) {
				$this->status_id = 1;
				//Нет статуса обработки пакета, хм странно, пробуем найти продукт в заказе
				$this->update(array(
					'status_id'
				));
				$obStatus = $this->wf_status;
			}
			$obWorkflow = new PackageWorkflow();
			$obWorkflow->package_id = $this->id;
			if ($obStatus->id == 1) {
				if ($obProduct) {
					//Продукт нашли
					$obWorkflow->step_id = 'select_services';
				} else {
					//Продукт не нашли
					$obWorkflow->step_id = 'select_product';
				}
			} else {
				$obStatus2WorkflowStep = $obStatus->workflow_step;
				if ($obStatus2WorkflowStep) {
					//Нашли привязку, ставим мастер на нужное место
					$obWorkflow->step_id = $obStatus2WorkflowStep->step_id;
				} else {
					if ($obProduct) {
						$obWorkflow->step_id = 'select_services';
					} else {
						$obWorkflow->step_id = 'select_product';
					}
				}
			}
			$obWorkflow->save();
		}
		return $obWorkflow;
	}

	/**
	 * Метод вызывается во время выполнения CActiveRecord::populateRecord(), т.е. выборки из БД
	 */
	protected function afterFind() {
		$this->cleanRecord = clone $this;
		parent::afterFind();
	}

	/**
	 * Метод вызывается перед сохранением записи и выполняет проверку статусов заказа
	 */
	protected function beforeSave() {
		if (parent::beforeSave()) {
			if (!$this->isNewRecord) {
				if ($obOldValue = Package::model()->findByPk($this->id)) {
					if($this->status_id!=15) {
						if($this->status_id<$obOldValue->status_id) {
							return false;
						}
						if ($obOldValue->status_id == 30 && $this->payment_id < 20) {
							$this->status_id = 30;
						}
						if ($this->payment_id > $obOldValue->payment_id && $obOldValue->payment_id>0 && $this->status_id < 30) {
							$this->payment_id = $obOldValue->payment_id;
						}
						if ($this->payment_id < $obOldValue->payment_id && $obOldValue->payment_id > 19) {
							$this->payment_id = $obOldValue->payment_id;
						}
					} else {
						$this->payment_id = 17;
					}
				}
				// прежние vs. текущие
				if (isset($this->cleanRecord) and ($this->cleanRecord->status_id != $this->status_id or $this->cleanRecord->payment_id != $this->payment_id or $this->cleanRecord->manager_id != $this->manager_id or $this->cleanRecord->site_id != $this->site_id)) {
					// какие-то статусы заказа поменялись, запишем в лог
					$log = new PackageStatusLog();
					$log->package_id = $this->primaryKey;
					$log->status_id = $this->status_id;
					$log->payment_id = $this->payment_id;
					$log->manager_id = $this->manager_id;
					$log->site_id = $this->site_id;
					$log->save();
				}
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
		$arFilter = array(
			'status_id'=>$this->status_id,'set_step'=>1
		);
		if ($obStepInfo = PackageStatusWorkflowStep::model()->findByAttributes($arFilter)) {
			$obWorkflow = $this->initWorkflow();
			$obWorkflow->step_id = $obStepInfo->step_id;
			$obWorkflow->update(array(
				'step_id'
			));
		}
	}

	/**
	 * Метод выполняет пересчёт платежей и обновление статуса заказа
	 * в случае необходимости
	 */
	public function recountPayments() {
		if(count($this->payments)>0) {
			$this->paid=0;
			foreach($this->payments as $obPayment) {
				if($obPayment->ptype_id==1) {
					$this->paid+=$obPayment->debit*$obPayment->amount;
				} elseif($this->payment_id<20) {
					if($this->status_id < 30) {
						$this->status_id = 30;
					}
					$this->payment_id=20;
				}
			}
		}
		if ($this->paid > 0) {
			if($this->status_id < 30) {
				$this->status_id = 30;
			}
			if($this->payment_id < 30) {
				$this->payment_id = 30;
			}
		}
		$this->dt_change = date('Y-m-d H:i:s');
		return $this->save();
	}

	/**
	 * Метод выполняет установку менеджера проекта
	 * @param $iManagerId - номер менеджера проекта
	 */

	public function setManager($iManagerId) {
		if ($iManagerId != $this->manager_id && $obNewManager = People::getById($iManagerId)) {
			// Log write
			if ($this->manager_id > 0) {
				$info = date('d-m-Y')."<div>Заказ передан от <b>".People::getById($this->manager_id)->fio.'</b> к <b>'.$obNewManager->fio."</b></div>";
				Logger::put(array(
					'client_id'=>$this->client_id,'manager_id'=>Yii::app()->user->id,'info'=>$info
				));
			}
			$this->manager_id = $iManagerId;
		}

		//Добавляем cвязь менеджера и клиента, если у клиента нет менеджера
		$obClient = People::model()->findByPk($this->client_id);
		if (!$obClient->manager) {
			$obClient2Manager = new PeopleToManager();
			$obClient2Manager->user_id = $obClient->id;
			$obClient2Manager->manager_id = $this->manager_id;
			$obClient2Manager->save();
		}
		//Добавляем информацию о юридическом лице, если она не заполнена
		if($this->jur_person_id==0 && $this->manager_id!=0) {
			$obManager=People::model()->findByPk($this->manager_id);
			if($obManager->jur_person_id==0) {
				$obManager->jur_person_id=2;
				$obManager->update(array('jur_person_id'));
			}
			$this->jur_person_id = $obManager->jur_person_id;
		}
	}

	/**
	 * Метод выполняет генерацию текстового номера заказа для отображения
	 */

	public function getNumber() {
		$str = '';
		if (is_object($this->manager)) {
			foreach (explode(" ", $this->manager->fio) as $word) {
				$str .= mb_substr($word, 0, 1, 'utf-8');
			}
			$str .= '-'.$this->primaryKey;
			return mb_convert_case($str, MB_CASE_UPPER, 'utf-8');
		} else {
			return '__-'.$this->primaryKey;
		}
	}

	public function getSum() {
		return new PackageSumm($this);
	}

	/**
	 * Функция возвращает данные в формате подготовленном для API
	 */
	public function getPackageInfo() {
		$arResult=array(
			'id'=>$this->id,
			'code'=>$this->getNumber(),
			'promocode'=>'',
			'title'=>$this->name,
			'summ'=>$this->summ,
			'date_create'=>date('c',strtotime($this->dt_beg)),
			'date_edit'=>date('c',strtotime($this->dt_change)),
			'description'=>$this->descr,
			'full_description'=>'',
			'icon'=>'',
			'client'=>array(
				'id'=>$this->client_id,
				'fio'=>$this->client->fio,
				'mail'=>$this->client->mail
			)
		);
		if(!is_null($this->promocode)) {
			$arResult['promocode']=$this->promocode->code;
		}
		$obProduct=$this->getProduct();

		//если нашли продукт, то полное описание берём из его описания
		if($obProduct) {
			$arDescriptions=$obProduct->descriptions;
			if(count($arDescriptions)>0) {
				$arResult['description']=$arDescriptions[0]->description;
				$arResult['full_description']=$arDescriptions[0]->content;
				$arResult['icon']=$arDescriptions[0]->icon;
				$arResult['title']=$arDescriptions[0]->title; //TODO Предусмотреть название по умолчанию
			}
			else
				$arResult['title']=$obProduct->name;
		} else
			$arResult['icon']='empty';
		//если есть site_id получим информацию о сайте
		if($obSite=$this->site) {
			$arResult['site']=array(
				'id'=>$obSite->id,
				'url'=>$obSite->url,
			);
		}
		//Берём статус
		$obStatus=$this->wf_status;
		$arResult['status']=array(
			'id'=>$obStatus->id,
			'title'=>$obStatus->name,
			'text_ident'=>$obStatus->text_ident
		);
		//Берём статус оплаты
		if($this->payment_id<17) {
			$this->payment_id=17;
			$this->update(array('payment_id'));
			$obPStatus=PackagePayment::model()->findByPk($this->payment_id);
		} else {
			$obPStatus=$this->pay_status;
		}
		$arResult['payment']=array(
			'id'=>$obPStatus->id,
			'title'=>$obPStatus->name,
			'text_ident'=>$obPStatus->text_ident
		);

		//А теперь ищем все услуги, кроме продукта и отображаем их
		if($arServices=$this->servPack) {
			$arResult['services']=array();
			foreach($arServices as $obServ2Pack) {
				$arRow=array(
					'id'=>$obServ2Pack->serv_id,
					'quant'=>$obServ2Pack->quant,
					'summ'=>$obServ2Pack->price,
					'descr'=>$obServ2Pack->descr,
				);
				$obService=$obServ2Pack->service;
				$arRow['price']=$obService->price;
				if($arDescriptions=$obService->descriptions) {
					$arRow['title']=$arDescriptions[0]->title;
					$arRow['description']=$arDescriptions[0]->description;
					$arRow['days']=$arDescriptions[0]->days;
				} else {
					$arRow['title']=$obService->name;
					$arRow['description']=$obService->descr;
					$arRow['days']=7;
				}
				if($obProduct && $obServ2Pack->serv_id==$obProduct->id) {
					$arResult['product']=$arRow;
				} else {
					$arResult['services'][]=$arRow;
				}
			}
		}
		return $arResult;
	}
}
