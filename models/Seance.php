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
 * @property integer $good_id
 * @property integer $price
 * @property integer $client_id
 * @property integer $status
 * @property integer $booking_now
 * @property string $booking_start_at
 * @property integer $booking_code
 *
 * @property Client[] $clients
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
            [['date', 'time', 'duration', 'good_id', 'price', 'status'], 'required'],
            [['date', 'time', 'booking_start_at'], 'safe'],
            [['duration', 'good_id', 'price', 'client_id', 'status', 'booking_now', 'booking_code'], 'integer'],
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
            'good_id' => 'Good ID',
            'price' => 'Price',
            'client_id' => 'Client ID',
            'status' => 'Status',
            'booking_now' => 'Booking Now',
            'booking_start_at' => 'Booking Start At',
            'booking_code' => 'Booking Code',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClients()
    {
        return $this->hasMany(Client::className(), ['seance_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGood()
    {
        return $this->hasOne(Good::className(), ['id' => 'good_id']);
    }
}
