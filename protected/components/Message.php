<?php

/**
 * Generates flash messages output
 *
 * Using:
 * Set message: Message::setMessage(Message::ERROR, 'messageText');
 *
 * CWebUser children needs this to be added:
 *
 * 	//disable flashes outdating after 2 page reloads
 *	public $autoUpdateFlash = false;
 *
 * @author Anton Andersen
 */
class Message extends CWidget {
	
	const NOTICE = 'notice';
	const ERROR = 'error';
	const SUCCESS = 'success';
	
	/**
	 * which messages to out
	 * @var string all || error || success || notice
	 */
	public $type = 'all';
	public $class = null;

	static function setMessage($type, $value) {
		Yii::app()->user->setFlash($type . '_' . microtime(), $value);
	}

	static function getMessages(){
		$arFlashes = array();
		$flashes = Yii::app()->user->getFlashes();
		foreach($flashes as $key => $message){
			$type = Message::NOTICE;
			if(strpos($key, Message::ERROR) === 0){
				$type = Message::ERROR;
			}
			elseif(strpos($key, Message::SUCCESS) === 0){
				$type = Message::SUCCESS;
			}
			$arFlashes[] = array('type' => $type, 'time' => substr($key,strrpos($key,'_')+1), 'text' => $message);
		}
		return $arFlashes;
	}

	public function run() {
		$arFlashes = Message::getMessages();
		$arOut = array(
			Message::ERROR => '',
			Message::NOTICE => '',
			Message::SUCCESS => '',
		);
		foreach($arFlashes as $arMsg){
			$arOut[$arMsg['type']] .= $arMsg['text'];
		}
		$strOut = '';
		foreach($arOut as $type => $strOut){
			if($strOut != ''){
				$class = 'flash flash-'.$type;
				echo CHtml::tag('ul', array('class'=>$class), $strOut);
			}
		}		
	}
}