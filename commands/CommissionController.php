<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Transaction;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CommissionController extends Controller
{

    public static function convertCurrency($amount, $from = 'EUR', $to = 'USD')
    {
        $amount = strtoupper($amount);
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from == $to) {
            return $amount;
        }
        $url = 'https://api.exchangerate.host/latest?base=' . $from . '&symbols=' . $to . '&amount=' . $amount;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($data, true);
        return $json['rates'][$to];
    }

    public static function actionCalculation($filename)
    {
        //get const
        $free_amount = \Yii::$app->params['freeAmount'];
        $free_amount_currency = \Yii::$app->params['freeAmountCurrency'];
        $free_operation_count = \Yii::$app->params['freeOperationCount'];
        $user_types = \Yii::$app->params['userTypes'];
        $operation_types = \Yii::$app->params['operationTypes'];
        $commissions = \Yii::$app->params['commissions'];
        $inputPath = \Yii::$app->params['inputPath'];
        //read file
        $path = \Yii::$app->basePath . $inputPath . $filename;
        if (!is_file($path)) {
            return ExitCode::NOINPUT;
        }
        $handle = fopen($path, "r");
        $model = new Transaction();
        $fields = $model->attributes();
        $rows = [];
        //load data to array
        for ($i = 0; $row = fgetcsv($handle); ++$i) {
            $data = [];
            foreach ($fields as $k => $v) {
                $data[$v] = $row[$k];
            }
            $model = new Transaction();
            //validate data
            if ($model->load($data, '') && $model->validate()) {
                $rows[] = $data;
            } else {
                return ExitCode::IOERR;
            }
        }
        fclose($handle);
        //calculate commission for each transaction
        foreach ($rows as $k => $row) {
            $date = $row['date'];
            $user_id = $row['user_id'];
            $user_type = $row['user_type'];
            $operation_type = $row['operation_type'];
            $amount = $row['amount'];
            $currency = $row['currency'];
            $d = new \DateTime($date);
            $d->modify('monday this week');
            $begin_date = $d->format('Y-m-d');
            $commission = 0;
            switch ($user_type . $operation_type) {
                // commission for private withdraw
                case $user_types['type1'] . $operation_types['type2']:
                    $weekly_amount = $weekly_operation_count = 0;
                    for ($i = 0; $i < $k; $i++) {
                        if ($user_id !== $rows[$i]['user_id'] ||
                            $user_type !== $rows[$i]['user_type'] ||
                            $operation_type !== $rows[$i]['operation_type']) continue;
                        if (strtotime($begin_date) <= strtotime($rows[$i]['date']) &&
                            strtotime($row['date']) <= strtotime($date)) {
                            $weekly_operation_count++;
                            $weekly_amount += self::convertCurrency($rows[$i]['amount'], $rows[$i]['currency'], $currency);
                        }
                    }
                    $free = self::convertCurrency($free_amount, $free_amount_currency, $currency);
                    if ($weekly_operation_count >= $free_operation_count || $weekly_amount >= $free) {
                        $commission = $amount * $commissions[$user_types['type1']][$operation_types['type2']] / 100;
                    } elseif (($weekly_amount + $amount) > $free) {
                        $commission = ($weekly_amount + $amount - $free) * $commissions[$user_types['type1']][$operation_types['type2']] / 100;
                    }
                    break;
                // commission for private deposit
                case $user_types['type1'] . $operation_types['type1']:
                    $commission = $amount * $commissions[$user_types['type1']][$operation_types['type1']] / 100;
                    break;
                // commission for business withdraw
                case $user_types['type2'] . $operation_types['type2']:
                    $commission = $amount * $commissions[$user_types['type2']][$operation_types['type2']] / 100;
                    break;
                // commission for business deposit
                case $user_types['type2'] . $operation_types['type1']:
                    $commission = $amount * $commissions[$user_types['type2']][$operation_types['type1']] / 100;
                    break;
            }
            $commission = ceil($commission * 100) / 100;
            $commission = sprintf("%01.2f", $commission);
            echo $commission . "\n";
        }
        return ExitCode::OK;
    }
}
