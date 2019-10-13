<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "good".
 *
 * @property int $id
 * @property string $name
 * @property int $price
 * @property int $duration
 * @property int $specialist_id
 * @property int $invisible
 *
 * @property Specialist $specialist
 * @property Seance[] $seances
 */
class Good extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'good';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'price', 'duration', 'specialist_id'], 'required'],
            [['price', 'duration', 'specialist_id', 'invisible'], 'integer'],
            [['name'], 'string', 'max' => 45],
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
            'name' => 'Name',
            'price' => 'Price',
            'duration' => 'Duration',
            'specialist_id' => 'Specialist ID',
            'invisible' => 'Invisible',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Specialist::className(), ['id' => 'specialist_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeances()
    {
        return $this->hasMany(Seance::className(), ['good_id' => 'id']);
    }
}
