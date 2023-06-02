<?php
use Elastic\Elasticsearch\ClientBuilder;

class Elasticsearch
{
    private static $client;

    public static function getClient()
    {
        if (!isset(self::$client)) {
            self::$client = ClientBuilder::create()
                ->setHosts(['localhost:9200'])
                ->setBasicAuthentication("elastic", "fxW8G-CfOIrK5R*Tecpy")
                ->build();
        }
        return self::$client;
    }
}
