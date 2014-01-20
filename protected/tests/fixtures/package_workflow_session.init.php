<?php
/**
 * @var CDbFixtureManager $this
 */
$this->getDbConnection()->createCommand('DROP TABLE IF EXISTS `package_workflow_session`')->execute();

$this->getDbConnection()->createCommand('CREATE TABLE IF NOT EXISTS `package_workflow_session` (
  `package_id` int(11) unsigned NOT NULL COMMENT \'ID пакета\',
  `step_id` int(11) unsigned NOT NULL COMMENT \'ID шага\',
  `data` text NOT NULL COMMENT \'данные\',
  PRIMARY KEY (`package_id`,`step_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;')->execute();