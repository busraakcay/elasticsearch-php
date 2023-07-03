<?php

class AdvertModel
{
    private $elasticsearch;

    public function __construct()
    {
        $this->elasticsearch = new ElasticsearchHelpers();
    }

    public function getAdverts()
    {
        return $this->elasticsearch->getAdverts();
    }

    public function getAdvertDetail($id)
    {
        return $this->elasticsearch->getAdvertById($id);
    }

    public function setAdvert($request, $type)
    {
        if ($type === "store") {
            return $this->elasticsearch->indexAdvert($request);
        }
        if ($type === "update") {
            return $this->elasticsearch->updateAdvert($request);
        }
    }
}
