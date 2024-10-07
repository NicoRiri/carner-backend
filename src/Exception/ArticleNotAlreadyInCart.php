<?php

namespace App\Exception;

class ArticleNotAlreadyInCart extends \Exception
{
    public function __construct()
    {
        parent::__construct("Article isn't already in cart");
    }

}