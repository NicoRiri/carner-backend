<?php

namespace App\Exception;

class MediaNotFound extends \Exception
{
    public function __construct()
    {
        parent::__construct("Media not found");
    }

}