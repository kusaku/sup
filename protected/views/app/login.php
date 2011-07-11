<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="language" content="ru" />
        <title><?= Yii::app()->name['name']?></title>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/sup.css" />
        <link rel="SHORTCUT ICON" href="/favicon.ico" />
    </head>
    <body style="padding: 0; margin: 0;">
        <?php $form = $this->beginWidget('CActiveForm', array('id'=>'login-form', 'enableClientValidation'=>true, 'clientOptions'=>array('validateOnSubmit'=>true, ), )); ?>
        <div class="login_form">
            <img src="/images/logo.png" class="login_form_img"/>
            <div class="row">
                <?php echo $form->labelEx($model, 'Имя пользователя'); ?>
                <br>
                <?php echo $form->textField($model, 'username'); ?>
                <?php echo $form->error($model, 'username'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'Пароль'); ?>
                <br>
                <?php echo $form->passwordField($model, 'password'); ?>
                <?php echo $form->error($model, 'password'); ?>
                <p class="hint">
                </p>
            </div>
            <div class="row rememberMe">
                <?php echo $form->checkBox($model, 'rememberMe'); ?>
                <?php echo $form->label($model, 'Запомнить меня'); ?>
                <?php echo $form->error($model, 'rememberMe'); ?>
            </div>
            <div class="row_buttons">
                <?php echo CHtml::submitButton('Enter'); ?>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </body>
</html>