<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "client".
 *
 * @property integer $id
 * @property string $name
 * @property string $wish
 * @property integer $phone
 * @property string $email
 * @property integer $seance_id
 *
 * @property Seance $seance
 */
class Client extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'client';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'phone', 'email', 'seance_id'], 'required'],
            [['wish'], 'string'],
            [['phone', 'seance_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 45],
            [['seance_id'], 'exist', 'skipOnError' => true, 'targetClass' => Seance::className(), 'targetAttribute' => ['seance_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'wish' => 'Wish',
            'phone' => 'Phone',
            'email' => 'Email',
            'seance_id' => 'Seance ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeance()
    {
        return $this->hasOne(Seance::className(), ['id' => 'seance_id']);
    }
}
