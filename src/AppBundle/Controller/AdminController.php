<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("", name="admin_dash")
     */
    public function dashboardAction()
    {
        return $this->redirectToRoute('admin_posts');
    }

    /**
     * @Route("/posts", name="admin_posts")
     */
    public function postsAction()
    {
        $client = new Client();
        $client->getTransport()->setAuth('neo4j', 'password');
        $query = new Query($client, "MATCH (p:Post)<-[r:AUTHORED]-(a:Author) RETURN p,a");
        $results = [];

        $resultSet = $query->getResultSet();

        foreach($resultSet as $result) {
            $results[] = array_merge(
                ['id'=>$result['p']->getId()],
                $result['p']->getProperties(),
                ['author'=>array_merge(
                    ['id'=>$result['a']->getId()],
                    $result['a']->getProperties()
                )]
            );
        }

        dump($results);

        $data['posts'] = $results;

        return $this->render('@AppBundle/Views/Admin/Posts/index.html.twig', $data);
    }

    /**
     * @Route("/posts/edit/{id}", name="admin_posts_edit")
     * @Route("/posts/new", name="admin_posts_new")
     */
    public function editPostAction(Request $request, $id = null)
    {
        /*
        $em = $this->container->get('neo4j.manager');
        $postRepo = $em->getRepository('AppBundle\\Entity\\Post');
        $authorRepo = $em->getRepository('AppBundle\\Entity\\Author');

        if($id)
            $post = $postRepo->findOneById($id);
        else
            $post = new Post();

        $data['post'] = $post;
        $data['authors'] = $authorRepo->findAll();
        */
        $client = new Client();
        $client->getTransport()->setAuth('neo4j', 'password');

        if($id) {
            $query = new Query($client, "MATCH (n:Post) WHERE n.id = {id} RETURN n", ["id"=>$id]);
            $post = $query->getResultSet()->current()['n']->getProperties();
        }
        if(!$id || !$post) {
            $post = (object)['id'=>null,'title'=>null,'content'=>null,'author'=>null];
        }

        $query = new Query($client, "MATCH (n:Author) RETURN n");
        $authors = [];
        foreach($query->getResultSet() as $author) $authors[] = $author['n']->getProperties();

        $data['post'] = $post;
        $data['authors'] = $authors;

        return $this->render('@AppBundle/Views/Admin/Posts/edit.html.twig', $data);
    }

    /**
     * @Route("/posts/save/{id}", name="admin_posts_save")
     */
    public function savePostAction(Request $request, $id = null)
    {
        /*
        $em = $this->container->get('neo4j.manager');
        $postRepo = $em->getRepository('AppBundle\\Entity\\Post');
        $authorRepo = $em->getRepository('AppBundle\\Entity\\Author');

        $form = $request->request->all();

        if($id)
            $post = $postRepo->find($id);
        else
            $post = new Post();


        $author = $authorRepo->find($form['author']);

        $post->setTitle($form['title']);
        $post->setContent($form['content']);
        $post->setAuthor( $author );

        $em->persist($post);
        $em->flush();
        */
        $client = new Client();
        $client->getTransport()->setAuth('neo4j', 'password');

        $form = $request->request->all();

        if($id) {
            $query = new Query($client, "MATCH (n:Post) WHERE n.id = {id} RETURN n", ["id"=>$id]);
            $post = $query->getResultSet()->current()['n'];
        }
        if(!$id || !$post) {
            $post = (object)['id'=>null,'title'=>null,'content'=>null,'author'=>null];
        }

        if(!$post->id) {
            // Create new post
            $query = new Query($client, "CREATE n =(post {title:{title}, content:{content}})" .
                                        "RETURN n", ["title"=>$form['title'], "content"=>$form['content']]);
            $post = $query->getResultSet()->current()['n'];

            $query = new Query($client, "MATCH (a:Author),(p:Post)" .
                                        "WHERE a.name = {authorName} AND id(p) = {postId}" .
                                        "CREATE (a)-[r:AUTHOR {authored_on: {timestamp}}]->(p)" .
                                        "RETURN r", ["authorId"=>$form['author'], "postId"=>$post->getProperty('id'), "timestamp"=>strtotime('now')]);
            $relation = $query->getResultSet()->current()['r'];
        } else {
            // Update post
            $query = new Query($client, "MATCH (n {id: {id}})" .
                                        "SET n.title = {title}" .
                                        "SET n.content = {content}" .
                                        "RETURN n");
            $post = $query->getResultSet()->current()['n'];
        }



        return $this->redirectToRoute('admin_posts');
    }
}