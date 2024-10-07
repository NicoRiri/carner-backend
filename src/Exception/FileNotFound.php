<?php

namespace App\Exception;

class FileNotFound extends \Exception
{
    public function __construct()
    {
        parent::__construct("File not found");
    }

}