<?php 
class MailController extends Controller {
	public function actionIndex() {
		$templates = MailTemplates::model()->findAll('people_id='.Yii::app()->user->id.' or people_id = 0');
		$this->renderPartial('index', array('templates'=>$templates));
	}
	
	public function actionList($client_id = 0) {
		$templates = MailTemplates::model()->findAll('people_id='.Yii::app()->user->id.' or people_id = 0');
		$this->renderPartial('list', array('client_id'=>$client_id, 'templates'=>$templates));
	}
	
	public function actionSend($client_id = 0, $template_id = 0) {
		$client = People::getById((int) $client_id);
		$template = MailTemplates::getById((int) $template_id);
		
		if ($client and $template) {
			Yii::import('application.extensions.phpmailer.JPhpMailer');
			
			$mail = new JPhpMailer(true);
			$mail->IsSMTP();
			$mail->Host = 'mail.fabricasaitov.ru';
			$mail->SMTPAuth = true;
			$mail->Username = 'kirill.a@fabricasaitov.ru';
			$mail->Password = 'eBr_iMQ9';
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
		$this->renderPartial('send', array('message'=>$message));
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
				exit(json_encode(array('success'=>false, 'message'=>'Ошибка сохранения...')));
			}			
		}
		exit(json_encode(array('success'=>true)));
	}
}

