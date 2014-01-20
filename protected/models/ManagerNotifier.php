<?php
/**
 * Модель обеспечивает простое добавление уведомлений для менеджеров
 * Уведомления сразу рассылаются в календарь, журнал заказа и на почту менеджеру.
 */
class ManagerNotifier extends CModel {
	public $log='';
	public $calendar='';
	public $mail='';
	public $manager_id=0;
	public $client_id=0;

	private $bSendLog;
	private $bSendEvent;
	private $bSendEmail;
	
	function __construct($bSendLog=true,$bSendEvent=true,$bSendEmail=true) {
		$this->bSendLog=$bSendLog;
		$this->bSendEvent=$bSendEvent;
		$this->bSendEmail=$bSendEmail;

	}

	function attributeNames() {
		return array('log','calendar','mail','manager_id','client_id');
	}

	function Send() {
		$obManager=People::getById($this->manager_id);
		if($obManager) {
			if($this->calendar=='') $this->calendar=$this->log;
			if($this->mail=='') $this->mail=$this->calendar;
			//Запись в журнал
			if($this->bSendLog) {
				$arNotification=array(
					'client_id'=>$this->client_id,
					'manager_id'=>$this->manager_id,
					'info'=>$this->log,
					'dt'=> date('Y-m-d h:i:s')
				);
				Logger::put($arNotification);
			}
			//Уведомление в календаре
			if($this->bSendEvent) {
				$event = new Calendar();
				$event->people_id = $this->manager_id;
				$event->date = date('Y-m-d H:i:s');
				$event->message = $this->calendar;
				$event->interval = 0;
				$event->status = 1;
				$event->save();
			}
			//Уведомление на почту
			if($this->bSendEmail) {
				$post = new stdClass ();
				$post->from = Yii::app()->getModule('api')->MailParams['emailFrom'];
				$post->fromname = Yii::app()->getModule('api')->MailParams['emailNameFrom'];
				$post->to = $obManager->mail;
				$post->toname = $obManager->fio;
				$post->subject = strip_tags($this->log);
				$post->body = $this->mail;
				// пробуем отправить
				try {
					PHPMail::send($post);
				}
				catch(exception $e) {}
			}
			return true;
		}
		return false;
	}
}
