<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/conference/{id}", name="conference")
     */
    public function show(Conference $conference, CommentRepository $commentRepository): Response
    {
        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $commentRepository->findBy(['conference' => $conference], ['createdAt' => 'DESC']),
        ]);
    }
}
