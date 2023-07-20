<?php

namespace App\Controller;


use App\Entity\Personne;
use App\Form\PersonneType;
use App\Repository\PersonneRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;





#[Route('personne')]
class PersonneController extends AbstractController
{

    #[Route('/', name: 'personne.index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();

        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/all/age/{ageMin}/{ageMax}', name: 'personne.list.age')]
    public function personnesByAge(PersonneRepository $repository, $ageMin, $ageMax): Response
    {
        $personnes = $repository->startsPersonnesByAgeInterval($ageMax,$ageMin);
        
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }
    

    #[Route('/{id<\d+>}', name: 'personne.detail')]
    public function detail(Personne $personne = null): Response
    {
        if (!$personne) {
            $this->addFlash('error', "La personne n'existe pas");
            return $this->redirectToRoute('personne.index');
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
        $form = $this->createForm(PersonneType::class);
        return $this->render('personne/add-personne.html.twig', [
            'form'=>$form->createView()
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
    public function updatePersonne(Personne $personne = null, ManagerRegistry $doctrine, $name, $firstname, $age) {
        //Vérifier que la personne à mettre à jour existe
        if ($personne) {
            // Si la personne existe => mettre a jour notre personne + message de succes
            $personne->setName($name);
            $personne->setFirstname($firstname);
            $personne->setAge($age);
            $manager = $doctrine->getManager();
            $manager->persist($personne);

            $manager->flush();
            $this->addFlash('success', "La personne a été mis à jour avec succès");
        }  else {
            //Sinon  retourner un flashMessage d'erreur
            $this->addFlash('error', "Personne innexistante");
        }
        return $this->redirectToRoute('personne.list.alls');
    }
}
