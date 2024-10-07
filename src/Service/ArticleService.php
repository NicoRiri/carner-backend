<?php

namespace App\Service;

use App\DTO\ArticleDTO;
use App\Entity\Article;
use App\Entity\User;
use App\Exception\ArticleAlreadyInCart;
use App\Exception\ArticleNotAlreadyInCart;
use App\Exception\ArticleNotFound;
use App\Exception\FileNotFound;
use App\Exception\MediaNameBadFormat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;

class ArticleService implements iArticleService
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }
    function getArticleOfUser(): array
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($this->security->getUser());
        $articles = $user->getArticles();
        $response = [];
        foreach ($articles as $article) {
            $response[] = new ArticleDTO($article->getId(), $article->getNom(), $article->getImage());
        }
        return $response;
    }

    /**
     * @throws ArticleNotFound
     * @throws ArticleAlreadyInCart
     */
    function addArticleToUser(int $id): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $user = $userRepository->find($this->security->getUser());
        $article = $articleRepository->find($id);
        if ($article === null) throw new ArticleNotFound();
        if ($user->getArticles()->contains($article)) throw new ArticleAlreadyInCart();
        $article->addUser($user);
        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    /**
     * @throws ArticleNotAlreadyInCart
     * @throws ArticleNotFound
     */
    function removeArticleToUser(int $id): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $user = $userRepository->find($this->security->getUser());
        $article = $articleRepository->find($id);
        if ($article === null) throw new ArticleNotFound();
        if (!$user->getArticles()->contains($article)) throw new ArticleNotAlreadyInCart();
        $user->removeArticle($article);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    function getAllArticles(): array
    {
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $articles = $articleRepository->findAll();
        $response = [];
        foreach ($articles as $article) {
            $response[] = new ArticleDTO($article->getId(), $article->getNom(), $article->getImage());
        }
        return $response;
    }

    /**
     * @throws FileNotFound
     * @throws MediaNameBadFormat
     */
    function uploadArticle($file, $name, $uploadDir): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($this->security->getUser());

        if (!$file) throw new FileNotFound();

        $fileName = $file->getClientOriginalName();

        $split = explode(".", $fileName);

        if (count($split) != 2) throw new MediaNameBadFormat();

        $extension = $split[1];
        $fileName = Uuid::v1();
        $fileName = $fileName . "." . $extension;

        $file->move($uploadDir, $fileName);

        $article = new Article();
        $article->setImage($fileName);
        $article->setNom($name);

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }
}