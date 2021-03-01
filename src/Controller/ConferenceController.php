<?php

namespace App\Controller;


use App\Entity\Conference;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
    private $entityManager;
    ##public function __construct(Environment $twig){
        public function __construct(Environment $twig, EntityManagerInterface$entityManager) {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
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

    ##[Route('/conference/{id}',  name:"conference")]
    #[Route('/conference/{slug}',  name:"conference")]

     /*public function show(Environment $twig, Conference $conference,
CommentRepository $commentRepository)*/

//public function show (Request $request, Environment $twig, Conference $conference, CommentRepository $commentRepository)
//public function show(Request $request, Conference $conference, CommentRepository $commentRepository)
//public function show(Request $request,Conference $conference, CommentRepository $commentRepository, ConferenceRepository $conferenceRepository){
   
    public function show(Request $request, Conference $conference,ConferenceRepository $conferenceRepository,  CommentRepository $commentRepository, string $photoDir)
  {  
      $comment = new Comment ();
      $form =  $this->createForm(CommentFormType::class, $comment);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
    $comment->setConference($conference);

    if ($photo = $form['photo']->getData()) {
         $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
         try {
         $photo->move($photoDir, $filename);
         } catch (FileException $e) {
         // unable to upload the photo, give up
         }
         $comment->setPhotoFilename($filename);
         }
        
    $this->entityManager->persist($comment);
    $this->entityManager->flush();

    return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
    }



    $offset = max(0,$request->query->getInt('offset',0));
    $paginator =  $commentRepository->getCommentPaginator($conference,$offset);

   // return new Response($twig->render('conference/show.html.twig', [
    return new Response($this->twig->render('conference/show.html.twig', [   
    'conferences' => $conferenceRepository->findAll(),
    'conference' => $conference,
    //'comments' => $commentRepository->findBy(['conference'=>$conference], ['createdAt' => 'DESC']),]));
    'comments' => $paginator,
    'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE, 
    'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
    'comment_form' => $form->createView(),
 ]));
 }
}
