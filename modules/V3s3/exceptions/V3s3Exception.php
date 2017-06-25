<?php

namespace app\modules\V3s3\exceptions;

use Exception;
use RuntimeException;

class V3s3Exception extends RuntimeException {
	const INVALID_METHOD=1;
	const OBJECT_NAME_TOO_LONG=2;
	const PUT_EMPTY_OBJECT_NAME=3;
	const DELETE_EMPTY_OBJECT_NAME=4;
	const POST_EMPTY_OBJECT_NAME=5;
	const POST_INVALID_REQUEST=6;

	public function __construct($message, $code = 0, Exception $previous = null) {
		switch($code) {
			case self::INVALID_METHOD:
			case self::OBJECT_NAME_TOO_LONG:
			case self::PUT_EMPTY_OBJECT_NAME:
			case self::DELETE_EMPTY_OBJECT_NAME:
			case self::POST_EMPTY_OBJECT_NAME:
			case self::POST_INVALID_REQUEST:
				break;
		}
		parent::__construct($message, $code, $previous);
	}

	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}