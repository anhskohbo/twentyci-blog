<?php

namespace App\Helpers\Traits;

use System\Database\Helpers\UrlPresenter;

trait HasUrlPresenter
{
    public function getPublicUrl($key = null): UrlPresenter
    {
        return $this->getUrlPresenter()->link();
    }

    public function getUrlAttribute(): UrlPresenter
    {
        return $this->getUrlPresenter();
    }

    abstract protected function getUrlPresenter(): UrlPresenter;
}
