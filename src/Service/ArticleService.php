<?php

namespace App\Service;

use App\DTO\ArticleDTO;
use App\Entity\Article;
use App\Entity\Liste;
use App\Entity\User;
use App\Entity\User2Article;
use App\Exception\ArticleAlreadyInCart;
use App\Exception\ArticleInCartWasModified;
use App\Exception\ArticleNotAlreadyInCart;
use App\Exception\ArticleNotFound;
use App\Exception\FileNotFound;
use App\Exception\MediaNameBadFormat;
use App\Repository\ListeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;

class ArticleService implements iArticleService
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private ListeRepository $listeRepository;

    public function __construct(EntityManagerInterface $entityManager, Security $security, ListeRepository $listeRepository)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->listeRepository = $listeRepository;
    }

    function getArticleOfUser(): array
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($this->security->getUser());
        $articles = $user->getListes();
        $response = [];
        foreach ($articles as $article) {
            $a = $article->getArticle();
            $response[] = new ArticleDTO($a->getId(), $a->getNom(), $a->getImage(), $article->isOkay());
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
            $response[] = new ArticleDTO($article->getId(), $article->getNom(), $article->getImage(), false);
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