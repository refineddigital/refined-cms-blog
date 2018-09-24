<?php

namespace RefinedDigital\Blog\Module\Http\Repositories;

use RefinedDigital\CMS\Modules\Core\Http\Repositories\CoreRepository;
use RefinedDigital\CMS\Modules\Tags\Models\Tag;

class BlogRepository extends CoreRepository
{

    public function __construct()
    {
        $this->setModel('RefinedDigital\Blog\Module\Models\Blog');
    }

    public function getForFront($perPage = 5)
    {
        $data = $this->model::with(['meta', 'meta.template'])
            ->whereActive(1)
            ->published()
            ->search(['name','content'])
            ->orderBy('published_at', 'desc')
            ->paging($perPage);

        return $data;
    }

    public function getForFrontWithTags($tag, $type, $perPage = 5)
    {
        return $this->model::allWithTags([$tag], $type)
            ->whereActive(1)
            ->published()
            ->search(['name','content'])
            ->orderBy('published_at', 'desc')
            ->paging($perPage);

    }

    public function getForHomePage($limit = 6)
    {
        return $this->model::with(['meta', 'meta.template'])
            ->whereActive(1)
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRandom($limit = 6, $avoid = false)
    {
        $data = $this->model::with(['meta', 'meta.template'])
            ->whereActive(1)
            ->published()
            ->orderBy('published_at', 'desc')
        ;

        if ($avoid) {
            $data->where('id', '!=', $avoid);
        }

        return $data
            ->limit($limit)
            ->inRandomOrder()
            ->get();
    }

    public function getRandomWithTags($tag, $limit = 6, $avoid = false)
    {
        $data = $this->model::allWithTags([$tag->name], $tag->type)
            ->with(['meta', 'meta.template'])
            ->whereActive(1)
            ->published()
            ->orderBy('published_at', 'desc')
        ;

        if ($avoid) {
            $data->where('id', '!=', $avoid);
        }

        return $data
            ->limit($limit)
            ->inRandomOrder()
            ->get();
    }

    public function getFirstPostByCategory()
    {
        $categories = $this->getCategories();
        $data = collect([]);
        if ($categories->count()) {
            foreach($categories as $category) {
                $d = $this->model::whereActive(1)
                        ->whereHas('taggables', function($q) use ($category) {
                            return $q->where('id', $category->id);
                        })
                        ->published()
                        ->orderBy('published_at', 'desc')
                        ->first();
                if (isset($d->id)) {
                    $d->theCategory = $category;
                    $data->push($d);
                }
            }
        }

        return $data;
    }

    public function getTags()
    {
        return $this->getTagCollection('tags', $this->model);
    }

    public function getCategories()
    {
        return $this->getTagCollection('categories', $this->model);
    }
}
