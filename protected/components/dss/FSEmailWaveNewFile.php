<?php
/**
 * Класс обеспечивает подготовку и отправку письма о регистрации пользователя
 */

class FSEmailWaveNewFile extends FSEmail {
    protected $sType='emailWaveNewFile';
    protected $obUser;
    protected $obPackage;
    
    function __construct($obStorage,$obPackage) {
        parent::__construct($obStorage);
        $this->obPackage=$obPackage;
        $this->obUser=$this->obPackage->client;
    }
    
    function getTitle() {
        return 'Новый файл по заказу '.$this->obPackage->getNumber();
    }
    
    /**
     * Метод возвращает контроллер ответственный за отрисовку документа
     */
    protected function getController() {
        $arResult=Yii::app()->createController('documents/email/wavenewfile');
        if(is_array($arResult)) {
            return $arResult[0];
        }
        return false;
    }
    
    /**
     * Вообще странно, хэши будут часто совпадать, но если этого не сделать - они никогда не совпадут
     */
    public function getHash() {
        if($this->sHash=='')
            $this->setHash(md5($this->obUser->id.'|'.$this->obUser->mail.'|'.$this->getTitle().'|'.$this->obPackage->id.'|'.time()));
        return $this->sHash;
    }
    
    /**
     * Метод выполняет генерацию текста письма и его сохранение как нового документа.
     */
    public function _getAsHtml() {
        if($obController=$this->getController()) {
            $obController->createAction('wavenewfile')->runWithParams(array('user'=>$this->obUser,'package'=>$this->obPackage));
            $sContent=$obController->getOutput();
            $this->store($sContent,'html');
            return $sContent;
        }
        throw new Exception(20,'SYSTEM_DOCUMENT_TEMPLATE_NOT_FOUND');
    }
        
    /**
     * Метод выполняет сохранение документа в хранилище. При этом также создаются связи между документом и пакетом, а также
     * клиентом и менеджером.
     * @param $sContent - содержимое документа
     * @param $sFormat - формат документа
     * @return Documents - объект описывающий метаданные документа
     */
    protected function store($sContent,$sFormat='html') {
        if($obDocMeta=parent::store($sContent,$sFormat)) {
            $this->obStorage->linkToPeople($obDocMeta,$this->obUser->id);
            $this->obStorage->linkToPackage($obDocMeta,$this->obPackage->id);
        }
        return $obDocMeta;
    }
}
