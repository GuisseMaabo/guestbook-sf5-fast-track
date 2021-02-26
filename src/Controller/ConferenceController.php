<?php

namespace App\Controller;


use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;


class ConferenceController extends AbstractController
{

    /**
     * You might have noticed that both methods in ConferenceController take
     * a Twig environment as an argument. Instead of injecting it into each
     * method, let’s use some constructor injection instead (that makes the list
     * of arguments shorter and less redundant)
     */

    private $twig;
    public function __construct(Environment $twig){
        $this->twig = $twig;
    }
    #[Route('/', name: 'homepage')]
    /*public function index (Environment $twig, ConferenceRepository $conferenceRepository)*/
    public function index(ConferenceRepository $conferenceRepository)
    {
        // return new Response($twig->render('conference/index.html.twig', [
            return new Response($this->twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
             ]));
    }

    #[Route('/conference/{id}',  name:"conference")]
     /*public function show(Environment $twig, Conference $conference,
CommentRepository $commentRepository)*/

//public function show (Request $request, Environment $twig, Conference $conference, CommentRepository $commentRepository)
public function show(Request $request, Conference $conference, CommentRepository $commentRepository){
    
    $offset = max(0, $request->query->getInt('offset',0));
    $paginator =  $commentRepository->getCommentPaginator($conference,$offset);

   // return new Response($twig->render('conference/show.html.twig', [
    return new Response($this->twig->render('conference/show.html.twig', [   
    'conference' => $conference,
    //'comments' => $commentRepository->findBy(['conference'=>$conference], ['createdAt' => 'DESC']),]));
    'comments' => $paginator,
    'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE, 
    'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
 ]));
 }
}
