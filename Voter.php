<?php
namespace ferrumfist\yii2\starrating;

class Voter{
    protected $url;

    public function __construct($url = null){
        if( $url )
            $this->setUrl($url);
    }

    public function setUrl($url){
        $this->url = $url;
    }

    public function getUrl(){
        return $this->url;
    }
}