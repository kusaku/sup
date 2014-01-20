<?php
/**
 * @var $package Package
 * @var $user People
 */?>
Здравствуйте, <?php echo $user->fio?>!<br/>
<br/>
Статус Вашего заказа (<?php echo $package->id?>) изменился на <?php if($package->payment_id==20):?>"Условно оплачен"<?php else:?>"Оплачен"<?php endif?>.<br/>
<br/>
Для выбора макета дизайна Вашего сайта, перейдите, пожалуйста, к следующему шагу по ссылке:<br/>
<br/>
<a href="http://cabinet.fabricasaitov.ru/ru/packages/detail/<?php echo $package->id?>" target="_blank">http://cabinet.fabricasaitov.ru/ru/packages/detail/<?php echo $package->id?></a><br/>
<br/>
С уважением,<br/>
Компания Фабрика сайтов<br/>
тел./факс: +7 (495) 646-01-96<br/>
тел./факс: +7 (812) 643-43-11<br/>
8 800 333-88-30 (звонок бесплатный)<br/>
http://фабрика-сайтов.рф/<br/>
<br/>
Это автоматически созданное письмо. Пожалуйста, не отвечайте на него.<br/>
