<?php

namespace App\Exception;

class ArticleAlreadyInCart extends \Exception
{
    public function __construct()
    {
        parent::__construct("Article is already in cart");
    }

}