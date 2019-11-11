<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "seance".
 *
 * @property integer $id
 * @property string $date
 * @property string $time
 * @property integer $duration
 * @property integer $seance_status
 * @property integer $good_id
 * @property integer $price
 * @property integer $client_id
 *
 * @property Good $good
 */
class Seance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'seance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'time', 'duration', 'seance_status', 'good_id', 'price'], 'required'],
            [['date', 'time'], 'safe'],
            [['duration', 'seance_status', 'good_id', 'price', 'client_id'], 'integer'],
            [['good_id'], 'exist', 'skipOnError' => true, 'targetClass' => Good::className(), 'targetAttribute' => ['good_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'time' => 'Time',
            'duration' => 'Duration',
            'seance_status' => 'Seance Status',
            'good_id' => 'Good ID',
            'price' => 'Price',
            'client_id' => 'Client ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGood()
    {
        return $this->hasOne(Good::className(), ['id' => 'good_id']);
    }
}
