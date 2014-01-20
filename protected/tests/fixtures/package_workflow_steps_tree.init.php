<?php
/**
 * @var CDbFixtureManager $this
 */
$this->getDbConnection()->createCommand('DROP TABLE IF EXISTS `package_workflow_steps_tree`')->execute();

$this->getDbConnection()->createCommand('CREATE TABLE IF NOT EXISTS `package_workflow_steps_tree` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT \'ID записи\',
  `from_step_id` int(11) unsigned NOT NULL COMMENT \'ID исходного шага\',
  `to_step_id` int(11) unsigned NOT NULL COMMENT \'ID конечного шага\',
  `comment` varchar(255) NOT NULL default \'\',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `from_step_id` (`from_step_id`,`to_step_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;')->execute();