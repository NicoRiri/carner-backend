<?php

namespace App\Service;

interface iArticleService
{
    function getArticleOfUser();
    function uploadArticle($file, string $name, $uploadDir);
    function setArticleStatut(int $id, bool $status);

}