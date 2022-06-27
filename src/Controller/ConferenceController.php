<?php

namespace App\Controller;

//use App\SpamChecker;
use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\ConferenceRepository;
use App\Message\CommentMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class ConferenceController extends AbstractController
{
    private $entityManager;
    private $bus;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }
    /**
     * @Route("/old", name="homepage_old")
     */
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        
        return $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),            
        ]);
        /*
        // MAINTENANCE
        return new Response(<<<EOF
<html>
        <body>
                <img src="/images/under-construction.gif" />
        </body>
</html>
EOF
        );
        */
    }


    /**
     * @Route("/conference/{slug}", name="conference")
     */
    public function show(Request $request, 
        Conference $conference, 
        CommentRepository $commentRepository, 
        ConferenceRepository $conferenceRepository,        
        string $photoDir): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);

            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                    //dump($e);
                }
                $comment->setPhotoFilename($filename);
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            // SpamChecker_init via Messenger
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];
            //if (2 === $spamChecker->getSpamScore($comment, $context)) {
            //    throw new \RuntimeException('Blatant spam, go away!');
            //}
            $this->bus->dispatch(new CommentMessage($comment->getId(),$context));
            // SpamChecker_end
            // $this->entityManager->flush();
            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        
        return $this->render('conference/show.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form->createView(),
        ]);
    }
}
