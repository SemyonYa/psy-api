<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "workday".
 *
 * @property int $id
 * @property string $date
 * @property int $specialist_id
 *
 * @property Seance[] $seances
 * @property Specialist $specialist
 */
class Workday extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workday';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['specialist_id'], 'required'],
            [['specialist_id'], 'integer'],
            [['specialist_id'], 'exist', 'skipOnError' => true, 'targetClass' => Specialist::className(), 'targetAttribute' => ['specialist_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'specialist_id' => 'Specialist ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeances()
    {
        return $this->hasMany(Seance::className(), ['workday_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Specialist::className(), ['id' => 'specialist_id']);
    }
}
