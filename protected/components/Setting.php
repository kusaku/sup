<?php
/**
 * Класс обеспечивает хранение и считывание данных пользовательских настроек
 * в БД, также реализовано промежуточное кэширование данных. Для хранения
 * данные сериализуются функцией serialize.
 */
class Setting {
	private static $_arSettings = array();
	private static $_arRawSettings = array();

	/**
	 * Получаем определенную настройку или массив всех настроек определенного раздела
	 *
	 * @param string $strSection наименование раздела для хранения настроек
	 * @param string $strSetting название настройки или пустая строка, для получения всех настроек раздела
	 * @return mixed
	 * @throws Exception в случае если данной настройки не существует в хранилище
	 */
	public static function get($strSection,$strSetting=''){
		self::_getRawSettings();
		if(!isset(self::$_arSettings[$strSection])){
			self::$_arSettings[$strSection] = unserialize(self::$_arRawSettings[$strSection]);
		}
		if($strSetting && !self::$_arSettings[$strSection][$strSetting]){
			throw new Exception('There is no such setting in this section');
		}
		return $strSetting ? self::$_arSettings[$strSection][$strSetting] : self::$_arSettings[$strSection];
	}

	/**
	 * Сервисный метод для получения настроек из базы
	 */
	private static function _getRawSettings(){
		if(!self::$_arRawSettings){
			$sql = 'SELECT section, settings FROM setting';
			$command=Yii::app()->db->createCommand($sql);
			self::$_arRawSettings=$command->setFetchMode(PDO::FETCH_KEY_PAIR)->queryAll();
		}
	}

	/**
	 * Сохранение группы настроек
	 *
	 * @param string $strSection наименование раздела для хранения настроек
	 * @param array $arSettings массив вида 'название_настройки'=>значение_настройки
	 */
	public static function set($strSection,$arSettings){
		// сначала получаем данные, если их нет в нашем кэше
		self::get($strSection);

		$arToUpdate = array();
		foreach ($arSettings as $sSettingName => $varValue){
			//Если данные не изменились, то ничего делать не надо
			if(isset(self::$_arSettings[$strSection][$sSettingName]) && self::$_arSettings[$strSection][$sSettingName]===$varValue){
				continue;
			}
			$arToUpdate[$sSettingName]=$varValue;
		}

		// если нужно что-либо обновлять
		if($arToUpdate){
			// обновляем данные в кэше
			self::$_arSettings[$strSection] = array_merge(self::$_arSettings[$strSection],$arToUpdate);
			self::$_arRawSettings[$strSection] = serialize(self::$_arSettings[$strSection]);

			// записываем в хранилище
			$sql = 'UPDATE `setting` SET `settings` = :settings WHERE `section` = :section';
			$command=Yii::app()->db->createCommand($sql);
			$command->bindParam(":settings",self::$_arRawSettings[$strSection],PDO::PARAM_STR);
			$command->bindParam(":section",$strSection,PDO::PARAM_STR);
			if(!$command->execute()){
				throw new Exception('There is no such section for this setting');
			}
		}
	}
}
