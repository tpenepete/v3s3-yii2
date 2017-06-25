<?php

namespace app\modules\V3s3\controllers;

use finfo;
use Json;

use Yii as YII2_Yii;

use yii\web\Response as YII2_Response;
use yii\web\XmlResponseFormatter as YII2_XmlResponseFormatter;

use yii\web\Controller as YII2_Controller;
use yii\rest\ActiveController as YII2_ActiveController;

use yii\helpers\Json as YII2_Json;

use app\modules\V3s3\Module as V3s3Module;

use app\modules\V3s3\models\table\V3s3Table;

use app\modules\V3s3\helpers\V3s3Html;
use app\modules\V3s3\helpers\V3s3Xml;

use app\modules\V3s3\exceptions\V3s3Exception;

/**
 * Default controller for the `V3s3` module
 */
class DefaultController extends YII2_Controller {
	public $modelClass = 'app\modules\V3s3\models\table\V3s3Table';
	public $enableCsrfValidation = false;

	private $table;

	public function init() {
		$this->table = new V3s3Table;
	}

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex() {
		return $this->render('index');
	}

	public function actionPut() {
		$request = YII2_Yii::$app->request;
		$response = YII2_Yii::$app->response;

		$name = $request->getPathInfo();

		try {
			if (empty($name) || ($name == '/')) {
				throw new V3s3Exception(V3s3Module::t('V3s3', 'V3S3_EXCEPTION_PUT_EMPTY_OBJECT_NAME'), V3s3Exception::PUT_EMPTY_OBJECT_NAME);
			} else if (strlen($name) > 1024) {
				throw new V3s3Exception(V3s3Module::t('V3s3', 'V3S3_EXCEPTION_OBJECT_NAME_TOO_LONG'), V3s3Exception::OBJECT_NAME_TOO_LONG);
			}
		} catch(V3s3Exception $e) {
			$response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>0,
				'code'=>$e->getCode(),
				'message'=>$e->getMessage()
				];
		}

		$data = $request->getRawBody();

		$content_type = $request->headers->get('Content-Type');
		$mime_type = (is_null($content_type)?(new finfo(FILEINFO_MIME))->buffer($data):$content_type);
		$row = $this->table->put(
			[
				'ip'=>$request->getUserIP(),
				'name'=>$name,
				'data'=>$data,
				'mime_type'=>$mime_type,
			]
		);

		$response->headers->set('v3s3-object-id', $row['id']);

		$response->format = YII2_Response::FORMAT_JSON;
		return [
			'status'=>1,
			'message'=>V3s3Module::t('V3s3', 'V3S3_MESSAGE_PUT_OBJECT_ADDED_SUCCESSFULLY'),
		];
	}

	public function actionGet() {
		$request = YII2_Yii::$app->request;
		$response = YII2_Yii::$app->response;

		$name = $request->getPathInfo();

		try {
			if (strlen($name) > 1024) {
				throw new V3s3Exception(V3s3Module::t('V3s3', 'V3S3_EXCEPTION_OBJECT_NAME_TOO_LONG'), V3s3Exception::OBJECT_NAME_TOO_LONG);
			}
		} catch(V3s3Exception $e) {
			$response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>0,
				'code'=>$e->getCode(),
				'message'=>$e->getMessage()
			];
		}

		$row = $this->table->get(
			array_replace(
				$request->getQueryParams(),
				[
					'name'=>$name,
				]
			)
		);

		if(!empty($row['status'])) {
			$response->format = YII2_Response::FORMAT_RAW;
			$response->content = $row['data'];

			if(empty($row['mime_type'])) {
				$row['mime_type'] = (new finfo(FILEINFO_MIME))->buffer($row['data']);
			}
			$content_length = strlen($row['data']);
			$response->headers->fromArray(
				[
					'v3s3-object-id'=>[$row['id']],
					'Content-Type'=>[$row['mime_type']],
					'Content-Length'=>[$content_length]
				]
			);
			if(!empty($request->getQueryParam('download'))) { // PHP 5.5+
				$filename = basename($name);
				$response->setDownloadHeaders($filename, $row['mime_type'], false, $content_length);
			}

			return $response;
		} else {
			$response->setStatusCode(404);

			$response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>1,
				'results'=>0,
				'message'=>V3s3Module::t('V3s3', 'V3S3_MESSAGE_404')
			];
		}
	}

	public function actionDelete() {
		$request = YII2_Yii::$app->request;
		$response = YII2_Yii::$app->response;

		$name = $request->getPathInfo();

		try {
			if (empty($name) || ($name == '/')) {
				throw new V3s3Exception(V3s3Module::t('V3s3', 'V3S3_EXCEPTION_DELETE_EMPTY_OBJECT_NAME'), V3s3Exception::DELETE_EMPTY_OBJECT_NAME);
			} else if (strlen($name) > 1024) {
				throw new V3s3Exception(V3s3Module::t('V3s3', 'V3S3_EXCEPTION_OBJECT_NAME_TOO_LONG'), V3s3Exception::OBJECT_NAME_TOO_LONG);
			}
		} catch(V3s3Exception $e) {
			YII2_Yii::$app->response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>0,
				'code'=>$e->getCode(),
				'message'=>$e->getMessage()
			];
		}

		$input = $request->getQueryParams();
		$row = $this->table->api_delete(
			array_replace(
				$input,
				[
					'name'=>$name,
					'ip_deleted_from'=>$request->getUserIP()
				]
			)
		);

		if(empty($row)) {
			$response->setStatusCode(404);

			$response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>1,
				'results'=>0,
				'message'=>V3s3Module::t('V3s3', 'V3S3_MESSAGE_NO_MATCHING_RESOURCES')
			];
		} else {
			$response->headers->set('v3s3-object-id', $row['id']);

			$response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>1,
				'results'=>1,
				'message'=>V3s3Module::t('V3s3', 'V3S3_MESSAGE_DELETE_OBJECT_DELETED_SUCCESSFULLY')
			];
		}
	}

	public function actionPost() {
		$request = YII2_Yii::$app->request;
		$response = YII2_Yii::$app->response;

		$name = $request->getPathInfo();

		$input = $request->getRawBody();

		try {
			$parsed_input = (!empty($input)?YII2_Json::decode($input):[]);
		} catch(ZF3_JsonRuntimeException $e) {
			YII2_Yii::$app->response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>0,
				'code'=>$e->getCode(),
				'message'=>$e->getMessage()
			];
		}

		if(!empty($input) && empty($parsed_input)) {
			try {
				throw new V3s3Exception(V3s3Module::t('V3s3', 'V3S3_EXCEPTION_POST_INVALID_REQUEST'), V3s3Exception::POST_INVALID_REQUEST);
			} catch(V3s3Exception $e) {
				YII2_Yii::$app->response->format = YII2_Response::FORMAT_JSON;
				return [
					'status'=>0,
					'code'=>$e->getCode(),
					'message'=>$e->getMessage()
			];
			}
		}

		$attr = (!empty($parsed_input['filter'])?$parsed_input['filter']:[]);
		if(!empty($name) && ($name != '/')) {
			$attr['name'] = $name;
		}

		$rows = $this->table->post(
			$attr
		);

		if(!empty($rows)) {
			foreach ($rows as &$_row) {
				unset($_row['id']);
				unset($_row['timestamp']);
				unset($_row['hash_name']);
				unset($_row['timestamp_deleted']);
				if(empty($_row['mime_type'])) {
					$_row['mime_type'] = (new finfo(FILEINFO_MIME))->buffer($_row['data']).' (determined using PHP finfo)';
				}
				$_row['data'] = (new finfo(FILEINFO_MIME))->buffer($_row['data']);
			}

			$format = ((!empty($parsed_input['format'])&&in_array($parsed_input['format'], ['json', 'xml', 'html']))?strtolower($parsed_input['format']):'json');
			switch($format) {
				case 'xml':
					//$rows = V3s3Xml::simple_xml($rows);
					$response->formatters = [
						YII2_Response::FORMAT_XML=>[
							'class'=>YII2_XmlResponseFormatter::class,
							'rootTag'=>'top'
						]
					];
					$response->format = YII2_Response::FORMAT_XML;
					$response->data = $rows;
					return $response;
					break;
				case 'html':
					$rows = V3s3Html::simple_table($rows);
					$response->format = YII2_Response::FORMAT_HTML;
					$response->data = $rows;
					return $response;
					break;
				case 'json':
				default:
					$response->format = YII2_Response::FORMAT_JSON;
					$response->data = $rows;
					break;
			}
		} else {
			$response->format = YII2_Response::FORMAT_JSON;
			return [
				'status'=>1,
				'results'=>0,
				'message'=>V3s3Module::t('V3s3', 'V3S3_MESSAGE_NO_MATCHING_RESOURCES')
			];
		}
	}
}
