<?php
namespace ferrumfist\yii2\starrating;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Controller;
use yii\web\Response;

abstract class DefaultController extends Controller{

    public function behaviors()
    {
        return [
            [
                'class' => ContentNegotiator::className(),
                'only' => ['index', 'save'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $id = $request->post('id');
        $score = $request->post('score');

        return $this->save($id, $score);
    }

    /**
     * @param $id
     * @param $score
     * @return array
     */
    abstract protected function save($id, $score);
}