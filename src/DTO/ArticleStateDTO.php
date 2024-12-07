<?php

namespace App\DTO;

class ArticleStateDTO
{
    public int $id;
    public string $name;
    public string $image;
    public bool $okay;

    /**
     * @param $id
     * @param $name
     * @param $image
     */
    public function __construct($id, $name, $image, $okay)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->okay = $okay;
    }

}