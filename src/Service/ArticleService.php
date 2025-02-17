<?php

namespace App\Service;

use App\DTO\ArticleDTO;
use App\DTO\ArticleStateDTO;
use App\Entity\Article;
use App\Entity\Liste;
use App\Entity\User;
use App\Exception\ArticleAlreadyInCart;
use App\Exception\ArticleInCartWasModified;
use App\Exception\ArticleMediaRequired;
use App\Exception\ArticleNameRequired;
use App\Exception\ArticleNotAlreadyInCart;
use App\Exception\ArticleNotFound;
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
        $articles = $user->getListes();
        $response = [];
        foreach ($articles as $article) {
            $a = $article->getArticle();
            $response[] = new ArticleStateDTO($a->getId(), $a->getNom(), $a->getImage(), $article->isOkay());
        }
        return $response;
    }

    /**
     * @throws ArticleNotFound
     * @throws ArticleAlreadyInCart
     * @throws ArticleInCartWasModified
     */
    function addArticleToUser(int $id): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $listeRepository = $this->entityManager->getRepository(Liste::class);
        $user = $userRepository->find($this->security->getUser());
        $article = $articleRepository->find($id);
        if ($article === null) throw new ArticleNotFound();
        if ($user->hasArticle($article)) {
            $liste = $listeRepository->findOneBy(['article' => $article, 'owner' => $user]);
            if ($liste->isOkay()) {
                $liste->setOkay(false);
                $this->entityManager->persist($liste);
                $this->entityManager->flush();
                throw new ArticleInCartWasModified();
            } else {
                throw new ArticleAlreadyInCart();
            }
        }

        $liste = new Liste();
        $liste->setArticle($article);
        $liste->setOwner($user);
        $liste->setOkay(false);

        $this->entityManager->persist($liste);
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
        $listeRepository = $this->entityManager->getRepository(Liste::class);
        $user = $userRepository->find($this->security->getUser());
        $article = $articleRepository->find($id);
        if ($article === null) throw new ArticleNotFound();
        if (!$user->hasArticle($article)) throw new ArticleNotAlreadyInCart();

        $liste = $listeRepository->findOneBy(['article' => $article, 'owner' => $user]);

        $this->entityManager->remove($liste);
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
     * @throws ArticleNameRequired
     * @throws MediaNameBadFormat
     * @throws ArticleMediaRequired
     */
    function uploadArticle($file, $name, $uploadDir): void
    {
        if (empty($name)) throw new ArticleNameRequired();
        if (!$file) throw new ArticleMediaRequired();

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

    /**
     * @throws ArticleNotFound
     */
    function deleteArticle($id, $uploadDir): void
    {
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $article = $articleRepository->find($id);
        if ($article === null) throw new ArticleNotFound();

        try {
            unlink($uploadDir . "/" . $article->getImage());
        } catch (\Exception) {
            // L'image n'existe déjà plus
        }
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }

    /**
     * @throws ArticleNotFound
     * @throws ArticleNotAlreadyInCart
     */
    function setArticleStatut(int $id, bool $status): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $listeRepository = $this->entityManager->getRepository(Liste::class);
        $user = $userRepository->find($this->security->getUser());
        $article = $articleRepository->find($id);
        if ($article === null) throw new ArticleNotFound();
        if (!$user->hasArticle($article)) throw new ArticleNotAlreadyInCart();

        $liste = $listeRepository->findOneBy(['article' => $article, 'owner' => $user]);
        $liste->setOkay($status);

        $this->entityManager->persist($liste);
        $this->entityManager->flush();
    }

    function forceCleanCart(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($this->security->getUser());
        $user->cleanCart($this->entityManager);
    }
}