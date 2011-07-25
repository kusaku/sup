<?php
return array (
  'guest' => 
  array (
    'type' => 2,
    'description' => 'Гость',
    'bizRule' => 'return Yii::app()->user->isGuest;',
    'data' => NULL,
  ),
  'authenticated' => 
  array (
    'type' => 2,
    'description' => 'Пользователь',
    'bizRule' => 'return !Yii::app()->user->isGuest;',
    'data' => NULL,
    'children' => 
    array (
      0 => 'admin',
      1 => 'moder',
      2 => 'topmanager',
      3 => 'manager',
      4 => 'master',
      5 => 'client',
      6 => 'leadmaster',
      7 => 'remotemaster',
      8 => 'superpartner',
    ),
  ),
  'admin' => 
  array (
    'type' => 2,
    'description' => 'Администратор',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="1";',
    'data' => NULL,
  ),
  'moder' => 
  array (
    'type' => 2,
    'description' => 'Модератор',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="2";',
    'data' => NULL,
  ),
  'topmanager' => 
  array (
    'type' => 2,
    'description' => 'Старший менеджер',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="3";',
    'data' => NULL,
  ),
  'manager' => 
  array (
    'type' => 2,
    'description' => 'Менеджер',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="4";',
    'data' => NULL,
  ),
  'master' => 
  array (
    'type' => 2,
    'description' => 'Мастер',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="5";',
    'data' => NULL,
  ),
  'partner' => 
  array (
    'type' => 2,
    'description' => 'Партнер',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="6";',
    'data' => NULL,
  ),
  'client' => 
  array (
    'type' => 2,
    'description' => 'Клиент',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="7";',
    'data' => NULL,
  ),
  'leadmaster' => 
  array (
    'type' => 2,
    'description' => 'Ведущий мастер',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="8";',
    'data' => NULL,
  ),
  'remotemaster' => 
  array (
    'type' => 2,
    'description' => 'Удаленный мастер',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="9";',
    'data' => NULL,
  ),
  'superpartner' => 
  array (
    'type' => 2,
    'description' => 'Супер партнер',
    'bizRule' => 'return isset(Yii::app()->user->group_id) and Yii::app()->user->group_id=="10";',
    'data' => NULL,
  ),
  'role1' => 
  array (
    'type' => 2,
    'description' => '',
    'bizRule' => NULL,
    'data' => NULL,
    'children' => 
    array (
      0 => 'role2',
    ),
  ),
  'role2' => 
  array (
    'type' => 2,
    'description' => '',
    'bizRule' => NULL,
    'data' => NULL,
  ),
);
