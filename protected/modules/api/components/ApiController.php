<?php 
/**
 * Базовый контроллер для контроллеров модуля API
 * @since 12.05.12
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 */

class ApiController extends Controller {

	public function __construct($id, $module = null) {
		Yii::app()->getEventHandlers('onError')->insertAt(0, array($this, 'handleError'));
		Yii::app()->getEventHandlers('onException')->insertAt(0, array($this, 'handleException'));
		parent::__construct($id, $module);
	}

	/**
	 * Метод выполняет установку ответа HTTP в заданном формате
	 */
	public function _setAnswer($code,$message) {
		header("HTTP/1.1 ".$code.' '.$message);
	}
	
	/**
	 * Метод уменьшает первую букву
	 */
	private function lcfirst($str) {
		$sFirst=strtolower(substr($str,0,1));
		return $sFirst.substr($str,1);
	}
	
	public function actions() {
		$sAlias='api.controllers.'.$this->getId();
		$arControllers=CFileHelper::findFiles(Yii::getPathOfAlias($sAlias),array('fileTypes'=>array('php'),'level'=>0));
		$arResult=array();
		foreach($arControllers as $sPath) {
			$sPath=str_replace(array(Yii::getPathOfAlias($sAlias).DIRECTORY_SEPARATOR,'.php'),'',$sPath);
			$arResult[$this->lcfirst(str_replace('Action','',$sPath))]=$sAlias.'.'.$sPath;
		}
		//print_r($arResult);
		return $arResult;
	}
	
	public function actionIndex() {
		$arResult=array(
			'result'=>500,
			'resultText'=>'Internal error'
		);
		$this->render('json',array('data'=>$arResult));
	}
	
	/**
	 * Метод вызывается после обработки View
	 */
	protected function afterRender($view,&$output) {
		if(Yii::app()->getModule('api')->logRequests) {
			$sMessage=date('d.m.Y H:i:s ').$this->_getRequest().' '.$output;
			Yii::log($sMessage,'trace');
		}
	}
	
	protected function _getRequest() {
		//TODO $sResult = Yii::app()->getUrlManager()->createPathInfo($_GET, '=', '&');
		$arResult=array();
		foreach($_GET as $key=>$value) {
			$arResult[]=$key.'='.$value;
		}
		$sResult=join('&',$arResult);
		if(isset($_GET['mode']) && $_GET['mode']=='POST' && $_SERVER['REQUEST_METHOD']=='POST') {
			$arResult=array();
			foreach($_POST as $key=>$value) {
				if(is_array($value))
					$arResult[]=$key.'='.json_encode($value);
				else
					$arResult[]=$key.'='.$value;
			}	
			$sResult.="\n".join('&',$arResult);
		}
		return $sResult;
	}


	public function handleError($event) {

		switch ($event->code) {
			case E_WARNING:
				$type = 'PHP warning';
				break;
			case E_NOTICE:
				$type = 'PHP notice';
				break;
			case E_USER_ERROR:
				$type = 'User error';
				break;
			case E_USER_WARNING:
				$type = 'User warning';
				break;
			case E_USER_NOTICE:
				$type = 'User notice';
				break;
			case E_RECOVERABLE_ERROR:
				$type = 'Recoverable error';
				break;
			default:
				$type = 'PHP error';
		}

		$arResult = array(
			'result' => 500,
			'resultText' => $type . ': ' . $event->message,
		);

		if (YII_DEBUG) {
			$trace = debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if (count($trace) > 3)
				$trace = array_slice($trace, 3);
			$traceString = '';
			foreach ($trace as $i => $t) {
				if (!isset($t['file']))
					$trace[$i]['file'] = 'unknown';

				if (!isset($t['line']))
					$trace[$i]['line'] = 0;

				if (!isset($t['function']))
					$trace[$i]['function'] = 'unknown';

				$traceString .= "#$i {$trace[$i]['file']}({$trace[$i]['line']}): ";
				if (isset($t['object']) && is_object($t['object']))
					$traceString .= get_class($t['object']) . '->';
				$traceString .= "{$trace[$i]['function']}()\n";

				unset($trace[$i]['object']);
			}

			$arResult['debug']['code'] = 500;
			$arResult['debug']['type'] = $type;
			$arResult['debug']['message'] = $event->message;
			$arResult['debug']['file'] = $event->file;
			$arResult['debug']['line'] = $event->line;
			$arResult['debug']['trace'] = $traceString;
		}

		if (!headers_sent()) {
			header("HTTP/1.0 500 PHP Error");
		}
		$this->renderPartial('/layouts/json', array('data' => $arResult));
		$event->handled = true;
	}

	public function handleException($exceptionEvent) {

		$exception = $exceptionEvent->exception;
		$arResult = array(
			'result' => 500,
			'resultText' => $exception->getMessage(),
		);
		$header = 'HTTP/1.0 500 Internal Server Error';

		if ($exception instanceof ApiException) {
			$arErrorData = $exception->getResultArray();

			$arResult['result'] = $arErrorData['result'];
			$arResult['resultText'] = $arErrorData['resultText'];
			$arResult['error'] = $arErrorData['error'];
			$arResult['errorText'] = $arErrorData['errorText'];
		} elseif ($exception instanceof CHttpException) {
			switch ($code = $exception->statusCode) {
				case 402:
					$iHttpErrorCode = 403;
					break;
				case 503:
					$iHttpErrorCode = 501;
					break;
				case 504:
					$iHttpErrorCode = 401;
					break;
				case 505:
					$iHttpErrorCode = 500;
					break;
				default:
					$iHttpErrorCode = $code;
			}

			$arResult['result'] = $code;
			$arResult['resultText'] = $exception->getMessage();

			$header = 'HTTP/1.0 ' . $iHttpErrorCode . ' ' . $this->_arHttpErrorTexts[$iHttpErrorCode];
		}

		if (YII_DEBUG) {
			if (($trace = $this->getExactTrace($exception)) === null) {
				$fileName = $exception->getFile();
				$errorLine = $exception->getLine();
			} else {
				$fileName = $trace['file'];
				$errorLine = $trace['line'];
			}

			$trace = $exception->getTrace();

			foreach ($trace as $i => $t) {
				if (!isset($t['file']))
					$trace[$i]['file'] = 'unknown';

				if (!isset($t['line']))
					$trace[$i]['line'] = 0;

				if (!isset($t['function']))
					$trace[$i]['function'] = 'unknown';

				unset($trace[$i]['object']);
			}

			$arResult['debug']['code'] = ($exception instanceof CHttpException) ? $exception->statusCode : 500;
			$arResult['debug']['type'] = get_class($exception);
			$arResult['debug']['errorCode'] = $exception->getCode();
			$arResult['debug']['message'] = $exception->getMessage();
			$arResult['debug']['file'] = $fileName;
			$arResult['debug']['line'] = $errorLine;
			$arResult['debug']['trace'] = $exception->getTraceAsString();
		}

		if (!headers_sent()) {
			header($header);
		}
		$this->renderPartial('/layouts/json', array('data' => $arResult));
		$exceptionEvent->handled = true;
	}

	/**
	 * Returns the exact trace where the problem occurs.
	 * @param Exception $exception the uncaught exception
	 * @return array the exact trace where the problem occurs
	 */
	protected function getExactTrace($exception) {
		$traces = $exception->getTrace();

		foreach ($traces as $trace) {
			// property access exception
			if (isset($trace['function']) && ($trace['function'] === '__get' || $trace['function'] === '__set'))
				return $trace;
		}
		return null;
	}

	private $_arHttpErrorTexts = array(
		'100' => 'Continue',
		'101' => 'Switching Protocols',
		'102' => 'Processing',
		'200' => 'OK',
		'201' => 'Created',
		'202' => 'Accepted',
		'203' => 'Non-Authoritative Information',
		'204' => 'No Content',
		'205' => 'Reset Content',
		'206' => 'Partial Content',
		'207' => 'Multi-Status',
		'226' => 'IM Used',
		'300' => 'Multiple Choices',
		'301' => 'Moved Permanently',
		'302' => 'Found',
		'303' => 'See Other',
		'304' => 'Not Modified',
		'305' => 'Use Proxy',
		'307' => 'Temporary Redirect',
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
		'406' => 'Not Acceptable',
		'407' => 'Proxy Authentication Required',
		'408' => 'Request Timeout',
		'409' => 'Conflict',
		'410' => 'Gone',
		'411' => 'Length Required',
		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
		'414' => 'Request-URI Too Long',
		'415' => 'Unsupported Media Type',
		'416' => 'Requested Range Not Satisfiable',
		'417' => 'Expectation Failed',
		'422' => 'Unprocessable Entity',
		'423' => 'Locked',
		'424' => 'Failed Dependency',
		'425' => 'Unordered Collection',
		'426' => 'Upgrade Required',
		'449' => 'Retry With',
		'456' => 'Unrecoverable Error',
		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
		'502' => 'Bad Gateway',
		'503' => 'Service Unavailable',
		'504' => 'Gateway Timeout',
		'505' => 'HTTP Version Not Supported',
		'506' => 'Variant Also Negotiates',
		'507' => 'Insufficient Storage',
		'509' => 'Bandwidth Limit Exceeded',
		'510' => 'Not Extended',
	);

}
