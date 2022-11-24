<?php


namespace app\controllers;


use app\models\User;
use Yii;
use yii\web\Controller;

class OrderViewController extends Controller
{
    public function actionIndex()
    {
        switch (Yii::$app->user->identity->role) {
            case User::ADMIN_ROLE:
                $this->redirect('order/admin');
                break;
            case User::USER_ROLE:
                $this->redirect('order/user');
                break;
            case  User::CUSTOMER_ROLE:
                $this->redirect('order/customer');
                break;
        }
    }
}