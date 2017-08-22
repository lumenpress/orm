<?php 

namespace Lumenpress\ORM\Builders;

use Illuminate\Support\Facades\Schema;

class TaxonomyBuilder extends Builder
{
    protected $aliases = [
        'tag' => 'post_tag',
        'order' => 'term_order',
        'group' => 'term_group',
    ];

    public function is($taxonomy)
    {
        return $this->where('taxonomy', $taxonomy);
    }

    public function type($taxonomy)
    {
        return $this->where('taxonomy', $taxonomy);
    }

    public function taxonomy($taxonomy)
    {
        return $this->where('taxonomy', $taxonomy);
    }

    public function exists($taxonomy, $name, $parent = 0)
    {
        $builder = $this->where('taxonomy', $taxonomy);
        if (is_numeric($name)) {
            $builder->where('term_id', $name);
        } else {
            $builder->where('slug', str_slug($name));
        }
        return $builder->where('parent', $parent)->first();
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (isset($this->aliases[$column])) {
            $column = $this->aliases[$column];
        }
        if (in_array($column, ['name', 'slug', 'term_group', 'term_order'])) {
            return $this->whereHas('term', function($query) use ($column, $operator, $value, $boolean)
            {
                $query->where($column, $operator, $value, $boolean);
            });
        }
        return parent::where($column, $operator, $value, $boolean);
    }

    public function orderBy($column, $direction = 'asc')
    {
        if (isset($this->aliases[$column])) {
            $column = $this->aliases[$column];
        }
        if ($column == 'term_order' && !Schema::hasColumn('terms', $column)) {
            return $this;
        }
        if (in_array($column, ['name', 'slug', 'term_order', 'term_group'])) {
            $this->join('terms', function($join) use ($column)
            {
                $join->on('terms.term_id', '=', 'term_taxonomy.term_id');
            })->orderBy('terms.'.$column);
        } else {
            return parent::orderBy($column, $direction);
        }
        return $this;
    }
}