<?php 
class DocsController extends Controller {

	/**
	 * генерация PDF из HTML
	 * @param object $content html строка
	 * @return
	 */

	private function doPDF($content, $mail = false) {
		$content = $this->renderPartial('pdfpage', array(
			'content'=>$content
		), true);
		// нужны абсолютные  url
		//$content = str_replace('src="/images/', 'src="http://'.$_SERVER['HTTP_HOST'].'/images/', $content);
		
		// mPDF
		$mPDF = Yii::app()->ePdf->mpdf();
		$mPDF->WriteHTML($content);
		
		$package_id = (int) Yii::app()->getRequest()->getParam('id');
		$filename = "{$this->getAction()->getId()}_{$package_id}.pdf";
		
		if ($mail) {
			$package = Package::model()->with('client')->findByPk($package_id);
			$client = $package->client;
			
			$filepath = Yii::getPathOfAlias('application.runtime').'/'.$filename;
			
			$mPDF->Output($filepath, 'F');
			
			// создаем письмо
			$post = new stdClass ();
			$post->from = Yii::app()->user->mail;
			$post->fromname = Yii::app()->user->fio;
			$post->to = $client->mail;
			$post->toname = $client->fio;
			$post->subject = "Документы по заказу #{$package->primaryKey}";
			$post->body = "Документы по заказу #{$package->primaryKey} находятся во вложенном файле";
			$post->attachments = array(
				$filename=>$filepath
			);
			
			PHPMail::send($post);
			
		} else {
			$mPDF->Output($filename, 'D');
		}
		
		unset($_GET['pdf'], $_GET['mail']);
		$url = Yii::app()->getUrlManager()->createUrl($this->getRoute(), $this->getActionParams());
		$this->redirect($url);
	}

	/**
	 * Метод генерирует PDF на основании переданного контента и возвращает его в виде результата
	 */
	private function doPDFEx($content) {
		$content = $this->renderPartial('pdfpage', array('content'=>$content), true);
		// нужны абсолютные  url
		//$content = str_replace('src="/images/', 'src="http://'.$_SERVER['HTTP_HOST'].'/images/', $content);
		// mPDF
		$mPDF = Yii::app()->ePdf->mpdf();
		$mPDF->WriteHTML($content);
		return $mPDF->Output($filename, 'S');
	}
	
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
			array('deny')
		);
	}

	public function actionIndex() {
		$this->redirect(Yii::app()->homeUrl);
	}
	
	/*
	 Действие при заданном параметре.
	 Возвращаем форму с данными человека.
	 */

	public function actionView($id, $hash = null) {
		$package = Package::model()->findByPk($id) or $package = new Package();
		
		if (md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
			if ($package->payment_id < 18) {
				$package->payment_id = 18;
				$package->save();
			}
			$this->renderPartial('view', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash
			));
		} else {
			throw new CHttpException(403, 'Неправильный ключ');
		}
	}

	public function actionSeen($id, $hash = null) {
		$package = Package::model()->findByPk($id) or $package = new Package();
		
		if (md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
			if ($package->payment_id < 19) {
				$package->payment_id = 19;
				$package->save();
			}
			$url = Yii::app()->getUrlManager()->createUrl($this->getUniqueId(), $this->getActionParams());
			$this->redirect($url);
		} else {
			throw new CHttpException(403, 'Неправильный ключ');
		}
	}
	
	/**
	 * генерация счета
	 * @param object $id
	 * @param object $hash
	 * @return
	 */
	public function actionBill($id, $hash, $pdf = false, $mail = false, $return=false) {
		$package = Package::model()->findByPk($id) or $package = new Package();
		
		if (md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
			$content = $this->renderPartial('bill', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash
			), true);
			if ($pdf) {
				if($return) {
					echo $this->doPDFEx($content);	
					return;
				} else {
					$this->doPDF($content, $mail);
				}
			}
			$this->renderPartial('webpage', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash,'content'=>$content
			));
		} else {
			throw new CHttpException(403, 'Неправильный ключ');
		}
	}
	
	/**
	 * оплата QIWI
	 * @param object $id
	 * @param object $hash
	 * @return
	 */

	public function actionQiwi($id, $hash, $pdf = false, $mail = false) {
		$package = Package::model()->findByPk($id) or $package = new Package();
		
		if (md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
			$content = $this->renderPartial('qiwi', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash
			), true);
			if ($pdf) {
				$this->doPDF($content, $mail);
			}
			$this->renderPartial('webpage', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash,'content'=>$content
			));
		} else {
			throw new CHttpException(403, 'Неправильный ключ');
		}
	}
	
	/**
	 * генерация акта
	 * @param object $id
	 * @param object $hash
	 * @return
	 */

	public function actionAct($id, $hash, $pdf = false, $mail = false, $return = false) {
		$package = Package::model()->findByPk($id) or $package = new Package();
		
		if (md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
			$content = $this->renderPartial('act', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash
			), true);
			if ($pdf) {
				if($return) {
					echo $this->doPDFEx($content);	
					return;
				} else {
					$this->doPDF($content, $mail);
				}
			}
			$this->renderPartial('webpage', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash,'content'=>$content
			));
		} else {
			throw new CHttpException(403, 'Неправильный ключ');
		}
	}
	
	/**
	 * генерация квитанции
	 * @param object $id
	 * @param object $hash
	 * @return
	 */

	public function actionReceipt($id, $hash, $pdf = false, $mail = false, $return = false) {
		$package = Package::model()->findByPk($id) or $package = new Package();
		
		if (md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
			$content = $this->renderPartial('receipt', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash
			), true);
			if ($pdf) {
				if($return) {
					echo $this->doPDFEx($content);	
					return;
				} else {
					$this->doPDF($content, $mail);
				}
			}
			$this->renderPartial('webpage', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash,'content'=>$content
			));
		} else {
			throw new CHttpException(403, 'Неправильный ключ');
		}
	}
	
	/**
	 * Метод генерирует оригинал договора (без печатей)
	 * @param object $id
	 * @param object $hash
	 * @return
	 */
	public function actionContractOriginal($id, $hash, $pdf = false, $mail = false, $return = false) {
		// TODO Понять зачем надо создавать новый пакет, реально нет никакого в этом смысла
		$package = Package::model()->findByPk($id) or $package = new Package();
		if (md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
			$content = $this->renderPartial('contractOriginal', array(
				'id'=>$id,'package'=>$package,'hash'=>$hash
			), true);
			if ($pdf) {
				if($return) {
					echo $this->doPDFEx($content);	
					return;
				} else {
					$this->doPDF($content, $mail);
				}
			}
			$this->renderPartial('webpage', array('id'=>$id,'package'=>$package,'hash'=>$hash,'content'=>$content));
		} else {
			throw new CHttpException(403, 'Неправильный ключ');
		}
	}
	
	/**
	 * Метод генерирует договор (с печатями)
	 * @param object $id
	 * @param object $hash
	 * @return
	 */
	public function actionContract($id, $hash, $pdf = false, $mail = false, $return = false) {
		// TODO Понять зачем надо создавать новый пакет, реально нет никакого в этом смысла
		if($package = Package::model()->findByPk($id)) {
			if(md5('sAlT'.$package->client_id.'pEpPeR') == $hash) {
				$content = $this->renderPartial('contract', array(
					'id'=>$id,'package'=>$package,'hash'=>$hash
				), true);
				if ($pdf) {
					if($return) {
						echo $this->doPDFEx($content);	
						return;
					} else {
						$this->doPDF($content, $mail);
					}
				}
				$this->renderPartial('webpage', array('id'=>$id,'package'=>$package,'hash'=>$hash,'content'=>$content));
				return;
			}
		}
		throw new CHttpException(403, 'Неправильный ключ'); 
	}
}
