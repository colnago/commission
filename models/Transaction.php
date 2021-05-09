<?php

namespace app\models;

use yii\base\Model;

class Transaction extends Model
{
    public $date;
    public $user_id;
    public $user_type;
    public $operation_type;
    public $amount;
    public $currency;

    public function rules()
    {
        return [
            [['date', 'user_id', 'user_type', 'operation_type', 'amount', 'currency'], 'required'],
            ['date', 'date', 'format' => 'Y-m-d'],
            ['user_id', 'integer', 'min' => 1],
            ['amount', 'double', 'min' => 0],
            ['user_type', 'in', 'range' => \Yii::$app->params['userTypes']],
            ['operation_type', 'in', 'range' => \Yii::$app->params['operationTypes']],
            ['currency', 'in', 'range' => \Yii::$app->params['currencies']]
        ];
    }

    public function attributeLabels()
    {
        return [
            'date' => 'Date',
            'user_id' => 'User ID',
            'user_type' => 'User Type',
            'operation_type' => 'Operation Type',
            'amount' => 'Operation Amount',
            'currency' => 'Operation Currency'
        ];
    }

}
