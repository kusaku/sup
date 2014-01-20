<?php
/**
 * @var $this CDbFixtureManager
 * @var $tableName string
 */
$this->getDbConnection()->createCommand('DROP TABLE IF EXISTS `package_workflow`')->execute();

$this->getDbConnection()->createCommand('CREATE TABLE IF NOT EXISTS `package_workflow` (
  `package_id` int(11) unsigned NOT NULL COMMENT \'ID пакета\',
  `step_id` int(11) unsigned NOT NULL COMMENT \'ID шага мастера\',
  `date_ping` datetime DEFAULT NULL,
  PRIMARY KEY (`package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;')->execute();
