Здравствуйте, <?php echo $user->fio or $user->login?>,<br/>
вы запросили восстановление пароля от личного кабинета «Фабрики Сайтов».<br/> 
<?php if($model->codePage!=''):?>
Для продолжения восстановления пароля вы можете ввести код восстановления пароля на странице <a href="<?php echo $application->url.$model->codePage;?>">ввода кода восстановления</a>
(<?php echo $application->url.$model->codePage;?>) 
<br/>
<?php endif?>
Ваш код восстановления пароля: <?php echo $model->code;?><br/>
<?php if($model->codeUrl!=''):?>
Или просто перейти по ссылке:<br/>
<a href="<?php echo $application->url.$model->getCodeLink();?>"><?php echo $application->url.$model->getCodeLink();?></a><br/>
<?php endif?>
С уважением, служба поддержки компании «Фабрика Сайтов»<br/>
---------<br/>
Данное письмо отправлено автоматически и отвечать на него не требуется<br/>