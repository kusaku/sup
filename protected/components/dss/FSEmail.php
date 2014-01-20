<?php
/**
 * Класс обеспечивает отправку документов как email
 */
 
abstract class FSEmail extends FSDocument implements IDocumentHtmlResult, IDocumentTextResult {
	public function send($email,$name,$emailFrom=false,$nameFrom=false) {
		//Если заполнил почту и новый, отсылаем приветственное письмо
		$post = new stdClass();
		$obAPIModule=Yii::app()->getModule('api');
		$post->from = !$emailFrom?$obAPIModule->MailParams['emailFrom']:$emailFrom;
		$post->fromname = !$nameFrom?$obAPIModule->MailParams['emailNameFrom']:$nameFrom;
		$post->to = $email;
		$post->toname = $name;
		$post->subject = $this->getTitle(); 
		$post->body = $this->_getAsHtml();
		$post->altBody= strip_tags($post->body);
		// пробуем отправить
		try {
			PHPMail::send($post);
			if($arResult=PHPMail::getLastMail()) {
				$sContent=join("\n\n",$arResult);
				$this->store($sContent,'raw');
			}
		} catch(exception $e) {}
	}
	
	public function getTitle() {
		return 'Уведомление от фабрики-сайтов';
	}
	
	protected function _getAsHtml() {
		return $this->getAsHtml();
	}
	
    /**
     * Метод возвращает письмо в виде HTML
     */
    public function getAsHtml() {
        if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'html'))) {
            return $this->obStorage->loadDocumentFromDisk($obOldDocument);
        }
        return $this->_getAsHtml(); 
    }
    
	protected function _getAsText() {
		return $this->getAsText();
	}
	
	public function getAsText() {
		return strip_tags($this->getAsHtml());
	}
	
	public function getAsFile() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'raw'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		}
		return false; 
	}
} 