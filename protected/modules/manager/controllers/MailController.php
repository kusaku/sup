<?php 
class MailController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return
	 */

	public function filters() {
		return array(
			'accessControl'
		);
	}
	
	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */

	public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
		return array(
			array(
				'allow','actions'=>array(
					'index','list','send','massMail','resetQueue','makeQueue','processQueue','saveTemplate'
				),'roles'=>array(
					'admin','moder','topmanager','manager','master'
				),
			),array(
				'deny','users'=>array(
					'*'
				),
			),
		);
	}

	public function actionIndex() {
		$templates = MailTemplates::model()->findAll('people_id='.Yii::app()->user->id.' or people_id = 0');
		
		$this->renderPartial('index', array(
			'templates'=>$templates
		));
	}

	public function actionList($package_id = 0) {
		$templates = MailTemplates::model()->findAll('people_id='.Yii::app()->user->id.' or people_id = 0');
		
		$this->renderPartial('list', array(
			'package_id'=>$package_id,'templates'=>$templates
		));
	}

	public function actionSend($package_id = 0, $template_id = 0) {
		$package = Package::model()->findByPk($package_id);
		$client = $package->client;
		$template = MailTemplates::getById((int) $template_id);
		
		if ($client and $template) {
		
			// создаем письмо
			$post = new stdClass ();
			$post->from = Yii::app()->user->mail;
			$post->fromname = Yii::app()->user->fio;
			$post->to = $client->mail;
			$post->toname = $client->fio;
			$post->subject = $template->getSubjectFor($client);
			$post->body = $template->getBodyFor($package);
			
			// пробуем отправить
			try {
				PHPMail::send($post);
				$message = 'Письмо отправлено!';
			}
			catch(exception $e) {
				$message = $e->getMessage();
			}
			
		} else {
			$message = (!$client ? 'Клиент' : 'Шаблон').' не существует!';
		}
		
		$this->renderPartial('send', array(
			'message'=>$message
		));
	}

	public function actionMassMail() {
		$templates = MailTemplates::model()->findAll('people_id='.Yii::app()->user->id.' or people_id = 0');
		
		$this->renderPartial('massmail', array(
			'templates'=>$templates
		));
	}


	public function actionResetQueue() {
		Registry::setValue('Queue.mail.offset');
		Registry::setValue('Queue.mail.total');
		MailQueue::clear();
		FailedMailQueue::clear();
		
		Yii::app()->end(json_encode(array(
			'success'=>true
		)));
	}

	public function actionMakeQueue($filter, $template_id) {
	
		$template = MailTemplates::getById((int) $template_id);
		if (!$template) {
			print(json_encode(array(
				'success'=>false
			)));
			Yii::app()->end();
		}
		
		$quantity = Registry::getValue('Queue.mail.quantity') or $quantity = 50;
		$offset = Registry::getValue('Queue.mail.offset') or $offset = 0;
		
		$recipients = People::getByFilter($filter);
		
		foreach (array_slice($recipients, $offset, $quantity) as $client) {
		
			try {
				$post = new stdClass ();
				$post->from = Yii::app()->user->mail;
				$post->fromname = Yii::app()->user->fio;
				$post->to = $client->mail;
				$post->toname = $client->fio;
				$post->subject = $template->getSubjectFor($client);
				$post->body = $template->getBodyFor($package);
				
			}
			catch(exception $e) {
				continue;
			}
			
			MailQueue::enQueue($post);
		}
		
		Registry::setValue('Queue.mail.offset', $quantity + $offset);
		Registry::setValue('Queue.mail.total', MailQueue::length());
		
		Yii::app()->end(json_encode(array(
			'success'=>true,'total'=>count($recipients),'done'=>MailQueue::length(),'left'=>max(array(
				0,count($recipients) - $quantity - $offset
			))
		)));
	}

	public function actionProcessQueue() {
		$quantity = Registry::getValue('Queue.mail.quantity') or $quantity = 50;
		
		while ($quantity and $post = MailQueue::deQueue()) {
			try {
				$quantity--;
				PHPMail::send($post);
			}
			catch(exception $e) {
				FailedMailQueue::enQueue($post);
				continue;
			}
		}
		
		Yii::app()->end(json_encode(array(
			'success'=>true,'total'=>Registry::getValue('Queue.mail.total'),'left'=>MailQueue::length(),'failed'=>FailedMailQueue::length()
		)));
		
	}

	public function actionSaveTemplate() {
		$people_id = $_POST['people_id'];
		$name = $_POST['name'];
		$subject = $_POST['subject'];
		$body = $_POST['body'];
		
		foreach ($people_id as $pk=>$p_id) {
			$template = MailTemplates::model()->findByPk($pk) or $template = new MailTemplates();
			try {
				$template->people_id = @$people_id[$pk];
				$template->name = @$name[$pk];
				$template->subject = @$subject[$pk];
				$template->body = @$body[$pk];
				$template->save();
			}
			catch(Exception $e) {
				Yii::app()->end(json_encode(array(
					'success'=>false,'message'=>'Ошибка сохранения...'
				)));
			}
		}
		Yii::app()->end(json_encode(array(
			'success'=>true
		)));
	}
}

