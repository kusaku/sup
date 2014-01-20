<?php 
class CabinetController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return array
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
					'index','wizzardStatus','WizzardStepData', 'waveAdd','waveUpload','waveGet','waveDownload','waveUpdate','messageTemplates','DesignFormRequest','BriefFormRequest','MessageTemplateAdd','MessageTemplateDelete'
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
	 * Действие обеспечивает отображение статуса заказа
	 * @param $packageId integer
	 *
	 * @throws CHttpException
	 * @return void
	 */
	public function actionIndex($packageId) {
		if($obPackage=Package::model()->findByPk($packageId)) {
			$this->renderPartial('index',array('package'=>$obPackage));
		} else {
			throw new CHttpException(404,'Заказ не найден',404);
		}
	}

	/**
	 * Действие обеспечивает получение состояния мастера к определённому заказу
	 */
	public function actionWizzardStatus($packageId,$time=0) {
		$arResult='';
		try {
			$obCondition=new CDbCriteria();
			$obCondition->addCondition('package_id='.intval($packageId));
			$obWorkflow=PackageWorkflow::model()->find($obCondition);
			if(!$obWorkflow) {
				if($obPackage=Package::model()->findByPk($packageId)) {
					if(!$obPackage->workflow) {
						$obPackage->initWorkflow();
						$obPackage=Package::model()->findByPk($packageId);
					}
					$arResult=array(
						'step_id'=>$obPackage->workflow->step_id
					);
				} else {
					throw new CHttpException(404,'Заказ не найден',404);
				}
			} elseif($obWorkflow->date_ping==NULL || strtotime($obWorkflow->date_ping)>$time) {
				$arResult=array(
					'step_id'=>$obWorkflow->step_id
				);
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		echo json_encode($arResult);
	}

	/**
	 * Действие обеспечивает получение данных по шагу мастера
	 */
	public function actionWizzardStepData($packageId,$stepId) {
		try {
			if($obPackage=Package::model()->findByPk($packageId)) {
				if(!$obPackage->workflow) {
					$obPackage->initWorkflow();
					$obPackage=Package::model()->findByPk($packageId);
				}
				if(is_numeric($stepId)) {
					$stepId++;
					$obStep=PackageWorkflowStep::model()->findByPk($stepId);
				} else {
					$obStep=PackageWorkflowStep::model()->findByAttributes(array('text_ident'=>$stepId));
					if($obStep->primaryKey!=$stepId) {
						throw new CException('Wrong step found');
					}
				}
				if(!$obStep)
					throw new CHttpException(404,'Шаг заказа не найден',404);
				$arResult=array();
				$sMethodName='_getStepData_'.$obStep->text_ident;
				if(method_exists($this, $sMethodName))
					$arResult=$this->$sMethodName($obPackage,$obStep);
				elseif($this->getViewFile('steps/step_'.$obStep->text_ident)) {
					$arResult['content']=$this->renderPartial('steps/step_'.$obStep->text_ident,array('package'=>$obPackage,'step'=>$obStep),true);
				} 
			} else {
				throw new CHttpException(404,'Заказ не найден',404);
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		echo json_encode($arResult);
	}

	private function getPackageByKey($key) {
		if(preg_match('#^package_(\d+).*$#',$key,$matches)) {
			return Package::model()->findByPk(intval($matches[1]));
		}
		return false;
	}

	/**
	 * Действие выполняет добавление записи комментария
	 */
	public function actionWaveAdd() {
		try {
			if(isset($_POST['key']) && isset($_POST['content'])) {
				if($_POST['key']=='')
					throw new Exception('Не указан ключ обсуждения');
				if($_POST['content']=='')
					throw new Exception('Не введён текст сообщения');
				//Определяем обсуждение или создаём новое связанное с ключём
				$obWave=Waves::model()->findByAttributes(array('text_ident'=>$_POST['key']));
				if(!$obWave) {
					$obWave=new Waves();
					$obWave->text_ident=$_POST['key'];
					$obWave->author_id=Yii::app()->user->id;
				}
				$iPostId=$obWave->addPost(Yii::app()->user->id, $_POST['content']);
				$obPost=WavePosts::model()->findByPk($iPostId);
				//Пробуем определить и найти пакет по ключу
				if($obPackage=$this->getPackageByKey($obWave->text_ident)) {
					$obUser=$obPackage->client;
					//Формируем и отправляем письмо
					if(!$obWave->isUserOnline($obPackage->client_id)) {
						try {
							Yii::app()->getComponent('documents')->createEmailWaveNewPost($obPackage,$obPost->getAsArray())->send($obUser->mail, $obUser->fio);
						} catch(exception $e) {}
					}
				}
				$arResult['post']=$obPost->getAsArray();
				$arResult['post']['content']=nl2br(strip_tags($arResult['post']['content']));
			} else {
				throw new Exception('Неверные параметры запроса');
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		echo json_encode($arResult);
	}

	/**
	 * Действие выполняет обновление текста сообщения
	 */
	public function actionWaveUpdate($key,$id) {
		try {
			if(isset($_POST['text']) && $_POST['text']!='') {
				//Определяем обсуждение или создаём новое связанное с ключём
				$obWave=Waves::model()->findByAttributes(array('text_ident'=>$key));
				if(!$obWave) {
					throw new Exception('Обсуждение не найдено');
				}
				/**
				 * @var $obPost WavePosts
				 */
				$obPost=WavePosts::model()->findByAttributes(array('wave_id'=>$obWave->id,'id'=>$id));
				if(!$obPost) {
					throw new Exception('Сообщение не найдено');
				}
				$obPost->addContent(Yii::app()->user->id,$_POST['text']);
				$arResult['post']=$obPost->getAsArray();
				$arResult['post']['content']=nl2br(strip_tags($arResult['post']['content']));
			} else {
				throw new Exception('Неверные параметры запроса');
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		echo json_encode($arResult);
	}

	/**
	 * Действие выполняет загрузку списка сообщений начиная с указанного
	 */
	public function actionWaveGet($key,$fromId=0,$packageId=0) {
		try {
			//Определяем обсуждение или создаём новое связанное с ключём
			/**
			 * @var $obWave Waves
			 */
			$obWave=Waves::model()->findByAttributes(array('text_ident'=>$key));
			$arResult=array();
			if(!$obWave) {

			} else {
				/**
				 * @var $arPosts WavePosts[]
				 */
				$arPosts=$obWave->postsFrom($fromId,Yii::app()->user->id);
				foreach($arPosts as $obPost) {
					$arPost=$obPost->getAsArray();
					$arPost['content']=nl2br(strip_tags($arPost['content']));
					$arResult['posts'][]=$arPost;
				}
				if($packageId>0) {
					$obPackage=Package::model()->findByPk($packageId);
					$arResult['userOnline']=$obWave->isUserOnline($obPackage->client_id);
				}
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		echo json_encode($arResult);
	}

	/**
	 * Действие выполняет добавление файла к записи комментария
	 */
	public function actionWaveUpload() {
		$arResult=array();
		try {
			if(isset($_POST['key']) && isset($_FILES['file'])) {
				if($_POST['key']=='')
					throw new Exception('Не указан ключ обсуждения');
				if($_FILES['file']['error']>0 || $_FILES['file']['size']==0)
					throw new Exception('Ошибка загрузки файла');
				//Определяем обсуждение или создаём новое связанное с ключём
				/**
				 * @var $obWave Waves
				 */
				$obWave=Waves::model()->findByAttributes(array('text_ident'=>$_POST['key']));
				if(!$obWave) {
					throw new Exception('Обсуждение не найдено');
				}
				/**
				 * @var $obPost WavePosts
				 */
				$obPost=WavePosts::model()->findByAttributes(array('wave_id'=>$obWave->id,'author_id'=>Yii::app()->user->id),array('order'=>'date_add DESC'));
				if(!$obPost) {
					throw new Exception('Не удалось найти сообщение к которому можно привязать файлы');
				}
				$fFile=fopen($_FILES['file']['tmp_name'],'r');
				try {
					$obPost->uploadFileStream($_FILES['file']['name'],$fFile);
				} catch(Exception $e) {
					fclose($fFile);
					throw $e;
				}
				fclose($fFile);
				if($obWave->attachments) {
					$arResult['files']=array();
					foreach($obWave->attachments as $obAttachment) {
						$arRow=$obAttachment->attributes;
						if($arRow['type']=='document')
							$arRow['document']=$obAttachment->document->attributes;
						$arRow['author']=Waves::getUserArray($obAttachment->author);
						$arResult['files'][]=$arRow;
					}
				}
				//Пробуем определить и найти пакет по ключу
				if($obPackage=$this->getPackageByKey($obWave->text_ident)) {
					$obUser=$obPackage->client;
					//Формируем и отправляем письмо
					try {
						Yii::app()->getComponent('documents')->createEmailWaveNewFile($obPackage)->send($obUser->mail, $obUser->fio);
					} catch(exception $e) {}
				}
			} else {
				throw new Exception('Неверные параметры запроса');
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		$this->renderPartial('iframeUpload',array('json'=>json_encode($arResult)));
	}

	public function actionMessageTemplates() {
		$arResult=array();
		$arResult['models']=WaveMessageTemplates::model()->findAllByAttributes(array('common'=>1));
		$arResult['mymodels']=WaveMessageTemplates::model()->onlyMy()->findAll();
		$arResult['message']=new MessagesTemplateForm();
		$this->renderPartial('messageTemplates',$arResult);
	}

	public function actionMessageTemplateAdd() {
		if(Yii::app()->request->isPostRequest && isset($_POST['MessagesTemplateForm'])) {
			$obForm=new MessagesTemplateForm('form');
			$obForm->attributes=$_POST['MessagesTemplateForm'];
			if($obForm->validate() && $obForm->save()) {
				echo json_encode(array('ok'=>1));
			} else {
				echo json_encode(array('errors'=>$obForm->getErrors()));
			}
			return;
		}
		throw new CHttpException(400,'Wrong request');
	}

	public function actionMessageTemplateDelete() {
		if(Yii::app()->request->isPostRequest && isset($_POST['id'])) {
			if($obMessage=WaveMessageTemplates::model()->findByAttributes(array('people_id'=>Yii::app()->user->id,'id'=>intval($_POST['id'])))) {
				if($obMessage->delete()) {
					echo json_encode(array('ok'=>1));
				} else {
					echo json_encode(array('errors'=>$obMessage->getErrors()));
				}
				return;
			} else {
				throw new CHttpException(404,'Message template not found');
			}
		}
		throw new CHttpException(400,'Wrong request');
	}

	public function actionDesignFormRequest($packageId) {
		$arResult=array();
		try {
			if($obPackage=Package::model()->findByPk($packageId)) {
				if(!$obPackage->workflow) {
					$obPackage->initWorkflow();
					$obPackage=Package::model()->findByPk($packageId);
				}
				$arData=$obPackage->workflow->getData('design_form');
				if(isset($_REQUEST['act'])) {
					if($_REQUEST['act']=='unlock') {
						$arData['nextStepAllowed']=1;
						if($obPackage->workflow->saveData($arData,'design_form')) {
							$arResult['done']=1;
						}
					} elseif($_REQUEST['act']=='lock') {
						$arData['nextStepAllowed']=0;
						if($obPackage->workflow->saveData($arData,'design_form')) {
							$arResult['done']=1;
						}
					}
				}
			} else {
				throw new CHttpException(404,'Заказ не найден',404);
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		echo json_encode($arResult);
	}

	public function actionBriefFormRequest($packageId) {
		$arResult=array();
		try {
			if($obPackage=Package::model()->findByPk($packageId)) {
				if(!$obPackage->workflow) {
					$obPackage->initWorkflow();
					$obPackage=Package::model()->findByPk($packageId);
				}
				$arData=$obPackage->workflow->getData('brief_form');
				if(isset($_REQUEST['act'])) {
					if($_REQUEST['act']=='unlock') {
						$arData['nextStepAllowed']=1;
						if($obPackage->workflow->saveData($arData,'brief_form')) {
							$arResult['done']=1;
						}
					} elseif($_REQUEST['act']=='lock') {
						$arData['nextStepAllowed']=0;
						if($obPackage->workflow->saveData($arData,'brief_form')) {
							$arResult['done']=1;
						}
					}
				}
			} else {
				throw new CHttpException(404,'Заказ не найден',404);
			}
		} catch(exception $e) {
			$arResult=array(
				'error'=>$e->getCode(),
				'errorMessage'=>$e->getMessage()
			);
		}
		echo json_encode($arResult);
	}

	public function actionWaveDownload($id) {
		$obCriteria=new CDbCriteria();
		$obCriteria->addCondition('id='.intval($id));
		$obAttachment=WaveAttachments::model()->find($obCriteria);
		if(!$obAttachment)
			throw new CHttpException(404,'Attachment not found',404);
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
		if($obAttachment->type=='file') {
			header('Content-Type: '.$obAttachment->file->getMime(), false);
			if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
				// don't use length if server using compression
				header('Content-Length: '.$obAttachment->file->getFileSize());
			}
			header('Content-disposition: attachment; filename="'.$obAttachment->file->filename.'"');
			echo $obAttachment->file->loadFromDisk();
		} else {
			$obDocumentsAPI=Yii::app()->getComponent('documents');
			$arInfo=$obDocumentsAPI->getDocumentFileInfo($obAttachment->document);
			header('Content-Type: '.$arInfo['mime'], false);
			if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
				// don't use length if server using compression
				header('Content-Length: '.$arInfo['size']);
			}
			header('Content-disposition: attachment; filename="'.$obAttachment->title.'"');
			echo $obDocumentsAPI->loadDocumentFromDisk($obAttachment->document);
		}
	}

	private function _getStepData_waiting($obPackage,$obStep) {
		$arResult=array(
			'content'=>$this->renderPartial('steps/step_'.$obStep->text_ident,array('package'=>$obPackage,'step'=>$obStep),true),
			'init'=>'_initWaitingStep'
		);
		return $arResult;
	}

	private function _getStepData_payment_waiting($obPackage,$obStep) {
		$arResult=array(
			'content'=>$this->renderPartial('steps/step_'.$obStep->text_ident,array('package'=>$obPackage,'step'=>$obStep),true),
			'init'=>'_initPaymentWaitingStep'
		);
		return $arResult;
	}

	private function _getStepData_design_form($obPackage,$obStep) {
		$arResult=array(
			'content'=>$this->renderPartial('steps/step_'.$obStep->text_ident,array('package'=>$obPackage,'step'=>$obStep),true),
			'init'=>'_initDesignFormStep'
		);
		return $arResult;
	}

	private function _getStepData_brief_form($obPackage,$obStep) {
		$arResult=array(
			'content'=>$this->renderPartial('steps/step_'.$obStep->text_ident,array('package'=>$obPackage,'step'=>$obStep),true),
			'init'=>'_initBriefFormStep'
		);
		return $arResult;
	}

	private function _getStepData_form_domain($obPackage,$obStep) {
		$arCriteria=array();
		$arCriteria['package_id']=$obPackage->id;
		$obDomainRequestModel=DomainRequest::model();
		$arRequests=$obDomainRequestModel->findAllByAttributes($arCriteria);
		$arResult=array(
			'content'=>$this->renderPartial('steps/step_'.$obStep->text_ident,array('package'=>$obPackage,'requests'=>$arRequests,'step'=>$obStep),true),
			'init'=>'_initFormDomainStep'
		);
		return $arResult;
	}
}

