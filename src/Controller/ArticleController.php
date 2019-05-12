<?php

namespace App\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Article;

/**
 * Class ArticleController
 * @package App\Controller
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/article", name="article", methods={"GET"})
     */
    public function index()
    {
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findAll();
        return $this->json([
            'article' => $articles,
        ]);
    }

    /**
     * @Route("/article", name="api_create", methods={"POST"})
     * @param ObjectManager $objectMan
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function articleCreate(ObjectManager $objectMan, Request $request)
    {
        try {
            $article = new Article();
            $user_id = $this->getUser()->getId();
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $article->setUserId($user_id);
            $article->setTitle($title);
            $article->setDescription($description);
            $objectMan->persist($article);
            $objectMan->flush();
            return $this->json([
                'article' => $article,
            ], 201);
        } catch (UniqueConstraintViolationException $e) {
            $e->getMessage();
        }
    }

    /**
     * @Route("/article", name="api_update", methods={"PUT", "PATCH"})
     * @param ObjectManager $objectMan
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function articleUpdate(ObjectManager $objectMan, Request $request)
    {
        try {
            $article_id = $request->request->get('article_id');
            $article = $objectMan->find(Article::class, $article_id);
            $current_user_id = $this->getUser()->getId();
            $article_user_id = $article->getUserId();
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $article->isUserCan($current_user_id, $article_user_id);
            $article->setTitle($title);
            $article->setDescription($description);
            $objectMan->persist($article);
            $objectMan->flush();
            return $this->json([
                    'updated_article' => $article,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                 $e->getMessage();
            }
    }

    /**
     * @Route("/article", name="api_delete", methods={"DELETE"})
     * @param ObjectManager $objectMan
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function articleDelete(ObjectManager $objectMan, Request $request)
    {
        try {
            $article_id = $request->request->get('article_id');
            $article = $objectMan->find(Article::class, $article_id);
            $current_user_id = $this->getUser()->getId();
            $article_user_id = $article->getUserId();
            $article->isUserCan($current_user_id, $article_user_id);
            $objectMan->remove($article);
            $objectMan->flush();
            return $this->json([
                'status' => true,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $e->getMessage();
            }
    }
}
