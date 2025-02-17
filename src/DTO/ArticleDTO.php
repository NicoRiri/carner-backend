<?php

namespace App\DTO;

class ArticleDTO
{
    public int $id;
    public string $name;
    public string $image;

    /**
     * @param int $id
     * @param string $name
     * @param string $image
     */
    public function __construct(int $id, string $name, string $image)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
    }


}