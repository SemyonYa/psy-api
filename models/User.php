<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $organization_name
 * @property integer $specialists_quantity
 * @property string $email
 * @property string $phone
 * @property integer $parent_user_id
 * @property string $login
 * @property string $password_hash
 * @property string $access_token
 * @property string $refresh_token
 * @property integer $blocked
 * @property string $role
 * @property integer $specialist_id
 *
 * @property Specialist[] $specialists
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_name', 'email', 'phone', 'login', 'role'], 'required'],
            [['specialists_quantity', 'parent_user_id', 'specialist_id'], 'integer'],
            [['password_hash', 'access_token', 'refresh_token'], 'string'],
            [['organization_name'], 'string', 'max' => 150],
            [['email'], 'string', 'max' => 50],
            [['phone', 'login'], 'string', 'max' => 20],
            [['blocked'], 'string', 'max' => 4],
            [['role'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_name' => 'Organization Name',
            'specialists_quantity' => 'Specialists Quantity',
            'email' => 'Email',
            'phone' => 'Phone',
            'parent_user_id' => 'Parent User ID',
            'login' => 'Login',
            'password_hash' => 'Password Hash',
            'access_token' => 'Access Token',
            'refresh_token' => 'Refresh Token',
            'blocked' => 'Blocked',
            'role' => 'Role',
            'specialist_id' => 'Specialist ID',
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
