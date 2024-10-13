<?php

namespace App\Controller;

use App\Exception\ArticleAlreadyInCart;
use App\Exception\ArticleInCartWasModified;
use App\Exception\ArticleNotAlreadyInCart;
use App\Exception\ArticleNotFound;
use App\Exception\FileNotFound;
use App\Exception\MediaNameBadFormat;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    private ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    #[Route('/api/cart', name: 'api.cart', methods: ['GET'])]
    public function getAllArticleOfUser(): Response
    {
        return $this->json($this->articleService->getArticleOfUser());
    }

    #[Route('/api/cart/{id}', name: 'api.cartadd', methods: ['POST'])]
    public function addArticleToUser(int $id): Response
    {
        try {
            $this->articleService->addArticleToUser($id);
            return $this->json(["message" => "Article added successfully"], Response::HTTP_OK);
        } catch (ArticleNotFound $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ArticleAlreadyInCart $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (ArticleInCartWasModified $e) {
            return $this->json(["message" => $e->getMessage()], Response::HTTP_OK);
        }
    }

    #[Route('/api/cart/{id}/{status}', name: 'api.cart.status', methods: ['PATCH'])]
    public function editStatus(int $id, bool $status): Response
    {
        try {
            $this->articleService->setArticleStatut($id, $status);
            return $this->json(["message" => "Status changed successfully"], Response::HTTP_OK);
        } catch (ArticleNotFound $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ArticleNotAlreadyInCart $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/api/cart/{id}', name: 'api.cartrm', methods: ['DELETE'])]
    public function removeArticleOfUser(int $id): Response
    {
        try {
            $this->articleService->removeArticleToUser($id);
            return $this->json(["message" => "Article removed successfully"], Response::HTTP_OK);
        } catch (ArticleNotFound $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ArticleNotAlreadyInCart $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/api/cart', name: 'api.cart.clean', methods: ['DELETE'])]
    public function cleanCart(): Response
    {
        $this->articleService->forceCleanCart();
        return $this->json(["message" => "Cart has been cleared successfully"], Response::HTTP_OK);
    }

    #[Route('/api/article', name: 'api.article', methods: ['GET'])]
    public function getAllArticle(): Response
    {
        return $this->json($this->articleService->getAllArticles());
    }

    #[Route('/api/article', name: 'api.add.article', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        $uploadedFile = $request->files->get('file');
        $name = $request->request->get('name');
        $uploadDir = $this->getParameter('media_directory');

        try {
            $this->articleService->uploadArticle($uploadedFile, $name, $uploadDir);
        } catch (FileNotFound $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (MediaNameBadFormat $e) {
            return $this->json(["error" => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(["message" => "File uploaded successfully"], Response::HTTP_CREATED);
    }

}