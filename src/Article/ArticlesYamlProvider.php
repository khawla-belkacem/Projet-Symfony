<?php
namespace App\Article;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Retour de fificher article.yaml
 */
class ArticlesYamlProvider
{
    public function getArticles(): iterable{
        try {
            return Yaml::parseFile(__DIR__ . '/articles.yaml');
        } catch (ParseException $exception) {
            printf('unable to parse the yaml string: %s', $exception->getMessage());
        }
    }

}