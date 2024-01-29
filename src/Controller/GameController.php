<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameSearchType;
use App\Form\GameType;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/game', 'game_')]
class GameController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        GameRepository $gameRepository,
        CategoryRepository $categoryRepository,
        AuthenticationUtils $authenticationUtils,
        Request $request
    ): Response {
        $lastUsername = $authenticationUtils->getLastUsername();
        $searchForm = $this->createForm(GameSearchType::class);
        $searchForm->handleRequest($request);

        $params = [];
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();

            $params['title'] = $data['title'] ?: '';

            foreach ($data['categories'] as $category) {
                $params['categories'][] = $category->getLabel();
            }

            $params['sort'] = ($data['sort_by'] && $data['sort_order']) ? [
                'by' => $data['sort_by'],
                'order' => $data['sort_order'],
            ] : [];
        }
        
        return $this->render('game/index.html.twig', [
            // 'games' => $gameRepository->findBy(['isVisible' => true]),
            'games' => $gameRepository->search($params),
            'pageTitle' => 'Games',
            'title' => $params['title'] ?? '',
            'categories' => $categoryRepository->findBy([], ['label' => 'ASC']),
            'searchForm' => $searchForm,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        return $this->render('game/show.html.twig', [
            'game' => $game,
            'pageTitle' => 'Game',
        ]);
    }
}
