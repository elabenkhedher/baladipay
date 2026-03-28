<?php

namespace App\Controller;

use App\Entity\Taxe;
use App\Form\TaxeType;
use App\Repository\InfractionRepository;
use App\Repository\PaiementRepository;
use App\Repository\ReclamationRepository;
use App\Repository\TaxeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepo,
        PaiementRepository $paiementRepo,
        InfractionRepository $infractionRepo,
        ReclamationRepository $reclamationRepo
    ): Response {
        $stats = [
            'nb_citoyens' => count($userRepo->findAllCitoyens()),
            'nb_paiements' => count($paiementRepo->findBy(['statut' => 'paye'])),
            'total_collecte' => $paiementRepo->getTotalCollecte(),
            'amendes_impayees' => count($infractionRepo->findBy(['statut' => 'a_payer'])),
            'reclamations_actives' => count($reclamationRepo->findAllEnCours()),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/paiements', name: 'admin_paiements')]
    public function paiements(Request $request, PaiementRepository $paiementRepo): Response
    {
        $date = $request->query->get('date');
        $statut = $request->query->get('statut');
        $taxeId = $request->query->get('taxe_id');

        $paiements = $paiementRepo->findWithFilter($date, $statut, $taxeId);

        return $this->render('admin/paiements.html.twig', [
            'paiements' => $paiements,
        ]);
    }

    #[Route('/citoyens', name: 'admin_citoyens')]
    public function citoyens(UserRepository $userRepo): Response
    {
        return $this->render('admin/citoyens.html.twig', [
            'citoyens' => $userRepo->findAllCitoyens(),
        ]);
    }

    #[Route('/citoyen/{id}', name: 'admin_citoyen_detail')]
    public function citoyenDetail(int $id, UserRepository $userRepo): Response
    {
        $citoyen = $userRepo->find($id);
        if (!$citoyen) throw $this->createNotFoundException();

        return $this->render('admin/citoyen_detail.html.twig', [
            'citoyen' => $citoyen,
        ]);
    }

    #[Route('/taxes', name: 'admin_taxes')]
    public function taxes(TaxeRepository $taxeRepo): Response
    {
        return $this->render('admin/taxes.html.twig', [
            'taxes' => $taxeRepo->findAll(),
        ]);
    }

    #[Route('/taxe/new', name: 'admin_new_taxe')]
    public function newTaxe(Request $request, EntityManagerInterface $em): Response
    {
        $taxe = new Taxe();
        $form = $this->createForm(TaxeType::class, $taxe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($taxe);
            $em->flush();
            $this->addFlash('success', 'Taxe créée avec succès.');
            return $this->redirectToRoute('admin_taxes');
        }

        return $this->render('admin/taxe_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouvelle Taxe'
        ]);
    }

    #[Route('/taxe/{id}/edit', name: 'admin_edit_taxe')]
    public function editTaxe(int $id, Request $request, TaxeRepository $taxeRepo, EntityManagerInterface $em): Response
    {
        $taxe = $taxeRepo->find($id);
        if (!$taxe) throw $this->createNotFoundException();

        $form = $this->createForm(TaxeType::class, $taxe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Taxe mise à jour.');
            return $this->redirectToRoute('admin_taxes');
        }

        return $this->render('admin/taxe_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier la taxe'
        ]);
    }

    #[Route('/taxe/{id}/toggle', name: 'admin_toggle_taxe')]
    public function toggleTaxe(int $id, TaxeRepository $taxeRepo, EntityManagerInterface $em): Response
    {
        $taxe = $taxeRepo->find($id);
        if ($taxe) {
            $taxe->setActif(!$taxe->isActif());
            $em->flush();
        }
        return $this->redirectToRoute('admin_taxes');
    }

    #[Route('/infractions', name: 'admin_infractions')]
    public function infractions(InfractionRepository $infractionRepo): Response
    {
        return $this->render('admin/infractions.html.twig', [
            'infractions' => $infractionRepo->findAll(),
        ]);
    }

    #[Route('/infraction/{id}/statut/{statut}', name: 'admin_change_statut_infraction')]
    public function changeStatutInfraction(int $id, string $statut, InfractionRepository $infractionRepo, EntityManagerInterface $em): Response
    {
        $infraction = $infractionRepo->find($id);
        if ($infraction) {
            $infraction->setStatut($statut);
            $em->flush();
            $this->addFlash('success', 'Statut modifié.');
        }
        return $this->redirectToRoute('admin_infractions');
    }

    #[Route('/reclamations', name: 'admin_reclamations')]
    public function reclamations(ReclamationRepository $reclamationRepo): Response
    {
        return $this->render('admin/reclamations.html.twig', [
            'reclamations' => $reclamationRepo->findAll(),
        ]);
    }

    #[Route('/reclamation/{id}/resoudre', name: 'admin_resoudre_reclamation')]
    public function resoudreReclamation(int $id, ReclamationRepository $reclamationRepo, EntityManagerInterface $em): Response
    {
        $reclamation = $reclamationRepo->find($id);
        if ($reclamation) {
            $reclamation->setStatut('resolue');
            $em->flush();
        }
        return $this->redirectToRoute('admin_reclamations');
    }

    #[Route('/reclamation/{id}/rejeter', name: 'admin_rejeter_reclamation')]
    public function rejeterReclamation(int $id, ReclamationRepository $reclamationRepo, EntityManagerInterface $em): Response
    {
        $reclamation = $reclamationRepo->find($id);
        if ($reclamation) {
            $reclamation->setStatut('rejetee');
            $em->flush();
        }
        return $this->redirectToRoute('admin_reclamations');
    }
}
