<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "seance".
 *
 * @property int $id
 * @property string $time
 * @property int $duration
 * @property int $workday_id
 * @property int $seance_status_id
 * @property int $good_id
 * @property int $price
 *
 * @property Good $good
 * @property SeanceStatus $seanceStatus
 * @property Workday $workday
 */
class Seance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'seance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['time', 'duration', 'workday_id', 'seance_status_id', 'good_id', 'price'], 'required'],
            [['time'], 'safe'],
            [['duration', 'workday_id', 'seance_status_id', 'good_id', 'price'], 'integer'],
            [['good_id'], 'exist', 'skipOnError' => true, 'targetClass' => Good::className(), 'targetAttribute' => ['good_id' => 'id']],
            [['seance_status_id'], 'exist', 'skipOnError' => true, 'targetClass' => SeanceStatus::className(), 'targetAttribute' => ['seance_status_id' => 'id']],
            [['workday_id'], 'exist', 'skipOnError' => true, 'targetClass' => Workday::className(), 'targetAttribute' => ['workday_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'time' => 'Time',
            'duration' => 'Duration',
            'workday_id' => 'Workday ID',
            'seance_status_id' => 'Seance Status ID',
            'good_id' => 'Good ID',
            'price' => 'Price',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGood()
    {
        return $this->hasOne(Good::className(), ['id' => 'good_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeanceStatus()
    {
        return $this->hasOne(SeanceStatus::className(), ['id' => 'seance_status_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorkday()
    {
        return $this->hasOne(Workday::className(), ['id' => 'workday_id']);
    }
}
