<?php
namespace ferrumfist\yii2\starrating;

use Yii;
use yii\web\Controller;

abstract class DefaultController extends Controller{

    public $url = '/rating';

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
     * @return string json
     */
    abstract protected function save($id, $score);

    abstract public function getRating();
}