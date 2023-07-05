<?php

namespace App\Controller;

use App\Entity\Personne;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;



#[Route('personne')]
class PersonneController extends AbstractController
{

    #[Route('/', name: 'personne.list')]
    public function index(ManagerRegistry $doctrine): Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/{id<\d+>}', name: 'personne.detail')]
    public function detail(Personne $personne = null): Response {
        if(!$personne) {
            $this->addFlash('error', "La personne n'existe pas ");
            return $this->redirectToRoute('personne.list');
        }

        return $this->render('personne/detail.html.twig', ['personne' => $personne]);
    }

    #[Route('/all/{page?1}/{nbre?12}', name: 'personne.list.alls')]
    public function indexAll(ManagerRegistry $doctrine, $page, $nbre): Response {
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(Personne::class);
    
        // Count entities using QueryBuilder
        $queryBuilder = $repository->createQueryBuilder('p');
        $queryBuilder->select('COUNT(p.id)');
        $nbPersonne = $queryBuilder->getQuery()->getSingleScalarResult();
    
        $nbrePage = ceil($nbPersonne / $nbre);
        $personnes = $repository->findBy([], [], $nbre, ($page - 1) * $nbre);
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes,
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre
        ]);
    }
    

    #[Route('/add', name: 'app_personne.add')]
    public function addPersonne(ManagerRegistry $doctrine): Response
    {
        $entityManger = $doctrine->getManager();
        $personne = new Personne();
        $personne->setFirstname('ghassen');
        $personne->setName('benhammadi');
        $personne->setAge('32');
        $entityManger->persist($personne);
        return $this->render('detail.html.twig', [
            'personne' => $personne,
        ]);
    }
   
    #[Route('/delete/{id}', name: 'personne.delete')]
public function deletePersonne(ManagerRegistry $doctrine, Personne $personne = null): RedirectResponse {
    if ($personne) {
        $manager = $doctrine->getManager();
        $manager->remove($personne);
        $manager->flush();
        $this->addFlash('success', "La personne a été supprimée avec succès");
    } else {
        $this->addFlash('error', "La personne n'existe pas");
    }

    return $this->redirectToRoute('personne.list.alls');
}

    #[Route('/update/{id}/{name}/{firstname}/{age}', name: 'personne.update')]
    public function updatePersonne(Personne $personne = null, ManagerRegistry $doctrine, $name, $firstname, $age){
        if ($personne) {
           $personne->setName($name);
           $personne->setFirstname($firstname);
           $personne->setAge($age);
           $manager = $doctrine->getManager();
           $manager->persist($personne);
           $manager->flush();
           $this->addFlash('success', "la personne a été modifier avec succés");
        } else {
            $this->addFlash('error', "la personne n'exite pas ");
        }
        return $this->redirectToRoute('personne.list.alls');
    }
}
