<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "seance_status".
 *
 * @property int $id
 * @property string $name
 *
 * @property Seance[] $seances
 */
class SeanceStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'seance_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 45],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeances()
    {
        return $this->hasMany(Seance::className(), ['seance_status_id' => 'id']);
    }
}
