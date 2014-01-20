<?php
/**
 * @var $this CDbFixtureManager
 * @var $tableName string
 */
$this->getDbConnection()->createCommand('DROP TABLE IF EXISTS `package_workflow_steps`')->execute();

$this->getDbConnection()->createCommand('CREATE TABLE IF NOT EXISTS `package_workflow_steps` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT \'ID шага\',
  `title` varchar(255) NOT NULL COMMENT \'Название\',
  `text_ident` varchar(255) NOT NULL COMMENT \'Текстовый идентификатор\',
  `wizzard_menu_id` int(11) unsigned NOT NULL COMMENT \'ID активного пункта меню\',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8')->execute();
