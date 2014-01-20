<?php
/**
 * Класс выполняет подготовку и отправку писем
 * @author
 * @version 1.2
 * @since
 * - 1.2 - добавлено оборачивание всех ошибок в ошибку класса PhpMailException, для того, чтобы в дальнейшей обработке было видно, откуда была выброшена ошибка
 */
class PHPMail extends CModel {
	private static $arLastMail=false;

	public $from;
	public $fromname;
	public $to;
	public $toname;
	public $subject;
	public $body;
	public $attachments;

	public function attributeNames() {
		return array(
			'from','fromname','to','toname','subject','body','attachments'
		);
	}

	public function rules() {
		return array(
			array(
				'from,to,subject,body','required'
			),
			array(
				'from,to','email'
			),
			array(
				'attachments','safe'
			)
		);
	}

	public function SendMe() {
		self::send($this);
	}

	/**
	 * Метод позволяет подготовить и отправить электронное сообщение
	 * @param object  $post  - объект описывающий отправляемое письмо
	 * @param boolean $clear - флаг указывающий на необходимость очистки кэша
	 *
	 * @throws PHPMailException
	 * @return void
	 */
	static function send($post, $clear = true) {
		try {
			$mailer = Yii::app()->JPhpMailer;
			if($clear) {
				$mailer->Clear();
			}
			switch (Yii::app()->params['PHPMailer']['method']) {
				case 'mail':
					$mailer->IsMail();
					break;
				case 'sendmail':
					$mailer->IsSendmail();
					break;
				case 'qmail':
					$mailer->IsQmail();
					break;
				case 'smtp':
					$mailer->IsSMTP();
					$mailer->Host = Yii::app()->params['PHPMailer']['host'];
					$mailer->SMTPAuth = true;
					$mailer->Username = Yii::app()->params['PHPMailer']['user'] ? Yii::app()->params['PHPMailer']['user']['login'] : Yii::app()->user->mail;
					$mailer->Password = Yii::app()->params['PHPMailer']['user'] ? Yii::app()->params['PHPMailer']['user']['password'] : Yii::app()->user->password;
					break;
			}
			$mailer->SetFrom($post->from, $post->fromname);
			$mailer->AddAddress($post->to, $post->toname);
			$mailer->CharSet = 'utf-8';
			$mailer->Subject = $post->subject;
			$mailer->MsgHTML($post->body);
			$mailer->AltBody = strip_tags($post->body);
			if (isset($post->attachments) and is_array($post->attachments))
				foreach ($post->attachments as $name=>$attachment)
					$mailer->AddAttachment($attachment, $name);
			$mailer->Send();
			self::$arLastMail=array(
				'header'=>$mailer->CreateHeader(),
				'body'=>$mailer->CreateBody()
			);
		} catch(Exception $e) {
			throw new PHPMailException($e->getMessage(),$e->getCode());
		}
	}

	static function getLastMail() {
		if(self::$arLastMail) {
			return self::$arLastMail;
		}
		return false;
	}
}

/**
 * Класс обозначает все ошибки как ошибки уровня PHPMailException
 */
class PHPMailException extends CException {}
