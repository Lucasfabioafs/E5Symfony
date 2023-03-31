<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\FormationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Formation;
use App\Entity\Inscription;
use App\Entity\Employe;
use Symfony\Component\HttpFoundation\Session\Session;


class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(): Response
    {
        echo "coucou";
        return $this->render('formation/index.html.twig', [
            'controller_name' => 'FormationController',
        ]);
    }

    #[Route("/affLesFormations", name: "app_aff")]

    public function afficherLesFilmsAction(ManagerRegistry $doctrine)

    {

        

        $formation = $doctrine->getManager()->getRepository(Formation::class)->findAll();

        


        if (!$formation) {

            $message = "Pas de formation";

        } else {

            $message = null;

        }

        return $this->render("formation/listeFormation.html.twig", array('ensFormation' => $formation, 'message' => $message));

    }

   
    



    #[Route("/suppFilm/{id}", name: "app_film_sup")]

    public function suppFilmAction($id, ManagerRegistry $doctrine)

    {

        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($id);

        if ($formation) {
            
            $inscription = $doctrine->getManager()->getRepository(Inscription::class)->verifInscriExiste($id);
          
            $entityManager = $doctrine->getManager();

            if (count($inscription)==0){
                $entityManager->remove($formation);
                $entityManager->flush();
            }
            else{
                return $this->render("formation/erreursup.html.twig");
            }
            

        }



        return $this->redirectToRoute('app_aff');
    }

    #[Route('/inscriptionFormation/{id}',name: 'app_inscriptionFormation')]
    public function inscireFormation($id,Session $session,ManagerRegistry $doctrine){

        if (!$session->get('employeId')) {

            return $this->redirectToRoute('app_connexion');

        }
        
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($session->get('employeId'));
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($id);
        $verif=$doctrine->getManager()->getRepository(Inscription::class)->doubleInscription($formation,$employe);
        if($verif){
            return $this->render('formation/doubleinscri.html.twig');
        }
        else{
            $inscription=new Inscription();
            $inscription->setLaFormation($formation);
            $inscription->setStatut("en cours");
            $inscription->setLemploye($employe);
        

            $entityManager=$doctrine->getManager();
            $entityManager->persist($inscription);
            $entityManager->flush();
            return $this->redirectToRoute('app_enregistrer');
        }
        }

    

    #[Route('/ajoutFormation',name: 'app_ajout_formation')]

    public function ajoutFormationAction(Request $request,ManagerRegistry $doctrine, $formation=null)
    {
        if($formation==null){
            $formation=new Formation();
        }
        $form=$this->createForm(FormationType::class,$formation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $em=$doctrine->getManager();
            var_dump($formation);
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('app_aff');
        }
        return $this->render('formation/editer.html.twig',array('form'=>$form->createView()));
        
    }

    #[Route("/affDemandeInscription", name: "app_demandInscri")]

    public function afficherDemandeInscription(ManagerRegistry $doctrine)

    {

        $inscription = $doctrine->getManager()->getRepository(Inscription::class)->findAll();

        if (!$inscription) {

            $message = "Pas de formation";

        } else {

            $message = null;

        }

        return $this->render("inscription/listeDemande.html.twig", array('ensInscription' => $inscription, 'message' => $message));

    }




    #[Route('/accepter/{id}', name: 'app_accepter')]

    public function accepter($id,ManagerRegistry $doctrine)

    {

        $inscription = $doctrine->getManager()->getRepository(Inscription::class)->find($id);

        $inscription->setStatut("accepter");

        $entityManager = $doctrine->getManager();

        $entityManager->persist($inscription);

        $entityManager->flush();

        return $this->redirectToRoute('app_demandInscri');

    }

    #[Route('/refuser/{id}', name: 'app_refuser')]

    public function refuser($id,ManagerRegistry $doctrine)

    {

        $inscription = $doctrine->getManager()->getRepository(Inscription::class)->find($id);

        $inscription->setStatut("refuser");

        $entityManager = $doctrine->getManager();

        $entityManager->persist($inscription);

        $entityManager->flush();

        return $this->redirectToRoute('app_demandInscri');

    }


#[Route("/affDepartement", name: "app_departement")]

public function afficherDepartement(ManagerRegistry $doctrine)

{

    $formation = $doctrine->getManager()->getRepository(Formation::class)->findAll();


    return $this->render("formation/listeDepart.html.twig",array('ensFormation' => $formation));

}
#[Route("/affLeDepartement/{departement}", name: "app_voirDepartement")]

public function afficherLeDepartement($departement,ManagerRegistry $doctrine)

{

    $formation = $doctrine->getManager()->getRepository(Formation::class)->findByDepart($departement);


    return $this->render("formation/lesdepartements.html.twig",array('ensFormation' => $formation));

}


#[Route("/chercheFormationEmploye/{id}", name: "app_cherche")]

public function affFormationEmploye($id,ManagerRegistry $doctrine)

{
    $inscription = $doctrine->getManager()->getRepository(Inscription::class)->chercheFormation($id);   
    if (!$inscription) {
        $message = "Pas de formation";
    } else {
        $message = null;
    }
    return $this->render("inscription/listeFormationDeEmploye.html.twig", array('ensInscription' => $inscription, 'message' => $message));

}


}