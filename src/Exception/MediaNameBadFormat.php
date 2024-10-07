<?php

namespace App\Exception;

class MediaNameBadFormat extends \Exception
{
    public function __construct()
    {
        parent::__construct("Media name bad format");
    }

}