<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Comments;
use App\Form\AddArticleFormType;
use App\Form\CommentFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ArticlesController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $articles = $this->getDoctrine()->getRepository(Articles::class)->findAll();

        return $this->render('articles/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/article/new", name="add_article")
     */
    public function addArticle(Request $request): Response
    {
        $article = new Articles();

        $form = $this->createForm(AddArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUsers($this->getUser());

            $this->getDoctrine()->getManager()->persist($article);
            $this->getDoctrine()->getManager()->flush();;
            
            $this->addFlash('message', 'Votre article a bien été publié');
            return $this->redirectToRoute('home');
        }

        return $this->render('articles/add.html.twig', [
            'articleForm' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/article/{slug}", name="article")
     */
    public function article($slug, Request $request): Response
    {
        $article = $this->getDoctrine()->getRepository(Articles::class)->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException("L'article recherché n'existe pas");
        }

        $comment = new Comments();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                ->setArticles($article);

            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('articles/article.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);
    }
}
