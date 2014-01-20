<?php
/**
 * Класс выполняет обработку функции WaveAdd
 */
class WaveUpdateAction extends ApiUserAction implements IApiPostAction {
    public function run() {
        $this->_checkProtocolRequirements();
        
        if(!isset($_REQUEST['key']) || !isset($_REQUEST['content']) || !isset($_REQUEST['id'])) {
            throw new CHttpException(400,'Bad request',400);
        }
        $this->checkAccess();
        
        $obToken=$this->getController()->getModule()->getApplicationTokens();
        $iUserId=$obToken->getUserId();
        if($iUserId==0)
            throw new CHttpException(403,'Auth required',403);
        
        $obTransaction=Waves::model()->dbConnection->beginTransaction();
        try {
            //Определяем обсуждение или создаём новое связанное с ключём
            $obWave=Waves::model()->findByAttributes(array('text_ident'=>$_REQUEST['key']));
            if(!$obWave) {
                throw new ApiException(2,'Wave not found');
            }
            $obPost=WavePosts::model()->findByAttributes(array('wave_id'=>$obWave->id,'id'=>$id));
            if(!$obPost) {
                throw new ApiException(3,'Post not found');
            }
            if($iUserId!=$obPost->author_id) {
                throw new ApiException(4,'Not allowed');
            }
            $obPost->addContent($iUserId,$_POST['content']);
            if($obTransaction->active) $obTransaction->commit();
            $arResult=array(
                'result'=>200,
                'resultText'=>'ok',
                'data'=>$obPost->getAsArray()
            );
            $this->getController()->render('json',array('data'=>$arResult));
        } catch(ApiException $e) {
            if($obTransaction->active) $obTransaction->rollBack();
            throw $e;
        } catch(exception $e) {
            if($obTransaction->active) $obTransaction->rollBack();
            throw new ApiException(1,$e->getMessage());//'Package update error');
        }
    }
}
