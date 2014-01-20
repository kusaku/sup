<?php
/**
 * Модель обеспечивающая управление сообщениями комментариев
 * @property Waves $wave
 * @property WavePostContent $message
 * @property WavePostContent[] $contents
 * @property People $author
 */
class WavePosts extends CActiveRecord {
	const TMP_STORAGE='/runtime/wave';
	const GET_WAVE=1;
	const GET_ATTACHMENTS=2;
	
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'wave_posts';
	}

	public function relations() {
		return array(
			'wave'=>array(
				self::BELONGS_TO,'Waves','wave_id'
			), 'message'=>array(
				self::HAS_ONE,'WavePostContent','post_id','condition'=>'active=1'
			), 'contents'=>array(
				self::HAS_MANY,'WavePostContent','post_id'
			),  'author'=>array(
				self::BELONGS_TO,'People','author_id'
			), 
		);
	}
	
	/**
	 * Метод выполняет добавление текста сообщения
	 */
	public function addContent($user_id,$content) {
		$obContent=new WavePostContent();
		$obContent->post_id=$this->id;
		$obContent->date_add=date('Y-m-d H:i:s');
		$obContent->content=$content;
		$obContent->author_id=$user_id;
		$arContents=$this->contents;
		$iLastVersion=0;
		if(is_array($arContents) && count($arContents)>0) {
			foreach($arContents as $obCont)
				if($iLastVersion<$obCont->version) $iLastVersion=$obCont->version;
		}
		$obContent->version=$iLastVersion+1;
		WavePostContent::model()->updateAll(array('active'=>0),array('condition'=>'active=1 AND post_id='.$this->id));
		$obContent->active=1;
		$obContent->save();
		return $obContent->id;
	}
	
	/**
	 * Метод возвращает текущее сообщение в виде массива
	 */
	public function getAsArray($params=3) {
		$arResult=array(
			'id'=>$this->id,
			'content'=>htmlspecialchars($this->message->content,ENT_COMPAT,'utf-8',false),
			'author'=>Waves::getUserArray($this->author),
			'date_add'=>date('c',strtotime($this->date_add)),
			'version'=>$this->message->version,
		);
		if(($params & WavePosts::GET_WAVE)==WavePosts::GET_WAVE) {
			$arResult['wave']=array(
				'id'=>$this->wave->id,
				'date_add'=>date('c',strtotime($this->wave->date_add)),
				'date_edit'=>date('c',strtotime($this->wave->date_edit)),
				'text_ident'=>$this->wave->text_ident,
			);
		}
		if(($params & WavePosts::GET_ATTACHMENTS)==WavePosts::GET_ATTACHMENTS) {
			$arResult['attachments']=array();
			if($this->message->attachments) {
				foreach($this->message->attachments as $obAttachment) {
					$arResult['attachments'][]=array(
						'id'=>$obAttachment->id,
						'title'=>$obAttachment->title,
						'icon'=>$obAttachment->icon,
						'author'=>Waves::getUserArray($obAttachment->author),
						'date_add'=>date('c',strtotime($obAttachment->date_add))
					);
				}
			}
		}
		return  $arResult;
	}

	/**
	 * Метод выполняет добавление файла к обсуждению. Файл добавляется потоком
	 * @todo Реализовать метод
	 */
	public function uploadFileStream($sFilename,$rFile) {
		$sTmpName=time().'_'.md5($sFilename).rand(0,10000).'.tmp';
		$sDirname=Yii::app()->getBasePath().self::TMP_STORAGE;
		if(!file_exists($sDirname)) {
			mkdir($sDirname,0755,true);
		}
		if(file_exists($sDirname) && is_dir($sDirname)) {
			$rOutfile=fopen($sDirname.'/'.$sTmpName,'w');
			while($sLine=fread($rFile,8192)) {
				fwrite($rOutfile,$sLine,strlen($sLine));
			}
			fclose($rOutfile);
			$sType=CFileHelper::getMimeType($sDirname.'/'.$sTmpName);
			$sType=preg_replace('#( .*)#','',$sType);
			$obAttachment=new WaveAttachments();
			$obAttachment->wave_id=$this->wave_id;
			$obAttachment->date_add=date('Y-m-d H:i:s');
			$obAttachment->icon=WaveAttachments::getIconByMime($sType,$sFilename);
			$obAttachment->title=$sFilename;
			$obAttachment->author_id=$this->author_id;
			if($obAttachment->icon=='pdf' || $obAttachment->icon=='html') {
				//Формат допустим в документах
				$obAttachment->type='document';
				$obAttachment->save();
				$obDocuments=Yii::app()->getComponent('documents');
				$obDocument=$obDocuments->createDocumentFile($sFilename,$sDirname.'/'.$sTmpName);
				$obDocumentRecord=$obDocument->storeAndGet();
				$obDocuments->linkToWaveAttachment($obDocumentRecord,$obAttachment->id);
			} else {
				//Просто файл
				$obAttachment->type='file';
				$obAttachment->save();
				$obAttachmentFile=new WaveAttachmentFile();
				$obAttachmentFile->wave_attachment_id=$obAttachment->id;
				$obAttachmentFile->filename=$sFilename;
				$obAttachmentFile->saveOnDisk($sDirname.'/'.$sTmpName);
			}
			//@unlink($sDirname.'/'.$sTmpName);
			//Добавляем связь между файлом и версией сообщения
			$obContent=$this->message;
			$obLink=new WaveAttachmentPostContent();
			$obLink->attachment_id=$obAttachment->id;
			$obLink->post_content_id=$obContent->id;
			$obLink->save();
			return $obAttachment;
		} else {
			throw new Exception('File save error');
		}
	}
}