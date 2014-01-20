<?php
/**
 * Класс выполняет обработку функции Wave
 */
class WaveAction extends ApiUserAction implements IApiGetAction {
	public function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_REQUEST['key'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		$this->checkAccess();
		
		try {
			//Определяем обсуждение или создаём новое связанное с ключём
			$obWave=Waves::model()->findByAttributes(array('text_ident'=>$_REQUEST['key']));
			if(!$obWave) {
				throw new ApiException(1,'Wave not found '.$_REQUEST['key']);
			}
			$arAuthors=array($obWave->author_id=>Waves::getUserArray($obWave->author));
			$bGetFiles=true;
			if(isset($_REQUEST['getFiles']) && $_REQUEST['getFiles']=='N')
				$bGetFiles=false;
			if($bGetFiles) {
				$obAttachmentCondition=new CDbCriteria();
				$obAttachmentCondition->order='date_add desc';
				if(isset($_REQUEST['fromFileId']))
					$obAttachmentCondition->addCondition('id>'.intval($_REQUEST['fromFileId']));
				$obAttachmentCondition->addCondition('wave_id='.$obWave->id);
				$arAttachments=WaveAttachments::model()->findAll($obAttachmentCondition);
			}
			$arResult=array(
				'id'=>$obWave->id,
				'text_ident'=>$obWave->text_ident,
				'author'=>$obWave->author_id,
				'posts'=>array(),
				'attachments'=>array()
			);
			$obCondition=new CDbCriteria();
			$obCondition->addCondition('wave_id='.$obWave->id);
			$sSortField='date_add';
			if(isset($_REQUEST['sortDir']) && $_REQUEST['sortDir']=='desc') {
				$obCondition->order=$sSortField.' desc';
			} else {
				$obCondition->order=$sSortField.' asc';
			}
			if(isset($_REQUEST['limit'])) {
				$obCondition->limit=intval($_REQUEST['limit']);
			}
			if(isset($_REQUEST['offset'])) {
				$obCondition->offset=intval($_REQUEST['offset']);
			}
			if(isset($_REQUEST['userId'])) {
				$obCondition->addCondition('author_id='.intval($_REQUEST['userId']));
			}
			if(isset($_REQUEST['fromId'])) {
				$obCondition->addCondition('id>'.intval($_REQUEST['fromId']));
			}
			/**
			 * @var $arMessages WavePosts[]
			 */
			$arMessages=WavePosts::model()->findAll($obCondition);
			foreach($arMessages as $obPost) {
				$arRow=$obPost->getAsArray(WavePosts::GET_ATTACHMENTS);
				if(!isset($arAuthors[$arRow['author']['id']])) {
					$arAuthors[$arRow['author']['id']]=$arRow['author'];
				}
				$arRow['author']=$arRow['author']['id'];
				if($bGetFiles) {
					$arPostAttachmentIds=array();
					foreach($arRow['attachments'] as $arAtt) {
						$arPostAttachmentIds[]=$arAtt['id'];
					}
					$arRow['attachments']=$arPostAttachmentIds;
				}
				$arResult['posts'][]=$arRow;
			}
			if($bGetFiles) {
				foreach($arAttachments as $obAttachment) {
					$arResult['attachments'][]=array(
						'id'=>$obAttachment->id,
						'title'=>$obAttachment->title,
						'icon'=>$obAttachment->icon,
						'author'=>$obAttachment->author_id,
						'date_add'=>date('c',strtotime($obAttachment->date_add))
					);
					if(!isset($arAuthors[$obAttachment->author_id])) {
						$arAuthors[$obAttachment->author_id]=Waves::getUserArray($obAttachment->author);
					}
				}
			}
			//Отмечаем запрос на получение сообщений
			$obToken=$this->getController()->getModule()->getApplicationTokens();
			$obWave->ping($obToken->getUserId());
			//Проверяем наличие данных
			if(count($arResult['posts'])==0 && count($arResult['attachments'])==0) {
				$arResult='';
			} else {
				$arResult['authors']=array_values($arAuthors);
			}
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
