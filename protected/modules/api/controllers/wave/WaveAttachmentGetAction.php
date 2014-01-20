<?php
/**
 * Класс выполняет обработку функции Wave
 */
class WaveAttachmentGetAction extends ApiUserWaveAction implements IApiGetAction {
	public function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['key']) || !isset($_REQUEST['id'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		$this->checkAccess();
		
		try {
			//Определяем обсуждение или создаём новое связанное с ключём
			$obWave=Waves::model()->findByAttributes(array('text_ident'=>$_REQUEST['key']));
			if(!$obWave) {
				throw new ApiException(1,'Wave not found '.$_REQUEST['key']);
			}
			$obDb=Yii::app()->getDb();
			$obCommand=$obDb->createCommand('SELECT author_id FROM `wave_posts` WHERE `wave_id`='.$obWave->id.' GROUP BY `author_id`');
			$arAuthorIds=$obCommand->queryColumn();
			
			$obToken=$this->getController()->getModule()->getApplicationTokens();
			$iUserId=$obToken->getUserId();
			
			if(!in_array($iUserId,$arAuthorIds)) {
				//Если я не автор сообщений в данной ветке, надо попробовать поискать меня по индексу обсуждения, вдруг это проект
				if(!$this->checkAccessByKey($obWave->text_ident)) {
					throw new ApiException(2,'No access');
				}
			}
			
			$obCriteria=new CDbCriteria();
			$obCriteria->addCondition('id='.intval($_REQUEST['id']));
			$obCriteria->addCondition('wave_id='.$obWave->id);
			$obAttachment=WaveAttachments::model()->find($obCriteria);
			if(!$obAttachment)
				throw new ApiException(3,'Attachment not found');
			if($obAttachment->type=='file') {
				header('x-sup-wave-filename: '.$obAttachment->file->filename);
				header('x-sup-wave-filesize: '.$obAttachment->file->getFileSize());
				header('x-sup-wave-filemime: '.$obAttachment->file->getMime());
				echo $obAttachment->file->loadFromDisk();
			} else {
				header('x-sup-wave-filename: '.$obAttachment->title);
				$obDocumentsAPI=Yii::app()->getComponent('documents');
				$arInfo=$obDocumentsAPI->getDocumentFileInfo($obAttachment->document);
				header('x-sup-wave-filesize: '.$arInfo['size']);
				header('x-sup-wave-filemime: '.$arInfo['mime']);
				echo $obDocumentsAPI->loadDocumentFromDisk($obAttachment->document);
			}
			$obWave->ping($iUserId);
		} catch(exception $e) {
			if($e->getCode()==3 || $e->getCode()==40)
				throw new CHttpException(404,'File not found',404);
			elseif($e->getCode()==2)
				throw new CHttpException(403,'Access denied',403);
			else 
				throw new CHttpException(500,$e->getMessage());//'Package update error');
		}
	}
}
