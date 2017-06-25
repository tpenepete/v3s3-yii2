<?php

namespace app\modules\V3s3\models\table;

use Yii as YII2_Yii;
use yii\db\ActiveRecord as YII2_ActiveRecord;

/**
 * This is the model class for table "store".
 *
 * @property integer $id
 * @property integer $timestamp
 * @property string $date_time
 * @property string $ip
 * @property string $hash_name
 * @property resource $name
 * @property string $data
 * @property string $mime_type
 * @property integer $status
 * @property integer $timestamp_deleted
 * @property string $date_time_deleted
 * @property string $ip_deleted_from
 */
class V3s3Table extends YII2_ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'store';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['timestamp', 'status', 'timestamp_deleted'], 'integer'],
            [['name', 'data'], 'string'],
            [['date_time', 'date_time_deleted'], 'string', 'max' => 25],
            [['ip', 'ip_deleted_from'], 'string', 'max' => 15],
            [['hash_name'], 'string', 'max' => 40],
            [['mime_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => YII2_Yii::t('V3s3', 'ID'),
            'timestamp' => YII2_Yii::t('V3s3', 'Timestamp'),
            'date_time' => YII2_Yii::t('V3s3', 'Date Time'),
            'ip' => YII2_Yii::t('V3s3', 'Ip'),
            'hash_name' => YII2_Yii::t('V3s3', 'Hash Name'),
            'name' => YII2_Yii::t('V3s3', 'Name'),
            'data' => YII2_Yii::t('V3s3', 'Data'),
            'mime_type' => YII2_Yii::t('V3s3', 'Mime Type'),
            'status' => YII2_Yii::t('V3s3', 'Status'),
            'timestamp_deleted' => YII2_Yii::t('V3s3', 'Timestamp Deleted'),
            'date_time_deleted' => YII2_Yii::t('V3s3', 'Date Time Deleted'),
            'ip_deleted_from' => YII2_Yii::t('V3s3', 'Ip Deleted From'),
        ];
    }

	public function put(Array $attr) {
		$attr = array_intersect_key($attr, $this->attributes);
		$attr['timestamp'] = (isset($attr['timestamp'])?$attr['timestamp']:time());
		$attr['date_time'] = date('Y-m-d H:i:s O', $attr['timestamp']);
		if(isset($attr['name'])) {
			$attr['hash_name'] = sha1($attr['name']);
		} else {
			unset($attr['hash_name']);
		}
		$attr['status'] = (isset($attr['status'])?$attr['status']:1);
		unset($attr['id']);

		$this->attributes = $attr;

		$this->save();

		return $this;
	}

	public function get(Array $attr) {
		$attr = array_intersect_key($attr, $this->attributes);
		if(isset($attr['name'])) {
			$attr['hash_name'] = sha1($attr['name']);
		} else {
			unset($attr['hash_name']);
		}
		unset($attr['name']);

		$row = static::find()->asArray()->where($attr)->orderBy(['id'=>SORT_DESC])->all();

		$rows_count = count($row);
		if(empty($rows_count)) {
			return false;
		}

		return reset($row);
	}

	public function api_delete(Array $attr) {
		$attr = array_intersect_key($attr, $this->attributes);
		$attr['timestamp_deleted'] = (isset($attr['timestamp_deleted'])?$attr['timestamp_deleted']:time());
		$attr['date_time_deleted'] = date('Y-m-d H:i:s O', $attr['timestamp_deleted']);
		if(isset($attr['name'])) {
			$attr['hash_name'] = sha1($attr['name']);
		} else {
			unset($attr['hash_name']);
		}
		$attr['status'] = (isset($attr['status'])?$attr['status']:0);
		unset($attr['name']);

		$where = $attr;
		unset($where['status']);
		unset($where['timestamp_deleted']);
		unset($where['date_time_deleted']);
		unset($where['ip_deleted_from']);
		$row_array = static::find()->asArray()->where($where)->orderBy(['id'=>SORT_DESC])->all();

		$rows_count = count($row_array);
		if(empty($rows_count)) {
			return false;
		}

		$row = static::find()->where($where)->orderBy(['id'=>SORT_DESC])->one();
		$row->attributes = array_replace(reset($row_array), $attr);
		$row->save();

		return $row;
	}

	public function post(Array $attr) {
		$attr = array_intersect_key($attr, $this->attributes);
		if(isset($attr['name'])) {
			$attr['hash_name'] = sha1($attr['name']);
		} else {
			unset($attr['hash_name']);
		}
		unset($attr['name']);

		$rows = static::find()->asArray()->where($attr)->all();
		$rows_count = count($rows);

		return (!empty($rows_count)?$rows:[]);
	}
}
