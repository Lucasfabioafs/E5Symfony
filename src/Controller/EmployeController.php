<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\FormationType;
use App\Form\EmployeType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Formation;
use App\Entity\Employe;
use App\Form\ConnexionType;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Inscription;

class EmployeController extends AbstractController
{
    #[Route('/employe', name: 'app_employe')]
    public function index(): Response
    {
        return $this->render('employe/index.html.twig', [
            'controller_name' => 'EmployeController',
        ]);
    }

    #[Route('/ajoutEmploye',name: 'app_ajout_employe')]

    public function ajoutEmployeAction(Request $request,ManagerRegistry $doctrine, $employe=null)
    {
        if($employe==null){
            $employe=new Employe();
        }
        $form=$this->createForm(EmployeType::class,$employe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $em=$doctrine->getManager();
            $em->persist($employe);
            $em->flush();
            return $this->redirectToRoute('app_connexion');
        }
        return $this->render('employe/editer.html.twig',array('form'=>$form->createView()));
        
    }

    #[Route('/Connexion',name: 'app_connexion')]

    public function connexion(Request $request,ManagerRegistry $doctrine, $employe=null)
    {
        if($employe==null){
            $employe=new Employe();
        }
        $form=$this->createForm(ConnexionType::class,$employe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            var_dump($employe);
            $login=$form['login']->getData();
            $mdp=$form['mdp']->getData();
            $employe=$doctrine->getManager()->getRepository(Employe::class)->verifConnexion($login,$mdp);
            var_dump($employe);
            if($employe==null){
                
                return $this->redirectToRoute('app_connexion');
            }
            else{
                $session = new Session();
                $session->set('employeId', $employe->getId());
                if($employe->getStatut()==1){
                    return $this->redirectToRoute('app_affEmploye');
                }
                else{
                    
                    return $this->redirectToRoute('app_aff');

                }
                
            }
            
        }
        return $this->render('employe/connexion.html.twig',array('form'=>$form->createView()));
        
    }


    #[Route("/affLesFormationsEmploye", name: "app_affEmploye")]

    public function afficherFormationEmploye(Session $session,ManagerRegistry $doctrine)

    
    {
        if (!$session->get('employeId')) {

            return $this->redirectToRoute('app_connexion');

        }

        $formation = $doctrine->getManager()->getRepository(Formation::class)->findAll();
       
   
        if (!$formation) {

            $message = "Pas de formation";

        } else {

            $message = null;

        }

        return $this->render("formation/afficheFormationInscription.html.twig", array('ensFormation' => $formation, 'message' => $message));

    }
    

    #[Route('/enregistrer',name: 'app_enregistrer')]

    public function enregister(Request $request,ManagerRegistry $doctrine)
    {
        
        return $this->render('formation/enregistrer.html.twig');
        
    }
    ///INSCRIPTION=DOCTRINE GET REPOSITORY (INSCRIPTION::CLASS)->findby( 'laformation'=$laformation 'lemploye'=$employe   if (count($inscription==0) ->linscrire else deja inscrit 
   
    #[Route("/affLesFormationsListeEmploye", name: "app_affFormationEmploye")]

    public function affLesFormationsListeEmploye(Session $session, ManagerRegistry $doctrine)

    {



        if (!$session->get('employeId')) {

            return $this->redirectToRoute('app_connexion');

        }

        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($session->get('employeId'));

        $inscription = $doctrine->getManager()->getRepository(Inscription::class)->findBy(['lemploye' => $employe]);



        if (!$inscription) {

            $message = "Pas de formation";

        } else {

            $message = null;

        }

        return $this->render("employe/afficheFormationInscriptionEmploye.html.twig", array('ensFormation' => $inscription, 'message' => $message));

    }

    #[Route("/ListeEmploye", name: "app_ListeEmploye")]

    public function affLesEmploye(ManagerRegistry $doctrine)

    {
        $employe = $doctrine->getManager()->getRepository(Employe::class)->verifSatut();   
        if (!$employe) {
            $message = "Pas d'Employe";
        } else {
            $message = null;
        }
        return $this->render("employe/listeEmploye.html.twig", array('ensEmploye' => $employe, 'message' => $message));

    }

   
    
}
