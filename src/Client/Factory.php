<?php namespace EloquentEs\Client;

use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class Factory
 * @package EloquentEs\Client\Elastic
 */
class Factory
{
    /**
     * Map configuration array keys with ES ClientBuilder setters
     *
     * @var array
     */
    protected $configMappings = [
        'sslVerification'    => 'setSSLVerification',
        'sniffOnStart'       => 'setSniffOnStart',
        'retries'            => 'setRetries',
        'httpHandler'        => 'setHandler',
        'connectionPool'     => 'setConnectionPool',
        'connectionSelector' => 'setSelector',
        'serializer'         => 'setSerializer',
        'connectionFactory'  => 'setConnectionFactory',
        'endpoint'           => 'setEndpoint',
    ];
    /**
     * Make the Elasticsearch client for the given named configuration, or
     * the default client.
     *
     * @param array $config
     * @return \Elasticsearch\Client|mixed
     */
    public function make(array $config)
    {
        // Build the client
        return $this->buildClient($config);
    }
    /**
     * Build and configure an Elasticsearch client.
     *
     * @param array $config
     * @return \Elasticsearch\Client
     */
    protected function buildClient(array $config)
    {
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts($config['hosts']);
        if ($config['sslVerification']) {
            $clientBuilder->setSSLVerification(true);
        }

        // Configure logging
        if (empty($config['logging'])) {
            $logObject = !empty($config['logObject']) ? $config['logObject'] : null;
            $logPath = !empty($config['logPath']) ? $config['logPath'] : null;
            $logLevel = !empty($config['logLevel']) ? $config['logLevel'] : null;

            if ($logObject && $logObject instanceof LoggerInterface) {
                $clientBuilder->setLogger($logObject);

            } else if ($logPath && $logLevel) {
                $logObject = ClientBuilder::defaultLogger($logPath, $logLevel);
                $clientBuilder->setLogger($logObject);
            }
        }

        // Set additional client configuration
        foreach ($this->configMappings as $key => $method) {
            $value = $config[$key];
            if ($value !== null) {
                call_user_func([$clientBuilder, $method], $value);
            }
        }
        // Build and return the client
        return $clientBuilder->build();
    }
}