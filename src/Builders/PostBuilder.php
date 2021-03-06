<?php

namespace LumenPress\Nimble\Builders;

class PostBuilder extends Builder
{
    protected $aliases = [
        'title'         => 'post_title',
        'slug'          => 'post_name',
        'type'          => 'post_type',
        'date'          => 'post_date',
        'meta.template' => 'meta._wp_page_template',
    ];

    public function slug($slug)
    {
        return $this->where('post_name', $slug);
    }

    public function path($path)
    {
        $paths = explode('/', $path);
        $parentId = 0;
        $post = null;

        foreach ($paths as $slug) {
            if (! $slug) {
                continue;
            }
            $query = clone $this;
            $post = $query->parent($parentId)->slug($slug)->first();
            $parentId = isset($post->id) ? $post->id : 0;
        }

        return $post;
    }

    public function type($type)
    {
        if (is_array($type)) {
            return $this->whereIn('post_type', $type);
        }

        return $this->where('post_type', $type);
    }

    public function status($status)
    {
        if (is_array($status)) {
            return $this->whereIn('post_status', $status);
        }

        return $this->where('post_status', $status);
    }

    public function parent($parentId)
    {
        return $this->where('post_parent', $parentId);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (stripos($column, 'term.') === 0) {
            return $this->whereHas('tax', function ($query) use ($column, $operator, $value, $boolean) {
                if ($column === 'term.taxonomy') {
                    $query->where('taxonomy', $operator, $value, $boolean);
                } else {
                    $query->whereHas('term', function ($query) use ($column, $operator, $value, $boolean) {
                        $query->where($column === 'term.id' ? 'term_id' : substr($column, 5), $operator, $value, $boolean);
                    });
                }
            });
        }

        if (stripos($column, 'meta.') === 0) {
            return $this->whereHas('meta', function ($query) use ($column, $operator, $value, $boolean) {
                $query->where('meta_key', substr($column, 5));
                $query->where('meta_value', $operator, $value, $boolean);
            });
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * [orderBy description].
     *
     * $buidler->orderBy('column', 'asc')
     *
     * $buidler->orderBy('meta.column', 'asc')
     *
     * @param [type] $column [description]
     * @param string $order  [description]
     *
     * @return [type] [description]
     */
    public function orderBy($column, $order = 'asc')
    {
        if (isset($this->aliases[$column])) {
            $column = $this->aliases[$column];
        }

        if (stripos($column, 'meta.') !== 0) {
            return parent::orderBy($column, $order);
        }

        $column = str_replace('meta.', '', $column);

        return $this->join('postmeta', function ($join) use ($column) {
            $join->on('posts.ID', '=', 'postmeta.post_id');
            $join->where('meta_key', $column);
        })
            ->groupBy('posts.ID')
            ->orderBy('postmeta.meta_value', $order);
    }
}
