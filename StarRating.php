<?php

namespace ferrumfist\yii2\starrating;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class StarRating
 *
 * @package yii2mod\rating
 */
class StarRating extends InputWidget
{
    /**
     * Path to plugin images
     */
    public $assetBundleImagePath = '/lib/images';

    /**
     * @var array client options
     */
    public $clientOptions = [];

    public $value = 0;

    /**
     * @var votes number
     */
    public $votes = 0;

    /**
     * show caption
     * @var bool
     */
    public $caption = true;

    /**
     * Init widget, configure client options
     */
    public function init()
    {
        parent::init();

        $this->configureClientOptions();
    }

    /**
     * Render star rating
     *
     * @return string
     */
    public function run(){
        $this->registerAssets();

        $return = Html::tag('div', '', $this->options);

        if ($this->caption){
            $caption = \Yii::t('app', 'Rating: ')
            .Html::tag('span', $this->clientOptions['score'], ['class'=>'rating__score']).", "
            .\Yii::t('app', 'Votes: ')
            .Html::tag('span', $this->votes, ['class'=>'rating__voices']);

            $return .= Html::tag('div', $caption, ['class'=>'rating__details']);
        }

        return $return;
    }

    /**
     * Register client assets
     *
     * return @void
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        $clientOptions = Json::encode($this->clientOptions);
        $js = '$("div#' . $this->options['id'] . '").raty(' . $clientOptions . ');';
        $view->registerJs($js);
    }

    /**
     * Configure client options
     */
    protected function configureClientOptions()
    {
        $assetBundle = StarRatingAsset::register($this->view);

        if (!isset($this->clientOptions['score'])) {
            $this->clientOptions['score'] = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        }

        if (!isset($this->clientOptions['path'])) {
            $this->clientOptions['path'] = $assetBundle->baseUrl . $this->assetBundleImagePath;
        }

        if (!isset($this->clientOptions['scoreName']) && $this->hasModel()) {
            $this->clientOptions['scoreName'] = Html::getInputName($this->model, $this->attribute);
        }
    }
}
