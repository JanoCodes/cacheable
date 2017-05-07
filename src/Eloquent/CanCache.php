<?php

namespace Jano\Cacheable\Eloquent;

use Watson\Rememberable\Query\Builder;

trait CanCache
{
    /**
     * The type of cache handler.
     *
     * @var string
     */
    protected $cache = 'secure_file';

    /**
     * The number of minutes that the cache persists for.
     *
     * @var int
     */
    protected $expire;

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Watson\Rememberable\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        $builder = new Builder($conn, $grammar, $conn->getPostProcessor());
        $builder->cacheDriver($this->cache);

        if ($this->expire !== null) {
            $builder->remember($this->expire);
        }

        $builder->cacheTags($this->table);

        return $builder;
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $query = $this->newQueryWithoutScopes();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
            if ($this->isDirty()) {
                $saved = $this->performUpdate($query);
                $query->flushCache();
            }
            else {
                $saved = true;
            }
        }
        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert($query);
            $query->flushCache();
        }

        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }
}
