<?php

namespace App\Controller;

use App\Entity\Infraction;
use App\Form\InfractionType;
use App\Form\SearchCinType;
use App\Repository\InfractionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/police')]
#[IsGranted('ROLE_POLICE')]
class PoliceController extends AbstractController
{
    #[Route('/dashboard', name: 'police_dashboard')]
    public function dashboard(InfractionRepository $infractionRepo): Response
    {
        $infractions = $infractionRepo->findByAgent($this->getUser());
        
        return $this->render('police/dashboard.html.twig', [
            'infractions' => $infractions,
        ]);
    }

    #[Route('/infraction/new', name: 'police_new_infraction')]
    public function newInfraction(Request $request, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        
        $searchForm = $this->createForm(SearchCinType::class);
        $searchForm->handleRequest($request);
        
        $citoyen = null;
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $cin = $searchForm->get('cin')->getData();
            $citoyen = $userRepo->findByCin($cin);
            if ($citoyen) {
                $session->set('citoyen_id', $citoyen->getId());
            } else {
                $this->addFlash('error', 'Citoyen introuvable.');
                $session->remove('citoyen_id');
            }
        } elseif ($session->has('citoyen_id')) {
            $citoyen = $userRepo->find($session->get('citoyen_id'));
        }

        $infractionForm = null;
        if ($citoyen) {
            $infraction = new Infraction();
            $infractionForm = $this->createForm(InfractionType::class, $infraction);
            $infractionForm->handleRequest($request);

            if ($infractionForm->isSubmitted() && $infractionForm->isValid()) {
                $infraction->setUser($citoyen);
                $infraction->setAgent($this->getUser());
                $infraction->setDateInfraction(new \DateTime());
                $infraction->setStatut('a_payer');

                $em->persist($infraction);
                $em->flush();

                $this->addFlash('success', 'Infraction enregistrée pour ' . $citoyen->getNom());
                $session->remove('citoyen_id');
                return $this->redirectToRoute('police_new_infraction');
            }
        }

        return $this->render('police/infraction/new.html.twig', [
            'searchForm' => $searchForm->createView(),
            'infractionForm' => $infractionForm ? $infractionForm->createView() : null,
            'citoyen' => $citoyen,
        ]);
    }

    #[Route('/journal', name: 'police_journal')]
    public function journal(InfractionRepository $infractionRepo): Response
    {
        $infractions = $infractionRepo->findByAgent($this->getUser());
        return $this->render('police/journal.html.twig', [
            'infractions' => $infractions,
        ]);
    }
}
