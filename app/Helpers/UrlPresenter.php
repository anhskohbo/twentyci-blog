<?php

namespace System\Database\Helpers;

abstract class UrlPresenter
{
    /**
     * The model instance.
     *
     * @var string
     */
    protected $model;

    /**
     * Constructor.
     *
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    abstract public function link();

    /**
     * @param string $key
     * @return string
     */
    public function __get($key)
    {
        if (method_exists($this, $key)) {
            return $this->$key();
        }

        return '';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->link();
    }
}
