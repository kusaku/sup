<?php
interface IModelEvent {
	public function init(CModel $caller);
	public function afterSave();
	public function beforeSave();
}