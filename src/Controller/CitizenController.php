<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\InfractionRepository;
use App\Repository\PaiementRepository;
use App\Repository\ReclamationRepository;
use App\Repository\TaxeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/citizen')]
#[IsGranted('ROLE_CITOYEN')]
class CitizenController extends AbstractController
{
    #[Route('/dashboard', name: 'citizen_dashboard')]
    public function dashboard(
        TaxeRepository $taxeRepo,
        InfractionRepository $infractionRepo,
        PaiementRepository $paiementRepo,
        ReclamationRepository $reclamationRepo
    ): Response {
        $user = $this->getUser();
        $taxesActives = $taxeRepo->findBy(['actif' => true]);
        
        $mesPaiements = $paiementRepo->findByUser($user);
        $totalPaye = 0;
        foreach ($mesPaiements as $p) {
            if ($p->getStatut() === 'paye') {
                $totalPaye += $p->getMontant();
            }
        }
        
        $infractions = $infractionRepo->findByUser($user);
        $nbInfractionsAPayer = count($infractionRepo->findByUserAndStatut($user, 'a_payer'));
        
        // NB taxes en attente ? On simplifie : une taxe est en attente s'il n'y a pas de paiement 'paye' récent.
        // Ici on compte arbitrairement les paiements en attente et en retard
        $paiementsEnAttente = array_filter($mesPaiements, fn($p) => in_array($p->getStatut(), ['en_attente', 'en_retard']));
        
        return $this->render('citizen/dashboard.html.twig', [
            'nb_taxes_en_attente' => count($paiementsEnAttente),
            'nb_infractions_a_payer' => $nbInfractionsAPayer,
            'total_paye' => $totalPaye,
            'nb_reclamations' => count($reclamationRepo->findByUser($user)),
            'infractions_recentes' => array_slice($infractions, 0, 5),
            'paiements_recents' => array_slice($mesPaiements, 0, 5),
            'taxes_disponibles' => $taxesActives,
        ]);
    }

    #[Route('/taxes', name: 'citizen_taxes')]
    public function taxes(TaxeRepository $taxeRepo): Response
    {
        return $this->render('citizen/taxes.html.twig', [
            'taxes' => $taxeRepo->findBy(['actif' => true]),
        ]);
    }

    #[Route('/paiements', name: 'citizen_paiements')]
    public function paiements(PaiementRepository $paiementRepo): Response
    {
        return $this->render('citizen/paiements.html.twig', [
            'paiements' => $paiementRepo->findByUser($this->getUser()),
        ]);
    }

    #[Route('/infractions', name: 'citizen_infractions')]
    public function infractions(InfractionRepository $infractionRepo): Response
    {
        return $this->render('citizen/infractions.html.twig', [
            'infractions' => $infractionRepo->findByUser($this->getUser()),
        ]);
    }

    #[Route('/infraction/{id}/payer', name: 'citizen_payer_infraction', methods: ['GET', 'POST'])]
    public function payerInfraction(int $id, InfractionRepository $infractionRepo, EntityManagerInterface $em): Response
    {
        $infraction = $infractionRepo->find($id);
        if (!$infraction || $infraction->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        $paiement = new Paiement();
        $paiement->setReference('PAY-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT));
        $paiement->setDatePaiement(new \DateTime());
        $paiement->setMontant($infraction->getMontantAmende());
        $paiement->setStatut('paye');
        $paiement->setUser($this->getUser());
        $paiement->setInfraction($infraction);

        $infraction->setStatut('paye');

        $em->persist($paiement);
        $em->flush();

        $this->addFlash('success', 'Paiement effectué avec succès.');
        return $this->redirectToRoute('citizen_infractions');
    }

    #[Route('/infraction/{id}/contester', name: 'citizen_contester', methods: ['GET', 'POST'])]
    public function contester(int $id, InfractionRepository $infractionRepo, EntityManagerInterface $em): Response
    {
        $infraction = $infractionRepo->find($id);
        if (!$infraction || $infraction->getUser() !== $this->getUser() || $infraction->getStatut() === 'paye') {
            throw $this->createNotFoundException();
        }

        $reclamation = new Reclamation();
        $reclamation->setSujet('Contestation Infraction #' . $infraction->getId());
        $reclamation->setDescription('Je conteste l\'infraction du ' . $infraction->getDateInfraction()->format('d/m/Y'));
        $reclamation->setDateSoumission(new \DateTime());
        $reclamation->setStatut('en_cours');
        $reclamation->setUser($this->getUser());

        $infraction->setStatut('conteste');

        $em->persist($reclamation);
        $em->flush();

        $this->addFlash('success', 'Contestation enregistrée (réclamation créée).');
        return $this->redirectToRoute('citizen_reclamations');
    }

    #[Route('/reclamations', name: 'citizen_reclamations')]
    public function reclamations(ReclamationRepository $reclamationRepo): Response
    {
        return $this->render('citizen/reclamations.html.twig', [
            'reclamations' => $reclamationRepo->findByUser($this->getUser()),
        ]);
    }

    #[Route('/reclamation/new', name: 'citizen_new_reclamation')]
    public function newReclamation(Request $request, EntityManagerInterface $em): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setUser($this->getUser());
            $reclamation->setDateSoumission(new \DateTime());
            $reclamation->setStatut('en_cours');

            $em->persist($reclamation);
            $em->flush();

            $this->addFlash('success', 'Réclamation envoyée avec succès.');
            return $this->redirectToRoute('citizen_reclamations');
        }

        return $this->render('citizen/new_reclamation.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
