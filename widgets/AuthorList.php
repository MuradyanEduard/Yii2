<?php


namespace app\widgets;


use app\models\Author;
use app\models\AuthorSearch;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;

class AuthorList extends Widget
{
    public $book;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        ob_start();
    }

    public function run()
    {
        parent::run(); // TODO: Change the autogenerated stub
        $content = ob_get_clean();

        $searchModel = new AuthorSearch();
        $dataProvider = new ArrayDataProvider([
            'allModels' => $this->book->authors,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['id', 'name'],
            ],
        ]);

        return GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                'id',
                'name',
            ],
        ]);

    }
}