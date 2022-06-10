<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
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
        ConferenceRepository $conferenceRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        return $this->render('conference/show.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]);
    }
}
