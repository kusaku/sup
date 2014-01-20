<div class="wrapper">
	<div class="editClientWindow wizzard_window" id="sm_content">
		<div class="clientHead">
			Заказ #<?php echo $package->getNumber()?>: Состояние мастера ЛКК
		</div>
		<div class="clientInfo">
			<div class="wizzard_map">
				<div class="wm_step" id="wm_step_select_product">Выбор продукта</div>
				<div class="wm_step" id="wm_step_select_services">Заявка</div>
				<div class="wm_step" id="wm_step_paytype">Выбор способа оплаты</div>
				<div class="wm_step" id="wm_step_man_pay_sb">Печать квитанции физического лица</div>
				<div class="wm_step" id="wm_step_fill_rekviz">Реквизиты юр.лица</div>
				<div class="wm_step" id="wm_step_check_rekviz">Печать документов</div>
				<div class="wm_step" id="wm_step_man_pay_qiwi">Заполнение формы Qiwi</div>
				<div class="wm_step" id="wm_step_qiwi_pay_form">Оплата в Qiwi</div>
				<div class="wm_step" id="wm_step_vkredit_request">Заполнение заявки на кредит</div>
				<div class="wm_step" id="wm_step_robokassa_payment">Оплата в Robokassa</div>
				<div class="wm_step" id="wm_step_form_domain">Заявка на домен</div>
				<div class="wm_step" id="wm_step_form_info">Заполнение анкеты</div>
				<div class="wm_step" id="wm_step_payment_waiting">Ожидание оплаты
					<div class="im_icon"><!-- --></div>
				</div>
				<div class="wm_step" id="wm_step_design_form">Выбор дизайна
					<div class="im_icon"><!-- --></div>
				</div>
				<div class="wm_step" id="wm_step_brief_form">Заполнение брифа
					<div class="im_icon"><!-- --></div>
				</div>
				<div class="wm_step" id="wm_step_waiting">Ожидание готовности
					<div class="im_icon"><!-- --></div>
				</div>
				<div class="wm_step" id="wm_step_ready">Сайт готов</div>
				<div class="wm_loader" id="loader"><!-- --></div>
				<div class="wm_user"><!-- --></div>
			</div>
		</div>
		<div class="step_data">
			<a href="#" id="getData">получить данные</a>
		</div>
	</div>
</div>