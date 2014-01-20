<?php
/**
 * Виджет обеспечивает генерацию меню ссылок представленных в тексте сообщения
 */

class NoticeActionMenuWidget extends CWidget {
	public $message;

	public function run() {
		if(preg_match_all('#(<a.*</a>)#iU',$this->message,$matches)) {
			$arList=array();
			$bReplace=false;
			$arSearch='';
			$arReplace='';
			if(isset(Yii::app()->params['replaceUrl']) && is_array(Yii::app()->params['replaceUrl'])) {
				$bReplace=true;
				$arSearch=array_keys(Yii::app()->params['replaceUrl']);
				$arReplace=array_values(Yii::app()->params['replaceUrl']);
			}
			foreach($matches[1] as $sLink) {
				if($bReplace) {
					$sLink=str_replace($arSearch,$arReplace,$sLink);
				}
				$arList[]=$sLink;
			}
			$this->render('NoticeActionMenu',array('list'=>$arList));
		}
	}
}