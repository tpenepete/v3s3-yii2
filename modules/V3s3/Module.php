<?php

namespace app\modules\V3s3;

use Yii as YII2_Yii;

/**
 * V3s3 module definition class
 */
class Module extends \yii\base\Module {
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\V3s3\controllers';

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();

		$this->registerTranslations();
    }

	public function registerTranslations() {
		YII2_Yii::$app->i18n->translations['modules/V3s3/*'] = [
			'class' => 'yii\i18n\PhpMessageSource',
			'sourceLanguage' => 'en-US',
			'forceTranslation' => true,
			'basePath' => '@app/modules/V3s3/messages',
			'fileMap' => [
				'modules/V3s3/V3s3' => 'V3s3.php',
			],
        ];
    }

	public static function t($category, $message, $params = [], $language = null) {
		return YII2_Yii::t('modules/V3s3/' . $category, $message, $params, $language);
	}
}
