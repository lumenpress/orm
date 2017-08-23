<?php 

namespace Lumenpress\ORM\Models\Concerns;

trait PostAttributes
{
    /**
     * Mutator for postTitle attribute.
     *
     * @return void
     */
    public function setPostTitleAttribute($value)
    {
        $this->attributes['post_title'] = $value;
        $this->setPostNameAttribute($value);
    }

    /**
     * Mutator for postType attribute.
     *
     * @return void
     */
    public function setPostTypeAttribute($value)
    {
        $this->attributes['post_type'] = $value;
        if ($this->_slug) {
            $this->setPostNameAttribute($this->_slug);
        }
    }

    /**
     * Mutator for post status attribute.
     *
     * @return void
     */
    public function setPostStatusAttribute($value)
    {
        $this->attributes['post_status'] = $value;
        if ($this->_slug) {
            $this->setPostNameAttribute($this->_slug);
        }
    }

    /**
     * Mutator for post parent attribute.
     *
     * @return void
     */
    public function setPostParentAttribute($value)
    {
        $this->attributes['post_parent'] = $value;
        if ($this->_slug) {
            $this->setPostNameAttribute($this->_slug);
        }
    }

    /**
     * Mutator for post name attribute.
     *
     * @return void
     */
    public function setPostNameAttribute($value)
    {
        $this->_slug = $value;
        $this->attributes['post_name'] = $this->getUniquePostName(
            str_slug($value), 
            $this->ID,
            $this->post_status, 
            $this->post_type,
            $this->post_parent
        );
    }

    /**
     * Accessor for post content attribute.
     *
     * @return returnType
     */
    public function getPostContentAttribute($value)
    {
        return luemnpress_get_the_content($value);
    }

    /**
     * Accessor for guid attribute.
     *
     * @return returnType
     */
    public function getGuidAttribute($value)
    {
        return $this->ID !== 0 ? lumenpress_get_permalink($this->ID) 
            : url(($this->post_type === 'page' ? '' : $this->post_type).'/'.$this->post_name);
    }

    public function getUniquePostName($slug, $id = 0, $status = 'publish', $type = 'post', $parent = 0)
    {
        $i = 1;
        $tmp = $slug;
        while (static::where('post_type', $type)
            ->where('ID', '!=', $id)
            ->where('post_parent', $parent)
            ->where('post_status', $status)
            ->where('post_name', $slug)->count() > 0) {
            $slug = $tmp . '-' . (++$i);
        }
        return $slug;
    }
}