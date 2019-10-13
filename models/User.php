<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $name
 * @property string $organization_name
 * @property int $specialists_quantity
 * @property int $parent_user_id
 * @property string $login
 * @property string $password_hash
 * @property string $access_token
 * @property string $refresh_token
 * @property int $blocked
 *
 * @property Specialist[] $specialists
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_name', 'login'], 'required'],
            [['specialists_quantity', 'parent_user_id', 'blocked'], 'integer'],
            [['password_hash', 'access_token', 'refresh_token'], 'string'],
            [['login'], 'string', 'max' => 45],
            [['organization_name'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_name' => 'Organization Name',
            'specialists_quantity' => 'Specialists Quantity',
            'parent_user_id' => 'Parent User ID',
            'login' => 'Login',
            'password_hash' => 'Password Hash',
            'access_token' => 'Access Token',
            'refresh_token' => 'Refresh Token',
            'blocked' => 'Blocked',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialists()
    {
        return $this->hasMany(Specialist::className(), ['user_id' => 'id']);
    }
}
