<?php
/**
 * Класс обеспечивает вывод форм в стиле фабрики сайтов
 */

class FSActiveForm extends CActiveForm {

	/**
	 * @param string $text
	 * @param array  $arParams
	 * @param array  $arHeaderParams
	 *
	 * @return string
	 */
	public function formRowHeader($text,$arParams=array(),$arHeaderParams=array()) {
		return CHtml::tag('div',$arParams,CHtml::tag('h2',$arHeaderParams,$text));
	}

	/**
	 * @param CModel $model
	 * @param string $field
	 * @param array  $arParams
	 * @param array  $arFieldParams
	 * @param array  $arLabelParams
	 *
	 * @return string
	 */
	public function formRowTextField($model,$field,$arParams=array(),$arFieldParams=array(),$arLabelParams=array()) {
		if($model->hasErrors($field)) {
			if(isset($arParams['class'])) {
				$arParams['class'].=' error ';
			} else {
				$arParams['class']='error';
			}
			if(isset($arLabelParams['title'])) {
				$arParams['title'].="\n ".join("\n",$model->getErrors($field));
			} else {
				$arParams['title']=join("\n",$model->getErrors($field));
			}
		}
		return CHtml::tag('div',$arParams,$this->label($model,$field,$arLabelParams).$this->textField($model,$field,$arFieldParams));
	}

	/**
	 * Метод генерирует разделительную линию в форме
	 * @return string
	 */
	public function formRowDivider() {
		return CHtml::tag('div',array('style'=>'clear:both;border-top:1px solid #aaa;height:1px;overflow:hidden;'),'');
	}

	/**
	 * Метод генерирует строку формы с ссылкой
	 * @param       $title
	 * @param       $url
	 * @param array $arParams
	 * @param array $arLinkParams
	 *
	 * @return string
	 */
	public function formRowLink($title,$url,$arParams=array(),$arLinkParams=array()) {
		echo CHtml::tag('div',$arParams,CHtml::link($title,$url,$arLinkParams));
	}

	/**
	 * Метод генерирует строку формы с ссылкой
	 * @param       $title
	 * @param array $arParams
	 * @param array $arButtonParams
	 *
	 * @return string
	 */
	public function formRowSubmit($title,$arParams=array(),$arButtonParams=array()) {
		echo CHtml::tag('div',$arParams,CHtml::submitButton($title,$arButtonParams));
	}

	/**
	 * @param CModel $model
	 * @param string $field
	 * @param array  $arParams
	 * @param array  $arFieldParams
	 * @param array  $arLabelParams
	 *
	 * @return string
	 */
	public function formRowCheckbox($model,$field,$arParams=array(),$arFieldParams=array(),$arLabelParams=array()) {
		if($model->hasErrors($field)) {
			if(isset($arParams['class'])) {
				$arParams['class'].=' error ';
			} else {
				$arParams['class']='error';
			}
			if(isset($arLabelParams['title'])) {
				$arParams['title'].="\n ".join("\n",$model->getErrors($field));
			} else {
				$arParams['title']=join("\n",$model->getErrors($field));
			}
		}
		return CHtml::tag('div',$arParams,$this->label($model,$field,$arLabelParams).$this->checkBox($model,$field,$arFieldParams));
	}

	/**
	 * @param CModel $model
	 * @param string $field
	 * @param string $sFormat
	 * @param array  $arParams
	 * @param array  $arFieldParams
	 * @param array  $arLabelParams
	 *
	 * @return string
	 */
	public function formRowDateField($model,$field,$sFormat='d.m.Y H:i:s',$arParams=array(),$arFieldParams=array(),$arLabelParams=array()) {
		if($model->hasErrors($field)) {
			if(isset($arParams['class'])) {
				$arParams['class'].=' error ';
			} else {
				$arParams['class']='error';
			}
			if(isset($arLabelParams['title'])) {
				$arParams['title'].="\n ".join("\n",$model->getErrors($field));
			} else {
				$arParams['title']=join("\n",$model->getErrors($field));
			}
		}
		if($model->$field!='' && $model->$field!=null && $model->$field!='0000-00-00 00:00:00') {
			$arFieldParams['value']=date($sFormat,strtotime($model->$field));
		} else {
			$model->$field=null;
		}
		return CHtml::tag('div',$arParams,$this->label($model,$field,$arLabelParams).$this->textField($model,$field,$arFieldParams));
	}

	/**
	 * @param CModel $model
	 * @param string $field
	 * @param array  $arParams
	 * @param array  $arFieldParams
	 * @param array  $arLabelParams
	 *
	 * @return string
	 */
	public function formRowTextarea($model,$field,$arParams=array(),$arFieldParams=array(),$arLabelParams=array()) {
		if($model->hasErrors($field)) {
			if(isset($arParams['class'])) {
				$arParams['class'].=' error ';
			} else {
				$arParams['class']='error';
			}
			if(isset($arLabelParams['title'])) {
				$arParams['title'].="\n ".join("\n",$model->getErrors($field));
			} else {
				$arParams['title']=join("\n",$model->getErrors($field));
			}
		}
		return CHtml::tag('div',$arParams,$this->label($model,$field,$arLabelParams).$this->textArea($model,$field,$arFieldParams));
	}

	/**
	 * @param CModel $model
	 * @param string $field
	 * @param array  $arData
	 * @param array  $arParams
	 * @param array  $arFieldParams
	 * @param array  $arLabelParams
	 *
	 * @return string
	 */
	public function formRowDropDownList($model,$field, $arData=array(),$arParams=array(),$arFieldParams=array(),$arLabelParams=array()) {
		if($model->hasErrors($field)) {
			if(isset($arParams['class'])) {
				$arParams['class'].=' error ';
			} else {
				$arParams['class']='error';
			}
			if(isset($arLabelParams['title'])) {
				$arParams['title'].="\n ".join("\n",$model->getErrors($field));
			} else {
				$arParams['title']=join("\n",$model->getErrors($field));
			}
		}
		return CHtml::tag('div',$arParams,$this->label($model,$field,$arLabelParams).$this->dropDownList($model,$field,$arData,$arFieldParams));
	}
}
