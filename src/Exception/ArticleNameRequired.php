<?php

namespace App\Exception;

class ArticleNameRequired extends \Exception
{
    public function __construct()
    {
        parent::__construct("Article name is required");
    }

}