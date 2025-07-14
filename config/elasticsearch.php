<?php

use Elastic\Elasticsearch\ClientBuilder;

    return [
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'localhost:9200'),
        ],
        // 'retries' => 2,
        // 'handler' => ClientBuilder::defaultHandler(),
        // 'logger' => ClientBuilder::defaultLogger(storage_path('logs/elasticsearch.log')),
        // 'tracer' => ClientBuilder::defaultTracer(),
        'connectionPool' => '\Elasticsearch\ConnectionPool\StaticNoPingConnectionPool',
        'selector' => '\Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector',
        'serializer' => '\Elasticsearch\Serializers\SmartSerializer',
        'sniffOnStart' => false,
        'retryOnConflict' => 0,
        'timeout' => 30,
        'connectionTimeout' => 30,
    ];
?>
