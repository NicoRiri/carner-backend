<?php

namespace App\Exception;

class ArticleMediaRequired extends \Exception
{
    public function __construct()
    {
        parent::__construct("Article media is required");
    }

}