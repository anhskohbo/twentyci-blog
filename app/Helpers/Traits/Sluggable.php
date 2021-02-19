<?php

namespace App\Helpers\Traits;

use Cviebrock\EloquentSluggable\Sluggable as EloquentSluggable;

trait Sluggable
{
    use EloquentSluggable;

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        if (isset($this->sluggable) && is_array($this->sluggable)) {
            return array_map(
                static function ($source) {
                    return is_array($source) ? $source : [
                        'source' => $source,
                        'maxLength' => 100,
                        'separator' => '-',
                        'unique' => true,
                    ];
                },
                $this->sluggable
            );
        }

        return [];
    }
}
