<?php
/**
 * Модель обеспечивающая хранение файлов приложений комментариев
 */

class WaveAttachmentFile extends CActiveRecord {
	const STORAGE_PATH='/data/waveuploads';
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'wave_attachment_file';
	}

	public function relations() {
		return array(
			'attachment'=>array(
				self::BELONGS_TO,'WaveAttachments','wave_attachment_id'
			),
		);
	}
	
	/**
	 * Функция сохраняет указанный файл на диск и связывает его с текущей записью
	 */
	function saveOnDisk($tmpPath) {
		if(!file_exists($tmpPath) || !is_file($tmpPath))
			throw new Exception('Not a file: '.$tmpPath);
		$sFilehash=md5_file($tmpPath);
		$sDirpart=self::STORAGE_PATH.'/'.substr($sFilehash,0,3);
		$sDirname=Yii::app()->getBasePath().$sDirpart;
		if(!file_exists($sDirname)) {
			mkdir($sDirname,0755,true);
		}
		if(file_exists($sDirname) && is_dir($sDirname)) {
			$this->storage_filename=$sDirpart.'/'.$sFilehash;
			if(@copy($tmpPath,$sDirname.'/'.$sFilehash)) {
				$this->save();
				return true;
			}
			throw new Exception('File copy error');
		}
		throw new Exception(30,'SYSTEM_FILE_SAVE_ERROR');
	}
	
	function loadFromDisk() {
		$sFilename=Yii::app()->getBasePath().$this->storage_filename;
		if(file_exists($sFilename)) {
			return file_get_contents($sFilename);
		}
		throw new Exception(40,'SYSTEM_FILE_LOAD_ERROR');
	}

	function getFileSize() {
		$sFilename=Yii::app()->getBasePath().$this->storage_filename;
		if(file_exists($sFilename)) {
			return filesize($sFilename);
		}
		throw new Exception(40,'SYSTEM_FILE_LOAD_ERROR');
	}
	
	function getMime() {
		$sFilename=Yii::app()->getBasePath().$this->storage_filename;
		if(file_exists($sFilename)) {
			return CFileHelper::getMimeType($sFilename);
		}
		throw new Exception(40,'SYSTEM_FILE_LOAD_ERROR');
	}
}