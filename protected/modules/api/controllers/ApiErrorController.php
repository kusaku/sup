<?php 
/**
 * Класс обеспечивает генерацию ответа об ошибки при оформлении ответа сервера
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 12.05.12
 */
class ApiErrorController extends ApiController {
	public function actions() {
		return array(
			//неизвестная ошибка
			'c0'=>'api.controllers.apierror.BigErrorAction',
			//400 группа
			'c400'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>400,
				'ErrorCode'=>400
			),
			'c404'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>404,
				'ErrorCode'=>404
			),
			'c403'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>403,
				'ErrorCode'=>403
			),
			'c402'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>403,
				'ErrorCode'=>402
			),
			'c401'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>401,
				'ErrorCode'=>401
			),
			//500 группа
			'c500'=>'api.controllers.apierror.BigErrorAction',
			'c501'=>'api.controllers.apierror.ApiErrorAction',
			'c503'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>501,
				'ErrorCode'=>503
			),
			'c504'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>401,
				'ErrorCode'=>504
			),
			'c505'=>array(
				'class'=>'api.controllers.apierror.CommonErrorAction',
				'HttpErrorCode'=>500,
				'ErrorCode'=>505
			),
		);
	}
}