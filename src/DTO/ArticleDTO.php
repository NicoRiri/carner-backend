<?php

namespace App\DTO;

class ArticleDTO
{
    public $id;
    public $name;
    public $image;

    /**
     * @param $id
     * @param $name
     * @param $image
     */
    public function __construct($id, $name, $image)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
    }

}