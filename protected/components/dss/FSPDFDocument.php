<?php
/**
 * Класс реализует универсальный метод отрисовки HTML документа в виде PDF
 */

abstract class FSPDFDocument extends FSDocument implements IDocumentPdfResult, IDocumentTextResult {
	public function getAsPdf() {
		if($obOldDocument=$this->obStorage->getModel()->findByAttributes(array('md5summ'=>$this->getHash(),'type'=>$this->sType,'storage_format'=>'pdf'))) {
			return $this->obStorage->loadDocumentFromDisk($obOldDocument);
		} elseif($obController=$this->getController()) {
			$sHtmlString=$this->getAsHtml();
			$obController->createAction('pdf')->runWithParams(array('content'=>$sHtmlString));
			$mPDF = Yii::app()->ePdf->mpdf();
			$mPDF->WriteHTML($obController->getOutput());
			$sContent=$mPDF->Output('', 'S');
			$this->store($sContent,'pdf');
			return $sContent;
		}
		throw new Exception('SYSTEM_DOCUMENT_TEMPLATE_NOT_FOUND',10);
	} 
	
	/**
	 * Метод возвращает контроллер ответственный за отрисовку документа
	 */
	protected function getController() {
		$arResult=Yii::app()->createController('documents/document/pdf');
		if(is_array($arResult)) {
			return $arResult[0];
		}
		return false;
	}

	public function getAsText() {
		$sText=$this->getAsHtml();
		return strip_tags($sText);
	}
	
	public function getAsFile() {
		return $this->getAsPdf();
	}	
}
