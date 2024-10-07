<?php

namespace App\Service;

interface iArticleService
{
    function getArticleOfUser();
    function uploadArticle($file, string $name, $uploadDir);

}