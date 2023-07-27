<?php

namespace App\Controller;


use App\Entity\Personne;
use App\Event\AddPersonneEvent;
use App\Event\ListAllPersonnesEvent;
use App\Form\PersonneType;
use App\Repository\PersonneRepository;
use App\Service\Helpers;
use App\Service\PdfService;
use App\Service\UploaderService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;




#[Route('personne'), IsGranted('ROLE_USER')]
class PersonneController extends AbstractController
{
public function __construct(
    private LoggerInterface $logger,
    private Helpers $helper,
    private EventDispatcherInterface $dispatcher ){}
    #[Route('/', name: 'personne.index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        

        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/pdf/{id}', name: 'personne.pdf')]
    public function generatePdfPersonne(Personne $personne = null, PdfService $pdf) {
        $html = $this->render('personne/detail.html.twig', ['personne' => $personne]);
        $pdf->showPdfFile($html);
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
    #[ IsGranted("ROLE_USER")]
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
        $listAllPersonneEvent = new ListAllPersonnesEvent(count($personnes));
        $this->dispatcher->dispatch($listAllPersonneEvent, ListAllPersonnesEvent::LIST_ALL_PERSONNE_EVENT);
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes,
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre
        ]);
    }
    

    #[Route('/edit/{id?0}', name: 'personne.edit')]
    public function addPersonne(Personne $personne = null,
        ManagerRegistry $doctrine,
        Request $request, 
        UploaderService $uploader
        ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $new = false;
        // $entityManger = $doctrine->getManager();
       if(!$personne){
        $new = true;
        $personne = new Personne();
       }
       
        $form = $this->createForm(PersonneType::class, $personne);
        // methode pour supprimer deux champs ou directement du form
        $form->remove('createdAt');
        $form->remove('updatedAt');
        // mon formulaire va aller traiter la requete 
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){
        $photo = $form->get('photo')->getData();
        if ($photo) { 
           
            $directory = $this->getParameter('personne_directory');
            
            $personne->setImage($uploader->uploadFile($photo, $directory));
        }
        $manager = $doctrine->getManager();
        $manager->persist($personne);

        $manager->flush();
        if($new) {
            // creation notre événement
            $addPersonneEvent = new AddPersonneEvent($personne);
            // on va dispatcher cet événement
            $this->dispatcher->dispatch($addPersonneEvent, AddPersonneEvent::ADD_PERSONNE_EVENT);
        } 
        if($new){
            $message = "a été a ajouté avec succés";
            $personne->setCreatedBy($this->getUser());
        
        }else{
            $message = "a été a modifié avec succés";
        }
        $this->addFlash( "succes",$personne->getName(). $message);
        
              return $this->redirectToRoute('personne.index');
        } else{
            return $this->render('personne/add-personne.html.twig', [
                'form'=>$form->createView()
            ]);
       }
        
    }
   
    #[Route('/delete/{id}', name: 'personne.delete'), IsGranted('ROLE_ADMIN')]
    public function deletePersonne(ManagerRegistry $doctrine, Personne $personne = null): RedirectResponse
    {
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
