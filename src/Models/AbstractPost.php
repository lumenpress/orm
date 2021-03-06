<?php

namespace LumenPress\Nimble\Models;

use LumenPress\Nimble\Relations\HasMeta;
use LumenPress\Nimble\Concerns\PostAttributes;
use LumenPress\Nimble\Collections\RelatedCollection;

abstract class AbstractPost extends Model
{
    use PostAttributes;

    const CREATED_AT = 'post_date';

    const UPDATED_AT = 'post_modified';

    protected $table = 'posts';

    protected $primaryKey = 'ID';

    protected $dates = [
        'post_date',
        'post_date_gmt',
        'post_modified',
        'post_modified_gmt',
    ];

    public function __construct(array $attributes = [])
    {
        $this->ID = 0;
        // $this->post_author = (int) lumenpress_get_current_user_id();
        $this->post_author = 0;
        $this->post_content = '';
        // $this->post_title = 'Untitle';
        $this->post_excerpt = '';
        $this->post_status = 'publish';
        $this->comment_status = 'open';
        $this->ping_status = 'open';
        $this->to_ping = '';
        $this->pinged = '';
        $this->post_content_filtered = '';
        $this->post_parent = 0;
        $this->menu_order = 0;
        $this->post_type = property_exists($this, 'postType') ? $this->postType : 'post';
        $this->comment_count = 0;

        parent::__construct($attributes);
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        $builder = parent::newQuery();

        if (property_exists($this, 'postType')) {
            $builder->where('post_type', $this->postType);
        }

        return $builder;
    }

    public function meta($key = null)
    {
        $relation = new HasMeta($this);
        if ($key) {
            $relation->key($key);
        }

        return $relation;
    }

    public function save(array $options = [])
    {
        if (! $this->post_name) {
            $this->post_name = $this->post_title;
        }

        if (! parent::save($options)) {
            return false;
        }

        if (! $this->guid) {
            $this->guid = $this->getGuessGuid();
        }

        if (! isset($this->attributes['post_date_gmt'])) {
            $this->post_date_gmt = $this->post_date->timezone('UTC');
        }

        $this->post_modified_gmt = $this->post_modified->timezone('UTC');

        foreach ($this->relations as $relation) {
            if ($relation instanceof RelatedCollection) {
                $relation->save();
            }
        }

        return parent::save();
    }

    public function delete()
    {
        foreach ($this->hasMany(Post::class, 'post_parent')->get() as $model) {
            $model->delete();
        }

        return parent::delete();
    }
}
