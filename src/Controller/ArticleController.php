<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private function generateRandomString(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

    private function generateRandomParagraph(): string
    {
        $sentences = rand(3, 8);
        $paragraph = '';
        for ($i = 0; $i < $sentences; $i++) {
            $wordCount = rand(5, 15);
            $sentence = '';
            for ($j = 0; $j < $wordCount; $j++) {
                $sentence .= $this->generateRandomString(rand(3, 10)) . ' ';
            }
            $paragraph .= ucfirst(trim($sentence)) . '. ';
        }
        return $paragraph;
    }

    #[Route('/articles', name: 'app_article_list')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route('/article/{slug}', name: 'app_article')]
    public function show(EntityManagerInterface $entityManager, string $slug): Response
    {
        $article = $entityManager->getRepository(Article::class)->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article
        ]);
    }

    #[Route('/generate-article', name: 'app_generate_article')]
    public function generateArticle(EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        
        // Random title (5-10 words)
        $title = ucwords(implode(' ', array_map(
            fn() => $this->generateRandomString(rand(3, 8)),
            range(1, rand(5, 10))
        )));
        
        // Generate content with multiple paragraphs and headers
        $content = '';
        for ($i = 0; $i < rand(3, 6); $i++) {
            if ($i > 0) {
                $content .= "<h2>" . ucwords($this->generateRandomString(rand(10, 20))) . "</h2>\n";
            }
            $content .= "<p>" . $this->generateRandomParagraph() . "</p>\n";
            $content .= "<p>" . $this->generateRandomParagraph() . "</p>\n";
        }

        $author = ucwords(implode(' ', array_map(
            fn() => $this->generateRandomString(rand(4, 8)),
            range(1, rand(2, 3))
        )));

        $readingTime = rand(2, 15) . ' minutes';
        $slug = strtolower(str_replace(' ', '-', $this->generateRandomString(10)));
        $excerpt = $this->generateRandomParagraph();

        $article->setTitle($title);
        $article->setContent($content);
        $article->setAuthor($author);
        $article->setCreatedAt(new \DateTimeImmutable());
        $article->setReadingTime($readingTime);
        $article->setSlug($slug);
        $article->setExcerpt($excerpt);

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_article_list');
    }
}