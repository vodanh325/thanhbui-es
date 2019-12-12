<?php namespace EloquentEs\Supports;

use Illuminate\Database\Eloquent\Collection as BaseCollection;

/**
 * Class Collection
 * @package EloquentEs\Supports
 */
class Collection extends BaseCollection
{
    use ElasticCollectionTrait;
}