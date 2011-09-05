<?php 
Yii::import('application.extensions.phpmailer.JPhpMailer');
YiiBase::autoload('Persistent');

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
				'allow',
				'actions'=>array(
					'index',
					'list',
					'send',
					'massMail',
					'resetQueue',
					'makeQueue',
					'processQueue',
					'saveTemplate'
				),
				'roles'=>array(
					'admin',
					'moder',
					'topmanager',
					'manager',
					'master'
				),
			),
			array(
				'deny',
				'users'=>array(
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
	
	public function actionList($client_id = 0) {
		$templates = MailTemplates::model()->findAll('people_id='.Yii::app()->user->id.' or people_id = 0');
		$this->renderPartial('list', array(
			'client_id'=>$client_id,'templates'=>$templates
		));
	}
	
	public function actionSend($client_id = 0, $template_id = 0) {
		$client = People::getById((int) $client_id);
		$template = MailTemplates::getById((int) $template_id);
		
		if ($client and $template) {
		
			$mail = new JPhpMailer(true);
			$mail->IsSMTP();
			$mail->Host = 'mail.fabricasaitov.ru';
			$mail->SMTPAuth = true;
			$mail->Username = Yii::app()->user->mail;
			$mail->Password = base64_decode(Yii::app()->user->key);
			$mail->SetFrom(Yii::app()->user->mail, Yii::app()->user->fio);
			$mail->Subject = $template->getSubjectFor($client);
			$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
			$mail->MsgHTML($template->getBodyFor($client));
			$mail->AddAddress($client->mail, $client->fio);
			$mail->CharSet = 'utf8';
			
			//$mail->AddAttachment('/home/aks/Картинки/studio-logo.png', $name = "", $encoding = "base64", $type = "application/octet-stream");
			
			try {
				$mail->Send();
				$message = 'Письмо отправлено!';
			}
			catch(phpmailerException $e) {
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
		
		print(json_encode(array(
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
		
		foreach (array_slice($recipients, $offset, $quantity) as $recipient) {
			try {
				$mail = new JPhpMailer(true);
				$mail->IsSMTP();
				$mail->Host = 'mail.fabricasaitov.ru';
				$mail->SMTPAuth = true;
				$mail->Username = Yii::app()->user->mail;
				$mail->Password = base64_decode(Yii::app()->user->key);
				$mail->SetFrom(Yii::app()->user->mail, Yii::app()->user->fio);
				$mail->Subject = $template->getSubjectFor($recipient);
				$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
				$mail->MsgHTML($template->getBodyFor($recipient));
				$mail->AddAddress($recipient->mail, $recipient->fio);
				$mail->CharSet = 'utf8';
			}
			catch(exception $e) {
				continue;
			}
			
			MailQueue::enQueue($mail);
		}
		
		Registry::setValue('Queue.mail.offset', $quantity + $offset);
		Registry::setValue('Queue.mail.total', MailQueue::length());
		
		print(json_encode(array(
			'success'=>true,'total'=>count($recipients),'done'=>MailQueue::length(),'left'=>max(array(
				0,count($recipients) - $quantity - $offset
			))
		)));
		Yii::app()->end();
	}
	
	public function actionProcessQueue() {
		$quantity = Registry::getValue('Queue.mail.quantity') or $quantity = 50;
		
		while ($quantity and $mail = MailQueue::deQueue()) {
			try {
				$quantity--;
				$mail->Send();
			}
			catch(exception $e) {
				FailedMailQueue::enQueue($mail);
				continue;
			}
		}
		
		print(json_encode(array(
			'success'=>true,'total'=>Registry::getValue('Queue.mail.total'),'left'=>MailQueue::length(),'failed'=>FailedMailQueue::length()
		)));
		Yii::app()->end();
		
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
				exit(json_encode(array(
					'success'=>false,'message'=>'Ошибка сохранения...'
				)));
			}
		}
		exit(json_encode(array(
			'success'=>true
		)));
	}
}

