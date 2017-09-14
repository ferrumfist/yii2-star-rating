<?php

namespace ferrumfist\yii2\starrating;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use simialbi\yii2\schemaorg\models\AggregateRating;
use simialbi\yii2\schemaorg\models\Article;
use simialbi\yii2\schemaorg\helpers\JsonLDHelper;

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

    public $googleOptons = [];

    /**
     * @var string the input value.
     */
    public $value = 0;

    /**
     * @var Voter number
     */
    public $voices = 0;

    /**
     * show caption
     * @var bool
     */
    public $caption = true;

    public $captionOption = [];

    /**
     * Init widget, configure client options
     */
    public function init()
    {
        parent::init();

        $this->registerTranslations();

        $this->googleOptons = ArrayHelper::merge([
            'headline' => Yii::$app->controller->view->title,
            'author' => Yii::$app->name,
            'datePublished' => date('c'), //ISO 8601
            'publisher' => Yii::$app->name,
            'image' => ''
        ], $this->googleOptons);

        $this->captionOption = ArrayHelper::merge([
            'id' => $this->options['id'].'_caption',
            'score' => 'rating__score',
            'voices' => 'rating__voices'
        ], $this->captionOption);

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

        $article = new Article();
        $article->headline = $this->googleOptons['headline'];
        $article->author = $this->googleOptons['author'];
        $article->datePublished = $this->googleOptons['datePublished'];
        $article->publisher = $this->googleOptons['publisher'];
        $article->image = $this->googleOptons['image'];

        $rating = new AggregateRating();
        $rating->ratingValue = $this->clientOptions['score'];
        $rating->ratingCount = $this->voices;
        $rating->itemReviewed = [$article];

        JsonLDHelper::add($rating);
        JsonLDHelper::render();

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

        if( $this->hasModel() )
            $this->voices = $this->model->voices;

        $clientOptions = Json::encode($this->clientOptions);

        $this->clientOptions['click'] = new JsExpression("function(score){
                var target = $(this);
                var data = {id:'id', score:score};
                var opt = $clientOptions;
                
                $.ajax({
                  type: 'POST',
                  url: '/rating',
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
