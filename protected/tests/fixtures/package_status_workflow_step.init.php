<?php
/**
 * @var CDbFixtureManager $this
 */
$this->getDbConnection()->createCommand('DROP TABLE IF EXISTS `package_status_workflow_step`')->execute();

$this->getDbConnection()->createCommand('CREATE TABLE IF NOT EXISTS `package_status_workflow_step` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` int(11) unsigned NOT NULL,
  `step_id` int(11) unsigned NOT NULL,
  `set_step` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;')->execute();