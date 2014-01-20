<p>Здравствуйте!</p>
<?php if($package->manager_id>0 && $package->workflow):?>
    <p>Ваш менеджер (<?php echo $package->manager->fio?>) оставил комментарий на странице "<?php echo $package->workflow->step->title?>".<br/>
<?php elseif($package->manager_id>0):?>
    <p>Ваш менеджер (<?php echo $package->manager->fio?>) оставил комментарий к вашему заказу.<br/>
<?php endif?>     
Посмотреть комментарий можно перейдя по <a href="http://cabinet.fabricasaitov.ru/packages/detail/<?php echo $package->id?>">http://cabinet.fabricasaitov.ru/packages/detail/<?php echo $package->id?></a>.</p>

<p>Пожалуйста, не отвечайте на это письмо. Оно сформировано автоматически.</p>

<p>С уважением,<br/>
Компания Фабрика сайтов<br/>
тел./факс: +7 (495) 646-01-96<br/>
тел./факс: +7 (812) 643-43-11<br/>
8 800 333-88-30 (звонок бесплатный)<br/>
<a href="http://fsrf.fabricasaitov.ru/">http://фабрика-сайтов.рф/</a></p>