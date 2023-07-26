<?php 
namespace App\EventListener ;

use App\Event\AddPersonneEvent;
use App\Event\ListAllPersonnesEvent;
use Psr\Log\LoggerInterface;

 class PersonneListener 
 {
    public function __construct(private LoggerInterface $logger){}
    public function onPersonneAdd(AddPersonneEvent $event){
        $this->logger->debug("cc je suis entrain d ecouter l evenement personne.add et une personne vient d'etre ajoutéeet c'est ". $event->getPersonne()->getName());
    }

    public function onListAllPersonnes(ListAllPersonnesEvent $event){
        $this->logger->debug("Le nombre de personne dans la base est ". $event->getNbPersonne());
    }
 }