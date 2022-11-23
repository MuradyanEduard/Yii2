<?php

namespace app\controllers;

use app\models\Book;
use app\models\BookSearch;
use app\models\Order;
use app\models\OrderProduct;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\BaseJson;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index', 'create', 'add-product', 'remove-product', 'view'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'actions' => ['index'],
                ],
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'actions' => ['add-product', 'remove-product', 'create'],
                    'matchCallback' => function ($rule, $action) {
                        if (Yii::$app->user->getIdentity()->role == User::USER_ROLE)
                            return false;

                        return true;
                    }
                ]
            ],

        ];

        return $behaviors;
    }

    /**
     * Lists all Order models.
     *
     * @return string
     */


    public function actionIndex()
    {
        switch (Yii::$app->user->identity->role) {
            case User::ADMIN_ROLE:
                $searchModel = new Order();
                $dataProvider = $searchModel->search($this->request->queryParams);
                break;
            case User::USER_ROLE:
                $searchModel = new BookSearch();
                $dataProvider = $searchModel->searchByAuthorId($this->request->queryParams, Yii::$app->user->identity->id);
                break;
            case  User::CUSTOMER_ROLE:
                $searchModel = new Order();
                $dataProvider = $searchModel->searchByUserId($this->request->queryParams, Yii::$app->user->identity->id);
                break;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    /**
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        if (!Yii::$app->getRequest()->getCookies()->has('products') ||
            (Yii::$app->getRequest()->getCookies()->getValue('products') == []))
            return $this->redirect('/book');

        if ($this->request->isPost) {

            $order = new Order();
            $order->load([
                'Order' => [
                    'user_id' => Yii::$app->user->identity->id,
                    'total_sum' => 0
                ]
            ]);
            $order->save();

            $total_sum = 0;
            $productIds = Yii::$app->getRequest()->getCookies()->getValue('products');

            foreach ($productIds as $productId) {

                $book = Book::findOne($productId['id']);
                $count = intval($book->count < $productId['count'] ? $book->count : $productId['count']);

                $orderProduct = new OrderProduct();
                $orderProduct->load([
                    'OrderProduct' => [
                        'order_id' => $order->id,
                        'book_id' => $productId['id'],
                        'count' => $count,
                        'price' => $book->price * $count
                    ]
                ]);

                if ($orderProduct->validate()) {
                    $orderProduct->save();

                    $total_sum = $total_sum + ($count * $book->price);
                    $book->count -= $count;
                    $book->authorsArr = [''];
                    $book->save();
                }

            }

            $cookie = new \yii\web\Cookie([
                'name' => 'products',
                'value' => [],
            ]);
            Yii::$app->getResponse()->getCookies()->add($cookie);

            $order->total_sum = $total_sum;
            $order->save();
        }

        return $this->redirect('/book');
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAddProduct()
    {
        $newBook = new Book();
        $newBook->load($this->request->post());
        $book = Book::findOne($newBook->id);

        $products = [];

        if (Yii::$app->getRequest()->getCookies()->has('products')) {
            $products = Yii::$app->getRequest()->getCookies()->getValue('products');
        }

        for ($i = 0; $i < count($products); $i++) {
            if ($newBook->id == $products[$i]['id']) {
                $products[$i]['count'] = ($book->count < ($products[$i]['count'] + $newBook->count)) ?
                    ($book->count) : ($products[$i]['count'] + $newBook->count);
                break;
            }
        }

        if ($i == count($products)) {
            array_push($products, [
                'id' => $newBook->id,
                'count' => ($book->count < $newBook->count) ? ($book->count) : ($newBook->count)
            ]);
        }

        $cookie = new \yii\web\Cookie([
            'name' => 'products',
            'value' => $products,
        ]);

        Yii::$app->getResponse()->getCookies()->add($cookie);
        $this->redirect('/book');
    }

    public function actionRemoveProduct()
    {
        $id = $this->request->post()['id'];
        $products = [];

        if (Yii::$app->getRequest()->getCookies()->has('products')) {
            $products = Yii::$app->getRequest()->getCookies()->getValue('products');
        }

        foreach ($products as $key => $product) {
            if ($id == $product['id']) {
                unset($products[$key]);
            }
        }

        $cookie = new \yii\web\Cookie([
            'name' => 'products',
            'value' => $products,
        ]);

        Yii::$app->getResponse()->getCookies()->add($cookie);
        $this->redirect('/book');
    }


}

