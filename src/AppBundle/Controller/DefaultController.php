<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Post;
use AppBundle\Entity\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        /*
        $em = $this->container->get('neo4j.manager');
        $postRepo = $em->getRepository('AppBundle\\Entity\\Post');
        $post = $postRepo->find(17);
        dump($post->getTags()->first()->getPosts()->first());
        */

        /*


        $author = new Author();
        $author->setDisplayName("kyle");
        $author->setEmail("redactedprofile@gmail.com");
        $author->setPassword("password");
        $em->persist($author);
        $em->flush();

        $tag = new Tag();
        $tag->setTitle('Test Tag');
        $em->persist($tag);
        $em->flush();

        $post = new Post();
        $post->setAuthor($author);
        $post->setTitle('Welcome to mah blog!');
        $post->setContent('This is a test blog post');
        $post->getTags()->add($tag);
        $em->persist($post);
        $em->flush();

        $post2 = new Post();
        $post2->setAuthor($author);
        $post2->setTitle('ANOTHER POST! WTF');
        $post2->setContent('This is a test blog post');
        $post2->getTags()->add($tag);
        $em->persist($post2);
        $em->flush();

        $tag->getPosts()->add($post);
        $tag->getPosts()->add($post2);
        $em->persist($tag);

        $author->getPosts()->add($post);
        $author->getPosts()->add($post2);
        $em->persist($author);

        $em->flush();

        dump($author);
        dump($tag);
        dump($post);
        dump($post2);
        */



        return $this->render('default/index.html.twig');
    }
}
