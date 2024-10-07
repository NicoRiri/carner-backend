<?php

namespace App\Exception;

class ArticleNotFound extends \Exception
{
    public function __construct()
    {
        parent::__construct("Article not found");
    }

}