<?php 

class Docs2Controller extends Controller {
	public $layout='documents';
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
					'index','view','contract','contractOriginal','bill','receipt','act','preview','download'
				),'roles'=>array(
					'admin','moder','topmanager','manager','leadmaster','master','marketolog'
				)
			),array(
				'deny','users'=>array(
					'*'
				)
			)
		);
	}
	
	/**
	 * Метод выводит индексную страницу системы ведения документов. Не должен ничего отображать
	 */
	public function actionIndex() {
		$this->redirect(Yii::app()->homeUrl);
	}
	
	/**
	 * Метод выводит обзорную страницу документов
	 * @param integer $id - номер заказа документы которого необходимо посмотреть или сгенерировать
	 */
	public function actionView($id) {
		setlocale(LC_ALL, 'ru_RU');
		$obPackage=Package::model()->findByPk(intval($id));
		if(!$obPackage)
			throw new CHttpException(404,'Not found',404);
		$arHistory=array();
		if($arDocuments=$obPackage->documents) {
			foreach($arDocuments as $obDocument) {
				if(!array_key_exists($obDocument->type.'|'.$obDocument->md5summ, $arHistory)) {
					$arHistory[$obDocument->type.'|'.$obDocument->md5summ]=$obDocument->attributes;
					$arHistory[$obDocument->type.'|'.$obDocument->md5summ]['formats'][$obDocument->storage_format]=$obDocument->attributes;
				} else {
					$arHistory[$obDocument->type.'|'.$obDocument->md5summ]['formats'][$obDocument->storage_format]=$obDocument->attributes;
				}
			}
			usort($arHistory,array($this,'_sortHistory'));
		}
		$this->render('view',array('package'=>$obPackage,'history'=>$arHistory));
	}
	
	/**
	 * Действие обеспечивает генерацию подписанного договора
	 */
	public function actionContract($id,$documentId=false) {
		$obPackage=Package::model()->findByPk(intval($id));
		if(!$obPackage)
			throw new CHttpException(404,'Not found',404);
		$obDocuments=Yii::app()->getComponent('documents');
		if(!$documentId) {
			$obContract=$obDocuments->createContract($obPackage);
			$obContract->getAsPdf();
			$obDetails=$obDocuments->createContractDetailsApplication($obPackage);
			$obDetails->setContract($obContract);
			$obDetails->getAsPdf();
			$obApplication=$obDocuments->getModel()->findByAttributes(
				array('md5summ'=>$obDetails->getHash(),'type'=>$obDetails->getType(),'storage_format'=>'html')
			);
			$obFullContract=$obDocuments->createFullContract($obPackage,array($obApplication));
		} else {
			if($obDocument=$obDocuments->getModel()->findByPk($documentId)) {
				$obFullContract=$obDocuments->createFullContract($obPackage);
				$obFullContract->setHash($obDocument->md5summ);
			} else {
				throw new CHttpException(404,'Not found',404);
			}
		}
		if(isset($_GET['format']) && $_GET['format']=='pdf') {
			$sContent=$obFullContract->getAsPdf();
			if(isset($_GET['download']) && $_GET['download']==1) {
				$this->_uploadPdf($sContent, 'contract_'.$obPackage->getNumber().'.pdf');
			} else {
				$this->_printPdf($sContent, 'contract_'.$obPackage->getNumber().'.pdf');
			}
			Yii::app()->end();
		}
		$sContract=$obFullContract->getAsHtml();
		$this->render('contract',array('package'=>$obPackage,'contract'=>$sContract));
	}

	/**
	 * Действие обеспечивает генерацию не подписанного договора
	 */
	public function actionContractOriginal($id,$documentId=false) {
		$obPackage=Package::model()->findByPk(intval($id));
		if(!$obPackage)
			throw new CHttpException(404,'Not found',404);
		$obDocuments=Yii::app()->getComponent('documents');
		if(!$documentId) {
			$obContract=$obDocuments->createContractOriginal($obPackage);
			$obContract->getAsPdf();
			$obDetails=$obDocuments->createContractDetailsApplicationOriginal($obPackage);
			$obDetails->setContract($obContract);
			$obDetails->getAsPdf();
			$obApplication=$obDocuments->getModel()->findByAttributes(
				array('md5summ'=>$obDetails->getHash(),'type'=>$obDetails->getType(),'storage_format'=>'html')
			);
			$obFullContract=$obDocuments->createFullContractOriginal($obPackage,array($obApplication));
		} else {
			if($obDocument=$obDocuments->getModel()->findByPk($documentId)) {
				$obFullContract=$obDocuments->createFullContractOriginal($obPackage);
				$obFullContract->setHash($obDocument->md5summ);
			} else {
				throw new CHttpException(404,'Not found',404);
			}
		}
		if(isset($_GET['format']) && $_GET['format']=='pdf') {
			$sContent=$obFullContract->getAsPdf();
			if(isset($_GET['download']) && $_GET['download']==1) {
				$this->_uploadPdf($sContent, 'contract_'.$obPackage->getNumber().'_not_signed.pdf');
			} else {
				$this->_printPdf($sContent, 'contract_'.$obPackage->getNumber().'_not_signed.pdf');
			}
			Yii::app()->end();
		}
		$sContract=$obFullContract->getAsHtml();
		$this->render('contract',array('package'=>$obPackage,'contract'=>$sContract));
	}

	/**
	 * Действие обеспечивает генерацию не подписанного договора
	 */
	public function actionBill($id,$documentId=false) {
		$obPackage=Package::model()->findByPk(intval($id));
		if(!$obPackage)
			throw new CHttpException(404,'Not found',404);
		$obDocuments=Yii::app()->getComponent('documents');
		if(!$documentId) {
			$obBill=$obDocuments->createInvoice($obPackage);
		} else {
			if($obDocument=$obDocuments->getModel()->findByPk($documentId)) {
				$obBill=$obDocuments->createInvoice($obPackage);
				$obBill->setHash($obDocument->md5summ);
			} else {
				throw new CHttpException(404,'Not found',404);
			}
		}
		if(isset($_GET['format']) && $_GET['format']=='pdf') {
			$sContent=$obBill->getAsPdf();
			if(isset($_GET['download']) && $_GET['download']==1) {
				$this->_uploadPdf($sContent, 'bill_'.$obPackage->getNumber().'.pdf');
			} else {
				$this->_printPdf($sContent, 'bill_'.$obPackage->getNumber().'.pdf');
			}
			Yii::app()->end();
		}
		$sContract=$obBill->getAsHtml();
		$this->render('contract',array('package'=>$obPackage,'contract'=>$sContract));
	}
	
	/**
	 * Действие обеспечивает генерацию не подписанного договора
	 */
	public function actionReceipt($id,$documentId=false) {
		$obPackage=Package::model()->findByPk(intval($id));
		if(!$obPackage)
			throw new CHttpException(404,'Not found',404);
		$obDocuments=Yii::app()->getComponent('documents');
		if(!$documentId) {
			$obBill=$obDocuments->createReceipt($obPackage);
		} else {
			if($obDocument=$obDocuments->getModel()->findByPk($documentId)) {
				$obBill=$obDocuments->createReceipt($obPackage);
				$obBill->setHash($obDocument->md5summ);
			} else {
				throw new CHttpException(404,'Not found',404);
			}
		}
		if(isset($_GET['format']) && $_GET['format']=='pdf') {
			$sContent=$obBill->getAsPdf();
			if(isset($_GET['download']) && $_GET['download']==1) {
				$this->_uploadPdf($sContent, 'receipt_'.$obPackage->getNumber().'.pdf');
			} else {
				$this->_printPdf($sContent, 'receipt_'.$obPackage->getNumber().'.pdf');
			}
			Yii::app()->end();
		}
		$sContract=$obBill->getAsHtml();
		$this->render('contract',array('package'=>$obPackage,'contract'=>$sContract));
	}
	
	/**
	 * Действие обеспечивает генерацию не подписанного договора
	 */
	public function actionAct($id,$documentId=false) {
		$obPackage=Package::model()->findByPk(intval($id));
		if(!$obPackage)
			throw new CHttpException(404,'Not found',404);
		$obDocuments=Yii::app()->getComponent('documents');
		if(!$documentId) {
			$obAct=$obDocuments->createAct($obPackage);
		} else {
			if($obDocument=$obDocuments->getModel()->findByPk($documentId)) {
				$obAct=$obDocuments->createAct($obPackage);
				$obAct->setHash($obDocument->md5summ);
			} else {
				throw new CHttpException(404,'Not found',404);
			}
		}
		if(isset($_GET['format']) && $_GET['format']=='pdf') {
			$sContent=$obAct->getAsPdf();
			if(isset($_GET['download']) && $_GET['download']==1) {
				$this->_uploadPdf($sContent, 'act_'.$obPackage->getNumber().'.pdf');
			} else {
				$this->_printPdf($sContent, 'act_'.$obPackage->getNumber().'.pdf');
			}
			Yii::app()->end();
		}
		$sContract=$obAct->getAsHtml();
		$this->render('contract',array('package'=>$obPackage,'contract'=>$sContract));
	}
	
	/**
	 * Действие обеспечивает загрузку и отображение сохранённого документа
	 */
	public function actionPreview($id) {
		$obDocuments=Yii::app()->getComponent('documents');
		if($obDocument=$obDocuments->getModel()->findByPk($id)) {
			if(in_array($obDocument->storage_format,array('pdf','txt','html','image'))) {
				if($obDocument->storage_format=='pdf') {
				 	$this->_printPdf($obDocuments->loadDocumentFromDisk($obDocument),$obDocument->type.'_'.$obDocument->id.'.pdf');
				} elseif($obDocument->storage_format=='html' || $obDocument->storage_format=='txt') {
					$arResult=array(
						'document'=>$obDocument
					);
					if($obDocument->packages) {
						$arResult['package']=$obDocument->packages[0];
					} else {
						$arResult['package']=false;
					}
					$arResult['content']=$obDocuments->loadDocumentFromDisk($obDocument);
					$this->render('preview',$arResult);
				}
			} else {
				throw new CHttpException(500,'Cant display document');
			}
		} else {
			throw new CHttpException(404,'No document found');
		}
	}
	
	/**
	 * Действие обеспечивает загрузку и отображение сохранённого документа
	 */
	public function actionDownload($id) {
		$obDocuments=Yii::app()->getComponent('documents');
		if($obDocument=$obDocuments->getModel()->findByPk($id)) {
			$sData=$obDocuments->loadDocumentFromDisk($obDocument);
			if (headers_sent())
				throw new CHttpException(500,Yii::t('sup','Some data has already been output to browser, can\'t send file'));
			header('Content-Description: File Transfer');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Pragma: public');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream', false);
			header('Content-Type: application/download', false);
			if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
				// don't use length if server using compression
				header('Content-Length: '.strlen($sData));
			}
			header('Content-disposition: attachment; filename="'.$obDocument->type.'_'.$obDocument->id.'.'.$obDocument->storage_format.'"');
			echo $sData;
		} else {
			throw new CHttpException(404,'No document found');
		}
	}
	
	private function _sortHistory($a,$b) {
		return -strtotime($a['date_create'])+strtotime($b['date_create']);
	}
	
	/**
	 * Метод подготавливает заголовки и отправляет их как для печати файла
	 */
	private function _printPdf($sData,$sFilename) {
		//Download file
		if(headers_sent())
			throw new CHttpException(500,Yii::t('sup','Some data has already been output to browser, can\'t send PDF file'));
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			// don't use length if server using compression
			header('Content-Length: '.strlen($sData));
		}
		header('Content-Type: application/pdf');
		header('Content-disposition: inline; filename="'.$sFilename.'"');
		header('Cache-Control: public, must-revalidate, max-age=0'); 
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		echo $sData;
	}
	
	/**
	 * Метод отправляет заголовки и PDF как файл
	 */
	private function _uploadPdf($sData,$sFilename) {
		header('Content-Description: File Transfer');
		if (headers_sent())
			throw new CHttpException(500,Yii::t('sup','Some data has already been output to browser, can\'t send PDF file'));
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream', false);
		header('Content-Type: application/download', false);
		header('Content-Type: application/pdf', false);
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			// don't use length if server using compression
			header('Content-Length: '.strlen($sData));
		}
		header('Content-disposition: attachment; filename="'.$sFilename.'"');
		echo $sData;
	}
}

