<?php
/**
 * Класс обеспечивает работу формы редактирования партнёрской информации
 */
class ServiceDescriptionEditForm extends CFormModel {
	public $service_id;
	public $description_id;
	public $title;
	public $description;
	public $content;
	public $document_title;
	public $link;
	public $icon;
	public $days;
	public $category;


	public function rules() {
		return array(
			array(
				'title,description,content,document_title,link,icon,days,category','safe'
			),
		);
	}

	public function attributeLabels() {
		return array(
			'title'=>'Название',
			'description'=>'Описание',
			'content'=>'Содержимое',
			'document_title'=>'Название для документов',
			'link'=>'Ссылка на описание',
			'icon'=>'Иконка',
			'days'=>'Срок исполнения',
			'category'=>'Категория'
		);
	}

	/**
	 * Метод выполняет запрос на сохранение данных пользователя на сервере
	 */
	public function save() {
		if($obService=Service::model()->findByPk($this->service_id)) {
			$obDescription=$obService->description;
			if(!$obDescription) {
				$obDescription=new ServiceDescription();
				$obDescription->service_id=$obService->id;
			}
			$obDescription->title=$this->title;
			$obDescription->description=$this->description;
			$obDescription->content=$this->content;
			$obDescription->document_title=$this->document_title;
			$obDescription->link=$this->link;
			$obDescription->icon=$this->icon;
			$obDescription->days=$this->days;
			$obDescription->category=$this->category;
			return $obDescription->save();
		}
		return false;
	}
}
