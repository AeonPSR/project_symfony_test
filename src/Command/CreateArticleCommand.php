<?php

namespace App\Command;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-article',
    description: 'Creates a new article with random content'
)]
class CreateArticleCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

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

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        // Generate random author name (2-3 words)
        $author = ucwords(implode(' ', array_map(
            fn() => $this->generateRandomString(rand(4, 8)),
            range(1, rand(2, 3))
        )));

        // Random reading time between 2-15 minutes
        $readingTime = rand(2, 15) . ' minutes';

        // Create slug from title
        $slug = strtolower(str_replace(' ', '-', $this->generateRandomString(10)));

        // Generate excerpt (shorter random paragraph)
        $excerpt = $this->generateRandomParagraph();

        $article->setTitle($title);
        $article->setContent($content);
        $article->setAuthor($author);
        $article->setCreatedAt(new \DateTimeImmutable());
        $article->setReadingTime($readingTime);
        $article->setSlug($slug);
        $article->setExcerpt($excerpt);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $output->writeln('Article created with:');
        $output->writeln("Title: {$title}");
        $output->writeln("Author: {$author}");
        $output->writeln("Slug: {$slug}");
        $output->writeln("Reading time: {$readingTime}");

        return Command::SUCCESS;
    }
}