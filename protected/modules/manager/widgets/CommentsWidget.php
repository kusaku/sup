<?php
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 */

class CommentsWidget extends CWidget {
	public $key;
	private $obWave;
	
	public function init() {
     	$this->obWave=Waves::model()->findByAttributes(array('text_ident'=>$this->key));
		if(!$this->obWave) {
			$this->obWave=new Waves();
			$this->obWave->text_ident=$this->key;
		}   
    }
 
    public function run() {
    	$arResult=array(
    		'wave'=>$this->obWave,
    		'posts'=>false,
    		'attachments'=>false
		);
		if($this->obWave->messages) {
			$arResult['posts']=array();
			foreach($this->obWave->messages as $obPost) {
				$arResult['posts'][]=$obPost->getAsArray();
			}
		}
		if($this->obWave->attachments) {
			$arResult['attachments']=array();
			foreach($this->obWave->attachments as $obAttachment) {
				$arRow=$obAttachment->attributes;
				if($arRow['type']=='document')
					$arRow['document']=$obAttachment->document->attributes;
				$arRow['author']=Waves::getUserArray($obAttachment->author);
				$arResult['attachments'][]=$arRow;
			}
		}
    	$this->render('Comments',$arResult);
    }
}
