<?php namespace EloquentEs\Supports;

use EloquentEs\EloquentEsFacade as Search;

/**
 * Trait ElasticModelTrait
 * @package EloquentEs\Supports
 */
trait ElasticModelTrait
{
    /**
     * Elastic index name
     *
     * @var string
     */
    protected $esIndexName = null;

    /**
     * Type name
     *
     * @var string
     */
    protected $esTypeName = null;

    /**
     * Elastic id prefix
     *
     * @mixin
     */
    protected $esIdPrefix = '';

    /**
     * Elastic index settings
     *
     * @var array
     */
    protected $esIndexSettings = null;

    /**
     * Include _source field in document
     *
     * @var bool
     */
    protected $esSourceEnable = true;

    /**
     * Get the primary key for the elastic document.
     *
     * @return string
     */
    public function getEsKeyName()
    {
        return $this->getKeyName();
    }

    /**
     * Get elastic id
     *
     * @return mixed
     */
    public function getEsId()
    {
        return $this->esIdPrefix.$this->getKey();
    }

    /**
     * Get elastic index name
     *
     * @return string
     */
    public function getEsIndexName()
    {
        return $this->esIndexName ?: $this->getTable();
    }

    /**
     * Get elastic type name
     *
     * @return string
     */
    public function getEsTypeName()
    {
        return $this->esTypeName ?: $this->getTable();
    }

    /**
     * Get elastic index mapping
     *
     * @return array
     */
    public function getEsIndexMapping()
    {
        return property_exists(__CLASS__, 'esIndexMapping') ? $this->esIndexMapping : null;
    }

    /**
     * Get elastic index settings
     *
     * @return array
     */
    public function getEsIndexSettings()
    {
        return $this->esIndexSettings ?: config('elastic.defaultIndexSettings');
    }

    /**
     * Create elastic index
     *
     * @param int $shards
     * @param int $replicas
     *
     * @return array
     */
    public static function esCreateIndex($shards = null, $replicas = null)
    {
        $instance = new static;
        $index = ['index' => $instance->getEsIndexName()];
        $settings = $instance->getEsIndexSettings();

        if (!empty($settings)) {
            $index['body']['settings'] = $settings;
        }

        if (!empty($shards)) {
            $index['body']['settings']['number_of_shards'] = $shards;
        }

        if (!empty($replicas)) {
            $index['body']['settings']['number_of_replicas'] = $replicas;
        }

        $mapping = $instance->getEsIndexMapping();

        if (!empty($mapping)) {
            $index['body']['mappings'][$instance->getEsTypeName()] = [
                '_source' => ['enabled' => $instance->esSourceEnable],
                'properties' => $mapping,
            ];
        }

        return Search::indices()->create($index);
    }

    /**
     * Delete elastic index
     *
     * @return array
     */
    public static function esDeleteIndex()
    {
        $instance = new static;
        return Search::indices()->delete(['index' => $instance->getEsIndexName()]);
    }

    /**
     * Put index mapping
     *
     * @return bool
     */
    public static function esPutMapping()
    {
        $instance = new static;
        $mapping = $instance->getEsIndexMapping();
        $params = [
            'index' => $instance->getEsIndexName(),
            'type' => $instance->getEsTypeName(),
        ];

        if (!empty($mapping)) {
            $params['body'][$instance->getEsTypeName()] = [
                '_source' => ['enabled' => $instance->esSourceEnable],
                'properties' => $mapping,
            ];

            return Search::indices()->putMapping($params);
        }

        return false;
    }

    /**
     * Delete & create empty index with mapping settings.
     * Warning: It very dangerously
     */
    public static function esReset()
    {
        self::esDeleteIndex();
        self::esCreateIndex();
        self::esPutMapping();
    }

    /**
     * Object data
     *
     * @return mixed
     */
    public function getEsData()
    {
        $data = method_exists(__CLASS__, 'esSerialize')
            ? $this->esSerialize() : $this->toArray();

        return $data;
    }

    /**
     * Sync data to elastic server
     *
     * @return bool
     */
    public function esIndex()
    {
        $params = [
            'index' => $this->getEsIndexName(),
            'type' => $this->getEsTypeName(),
            'id' => $this->getEsId(),
            'body' => $this->getEsData(),
        ];

        return Search::index($params);
    }

    /**
     * Get elastic document by id
     *
     * @return mixed
     */
    public static function esFind()
    {
        $instance = new static;
        $params = [
            'index' => $instance->getEsIndexName(),
            'type' => $instance->getEsTypeName(),
            'id' => $instance->getEsId(),
        ];

        return Search::get($params);
    }

    /**
     * Search a Type.
     *
     * @param array $query
     * @param int $page
     * @param int $limit
     * @param array $sourceFields
     * @param array $sort
     * @param array $minScore
     * @return Collection
     */
    public static function esSearch(array $query = array(), $page = 1, $limit = 15, array $sourceFields = [], array $sort = [], $minScore = null)
    {
        $instance = new static;
        $params = [
            'index' => $instance->getEsIndexName(),
            'type' => $instance->getEsTypeName(),
            'body' => []
        ];

        if ($minScore) {
            $params['body']['min_score'] = $minScore;
        }

        if (!empty($query)) {
            $params['body']['query'] = $query;
        }

        if (!empty($sourceFields)) {
            $params['body']['_source']['include'] = $sourceFields;
        }

        if (is_numeric($limit)) {
            $params['size'] = $limit;
        }

        if (is_numeric($page)) {
            $params['from'] = ($page -1)*$limit;
        }

        if (!empty($sort)) {
            $params['body']['sort'] = $sort;
        }

        $result = Search::search($params);

        $total = $result['hits']['total'];
        $result = $result['hits']['hits'];
        $result = new Collection($result);

        return [
            'data' => $result->pluck('_source'),
            'total' => $total,
        ];
    }

    /**
     * Delete elastic document by id
     *
     * @return mixed
     */
    public function esDelete()
    {
        $params = [
            'index' => $this->getEsIndexName(),
            'type' => $this->getEsTypeName(),
            'id' => $this->getEsId(),
        ];

        return Search::delete($params);
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return EloquentEs\Supports\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
}