<?php
/**
 * Модель обеспечивающая управление приложениями к комментариям
 */

class WaveAttachments extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'wave_attachments';
	}

	public function relations() {
		return array(
			'wave'=>array(
				self::BELONGS_TO,'Waves','wave_id'
			), 'post_contents'=>array(
				self::MANY_MANY,'WavePostContent','wave_attachment_post_content(attachment_id,post_content_id)'
			), 'author'=>array(
				self::BELONGS_TO,'People','author_id'
			), 'document'=>array(
				self::HAS_ONE,'Documents',array('document_id'=>'id'),'through'=>'documentWave'
			), 'documentWave'=>array(
				self::HAS_MANY,'DocumentWaveAttachment','wave_attachment_id'
			), 'file'=>array(
				self::HAS_ONE,'WaveAttachmentFile','wave_attachment_id'
			)
		);
	}

	static public function getIconByMime($sMime,$sFilename=false) {
		switch ($sMime) {
			case 'text/html':
				return 'html';
			break;
			case 'application/pdf':
				if($sFilename!='') {
					if($sType=CFileHelper::getMimeTypeByExtension($sFilename)) {
						switch($sType) {
							case 'application/postscript':
								$sExtension=pathinfo($sFilename,PATHINFO_EXTENSION);
								if($sExtension=='ai') {
									return 'ai';
								}  else {
									return 'eps';
								}
							break;
							default:
								return $sType;
						}
					}
				}
				return 'pdf';
			break;
			case 'application/zip':
				if($sFilename!='') {
					if($sType=CFileHelper::getMimeTypeByExtension($sFilename)) {
						return $sType;
					}
				}
				return 'zip';
			break;
			case 'image/vnd.adobe.photoshop':
				return 'psd';
			break;
			case 'image/tiff':
				return 'tif';
			break;
			case 'application/x-rar':
			case 'application/rar':
				return 'rar';
			break;
			case 'image/png':
				return 'png';
			break;
			case 'image/gif':
				return 'gif';
			break;
			case 'application/vnd.oasis.opendocument.text':
				return 'oodoc';
			break;
			case 'application/msword':
				return 'doc';
			break;
			case 'application/vnd.ms-excel':
				return 'xls';
			break; 
			case 'application/vnd.ms-office':
				return 'mso';
			break;
			case 'application/x-shockwave-flash':
				return 'swf';
			break;
			case 'application/x-empty':
				return 'any';
			break;
			case 'image/jpeg':
				return 'jpg';
			break;
			case 'text/plain':
				return 'txt';
			break;
			case 'audio/mpeg':
				return 'mp3';
			break;
			default:
				return $sMime;
			break;
		}
	}
}