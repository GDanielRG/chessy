<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Form;

class ApiController extends Controller {

	protected function response($type = 'Success', $message = null, $data = [], $httpStatusCode = null)
	{
		if (substr($message, 0, 9) == "messages.")
			$message = trans($message);

		$response = [
			'type'    => $type,
			'message' => $message,
			'data'    => $data,
		];

		if (!is_null($httpStatusCode))
		{
			return response($response, $httpStatusCode)->header('Content-Type', 'text/json');
		}

		return $response;
	}

	protected function success($message = null, $data = [], $httpStatusCode = null)
	{
		return $this->response('Success', $message, $data, $httpStatusCode);
	}

	protected function error($message = null, $data = [], $httpStatusCode = null)
	{
		if (is_null($message))
			$message = "messages.errors.general";

		if (!isset($data['errors']))
			$data['errors'] = Form::getErrors();

		return $this->response('Error', $message, $data, $httpStatusCode);
	}

	protected function warning($message = null, $data = [], $httpStatusCode = null)
	{
		return $this->response('Warning', $message, $data, $httpStatusCode);
	}

	protected function info($message = null, $data = [], $httpStatusCode = null)
	{
		return $this->response('Info', $message, $data, $httpStatusCode);
	}

}
