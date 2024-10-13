<?php

namespace App\Exception;

class ArticleInCartWasModified extends \Exception
{
    public function __construct()
    {
        parent::__construct("Article was modified in cart successfully");
    }

}