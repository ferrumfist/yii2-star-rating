<?php

namespace ferrumfist\yii2\starrating;

use yii\web\AssetBundle;

/**
 * Class StarRatingAsset
 *
 * @package yii2mod\rating
 */
class StarRatingAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/raty';

    /**
     * @var array
     */
    public $js = [
        'lib/jquery.raty.js',
    ];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
