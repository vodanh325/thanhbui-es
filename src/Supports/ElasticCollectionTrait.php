<?php namespace EloquentEs\Supports;

use EloquentEs\EloquentEsFacade as Search;

/**
 * Class ElasticCollectionTrait
 * @package EloquentEs\Supports
 */
trait ElasticCollectionTrait
{
    /**
     * Add To Index
     *
     * Add all documents in this collection to to the Elasticsearch document index.
     *
     * @return null|array
     */
    public function esIndex()
    {
        if ($this->isEmpty()) {
            return null;
        }

        $all = $this->all();
        $params = [];

        foreach ($all as $item) {
            $params['body'][] = [
                'index' => [
                    '_id' => $item->getEsId(),
                    '_type' => $item->getEsTypeName(),
                    '_index' => $item->getEsIndexName(),
                ],
            ];
            $params['body'][] = $item->getEsData();
        }

        return Search::bulk($params);
    }

    /**
     * Delete From Index
     *
     * @return array
     */
    public function esDelete()
    {
        $all = $this->all();
        $params = array();

        foreach ($all as $item) {
            $params['body'][] = [
                'delete' => [
                    '_id' => $item->getEsId(),
                    '_type' => $item->getEsTypeName(),
                    '_index' => $item->getEsIndexName(),
                ],
            ];
        }

        return Search::bulk($params);
    }
    /**
     * Reindex
     *
     * Delete the items and then re-index them.
     *
     * @return array
     */
    public function esReindex()
    {
        $this->esDelete();
        return $this->esIndex();
    }
}