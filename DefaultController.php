<?php
namespace ferrumfist\yii2\starrating;

use yii\web\Controller;

class DefaultController extends Controller{

    public function actionIndex(){
        echo json_encode([
            'score' => 3.7,
            'voices' => 516
        ]);
    }
}