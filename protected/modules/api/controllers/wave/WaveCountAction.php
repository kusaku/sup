<?php
/**
 * Класс выполняет обработку функции WaveCount
 */
class WaveCountAction extends ApiUserAction implements IApiGetAction {
	public function run() {
		$this->_checkProtocolRequirements();

		if(!isset($_REQUEST['key'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		$this->checkAccess();

		try {
			//Определяем обсуждение или создаём новое связанное с ключём
			/**
			 * @var Waves $obWave
			 */
			$obWave=Waves::model()->findByAttributes(array('text_ident'=>$_REQUEST['key']));
			if(!$obWave) {
				throw new ApiException(1,'Wave not found '.$_REQUEST['key']);
			}
			$arResult=array();
			$bGetFiles=true;
			if(isset($_REQUEST['getFiles']) && $_REQUEST['getFiles']=='N')
				$bGetFiles=false;
			if($bGetFiles) {
				$obAttachmentCondition=new CDbCriteria();
				$obAttachmentCondition->order='date_add desc';
				if(isset($_REQUEST['fromFileId'])) {
					$obAttachmentCondition->addCondition('id>'.intval($_REQUEST['fromFileId']));
				}
				if(isset($_REQUEST['userId'])) {
					$obAttachmentCondition->addCondition('author_id='.intval($_REQUEST['userId']));
				}
				$obAttachmentCondition->addCondition('wave_id='.$obWave->id);
				$arResult['attachments']=WaveAttachments::model()->count($obAttachmentCondition);
			}

			$obCondition=new CDbCriteria();
			$obCondition->addCondition('wave_id='.$obWave->id);
			if(isset($_REQUEST['userId'])) {
				$obCondition->addCondition('author_id='.intval($_REQUEST['userId']));
			}
			$arResult['posts']=WavePosts::model()->count($obCondition);
			//Отмечаем запрос на получение сообщений
			$obToken=$this->getController()->getModule()->getApplicationTokens();
			$obWave->ping($obToken->getUserId());
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$arResult
			);
			$this->getController()->render('json',array('data'=>$arResult));
		} catch(ApiException $e) {
			throw $e;
		} catch(exception $e) {
			throw new ApiException($e->getCode(),$e->getMessage());//'Package update error');
		}
	}
}
