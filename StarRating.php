<?php

namespace ferrumfist\yii2\starrating;

use simialbi\yii2\schemaorg\helpers\JsonLDHelper;
use simialbi\yii2\schemaorg\models\Thing;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use simialbi\yii2\schemaorg\models\AggregateRating;

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

    /** @var Thing $itemReviewed */
    public $itemReviewed = null;

    /**
     * @var string the input value.
     */
    public $value = 0;

    public $voices = 0;

    /**
     * show caption
     * @var bool
     */
    public $caption = true;

    public $captionOption = [];

    public $ratingId;

    public $attribute = 'score';
    public $voicesAttribute = 'voices';
    public $clickUrl;

    /**
     * Init widget, configure client options
     */
    public function init()
    {
        parent::init();

        $this->registerTranslations();

        $this->captionOption = ArrayHelper::merge([
            'id' => $this->options['id'].'_caption',
            'score' => 'rating__score',
            'voices' => 'rating__voices'
        ], $this->captionOption);

        if( $this->hasModel() ) {
            $this->ratingId = $this->model->id;
        }

        if( !isset($this->ratingId) ){
            throw new InvalidConfigException('You must set "ratingId" param');
        }

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
            $caption = \Yii::t('starRating', 'Rating').": "
            .Html::tag('span', $this->clientOptions['score'], ['class'=>$this->captionOption['score']])." "
            .\Yii::t('starRating', 'Voices').": "
            .Html::tag('span', $this->voices, ['class'=>$this->captionOption['voices']]);

            $return .= Html::tag('div', $caption, ['id'=>$this->captionOption['id']]);
        }

        if( $this->itemReviewed ){
            $rating = new AggregateRating();
            $rating->ratingValue = $this->clientOptions['score'];
            $rating->ratingCount = $this->voices;
            $rating->itemReviewed = $this->itemReviewed;

            JsonLDHelper::add($rating);
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

        if(!isset($this->clientOptions['hints'])){
            $this->clientOptions['hints'] = [
                \Yii::t('starRating', 'bad'),
                \Yii::t('starRating', 'poor'),
                \Yii::t('starRating', 'regular'),
                \Yii::t('starRating', 'good'),
                \Yii::t('starRating', 'gorgeous')
            ];
        }

        if(!isset($this->clientOptions['noRatedMsg'])){
            $this->clientOptions['noRatedMsg'] = \Yii::t('starRating', 'Not rated yet!');
        }

        if( $this->hasModel() ) {
            $this->voices = $this->model->{$this->voicesAttribute};
            $this->clientOptions['readOnly'] = $this->model->readOnly;
        }

        $clientOptions = Json::encode($this->clientOptions);

        $this->clientOptions['click'] = new JsExpression("function(score){
                var target = $(this);
                var data = {id:'{$this->ratingId}', score:score};
                var opt = $clientOptions;
                
                $.ajax({
                  type: 'POST',
                  url: '{$this->clickUrl}',
                  data: data,
                  success: function(data){
                    opt.score = data.score;
                    opt.readOnly = true;
                    
                    target.raty(opt);
                    
                    //caption update
                    $('div#{$this->captionOption['id']} .{$this->captionOption['score']}').text(data.score);
                    $('div#{$this->captionOption['id']} .{$this->captionOption['voices']}').text(data.voices);
                  },
                  dataType: 'json'
                });
            }
        ");
    }

    public function registerTranslations(){
        $i18n = Yii::$app->i18n;
        $i18n->translations['starRating'] = [
            'class'          => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath'       => __DIR__.'/messages',
            'fileMap'        => [
                'starRating' => 'translate.php',
            ],
        ];
    }
}
