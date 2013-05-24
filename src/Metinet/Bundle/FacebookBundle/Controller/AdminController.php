<?php

namespace Metinet\Bundle\FacebookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;

class AdminController extends Controller
{

    /**
     * @Route("/admin", name="index_admin")
     * @Template()
     */
    public function indexAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $rep = $this->getDoctrine()->getRepository('MetinetFacebookBundle:User'); /* initialsation repository user */
        $repQuizz = $this->getDoctrine()->getRepository('MetinetFacebookBundle:Quizz'); /* initialsation repository quizz */
        $repQuizzResult = $this->getDoctrine()->getRepository('MetinetFacebookBundle:QuizzResult'); /* initialsation repository quizzresult */

         /* Variable Pour le Dashboard à la connexion */
        $nombreUtilisateurs = $this->nombreTotalDeJoueurs($rep); /*Nombre Total de Joueurs*/
        $nombreUtilisateurs7lastDays = $this->nombreTotalDeJoueurs7LastDaysAnd30lastDays($rep , false); /* Nombe Total de Joueurs sur les 7 derniers jours  , false pour recuperer sur les 7 derniers jours*/
        $nombreUtilisateurs30lastDays = $this->nombreTotalDeJoueurs7LastDaysAnd30lastDays($rep , true); /* Nombe Total de Joueurs sur les 30 derniers jours , true pour recuperer sur les 30 derniers jours*/
        $nombreQuizzs = $this->nombreTotalQuizz($repQuizz); /* Nombre de Quizz de disponibles pour les joueurs */
        $scoreMoyen = $this->scoreMoyenDesJoueurs($repQuizzResult); /* Calcul du score moyen des joueurs */
        $nbQuizzLancancement = $this->nombreQuizzsLancesParLesJoueurs($repQuizzResult); /* Calcul avec la classe QuizzResult du nombre de quizzs lancées */
        $arrayTopQuizz = $this->calculeTop3desQuizzLesPlusUtilises($repQuizzResult); /* Retourne un array qui contient les 3 top quizz avec leur id et leur nombre utilisation */
        $arrayBadQuizz = $this->calculeTop3desQuizzLesMoinsUtilises($repQuizzResult); /* Retourne un array qui contient les 3 bad quizz avec leur id et leur nombre utilisation */
        $users = $this->dixDerniersUsers($rep); /* retourne les 10 derniers utilisateurs */
        $user = $this->objectUserEnCours(); /* object user en cour */
        $quizzs = $this->quatreDernierQuizz($repQuizz); /* 4 dernier quizzs */
        $quizzMiEnAvant = $this->calculeQuizzMisEnAvant(); /* fonction charge quizz ispromoted = 1 */
        
        return array("nbUsers" => $nombreUtilisateurs ,
                     "nbUsers7lastDays" => $nombreUtilisateurs7lastDays ,
                     "nbUsers30lastDays" => $nombreUtilisateurs30lastDays,
                     "nbQuizzs" => $nombreQuizzs,
                     "scoreMoyen" => $scoreMoyen,
                     "quizzLances" => $nbQuizzLancancement,
                     "arrayTopQuizz" =>  $arrayTopQuizz ,
                     "arrayBadQuizz" =>  $arrayBadQuizz,
                     "users" => $users,
                     "user" => $user,
                     "quizzs" => $quizzs,
                     "QuizzMiEnAvant" => $quizzMiEnAvant);
    }


    protected function calculeQuizzMisEnAvant(){
       
        /* on charge le quizz is promoted = 1 */
        $quizzRandom = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:Quizz')->findOneByIsPromoted(1);
        return $quizzRandom ;
    }
    
    
    protected function quatreDernierQuizz($repQuizz){ /* fonction permettant de recuperer les 4 derniers quizzs */
        
        $query = $repQuizz->createQueryBuilder('q')
            ->orderBy('q.createdAt', 'DESC') 
            ->getQuery()
            ->setMaxResults(4); /* limit 4 */

        $quizzs = $query->getResult(); /* on recupere les resultats */
        
        return $quizzs ;
        
    }

        protected function objectUserEnCours(){ /* retourne id sql de user en cour */
       $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');
       $user = $this->getDoctrine()->getManager()->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($fbUid);
       return $user ;
    }

    protected function dixDerniersUsers($rep){ /* fonction permettant de recuperer les 10 derniers utilisateurs */
       
        $query = $rep->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC') /* par rapport à la date on recupere les 10 derniers utilisateurs */
            ->getQuery()
            ->setMaxResults(10); /* limit 10 */

        $users = $query->getResult(); /* on recupere les resultats */
        
        return $users ;
    }


    /**
     * @Route("/admin/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render(
            'MetinetFacebookBundle:Admin:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            )
        );
    }

	/**
	* Action de check login
	*@Route("/admin/login_check", name="login_check")
	*/
	public function loginCheckAction()
	{
	}

	/**
	* Action de logout
	* @Route("/admin/logout", name="logout")
	*/
	public function logoutAction()
	{
	}


    private function nombreTotalDeJoueurs($rep){
        return count($rep->findAll()) ; /* nombre user present en bdd */
    }
    
    private function nombreTotalDeJoueurs7LastDaysAnd30lastDays($rep,$boolean){
        
        $nbUtilisateurs = 0 ;
        $nbUtilisateursSecond = 0 ;
        
        foreach($rep->findAll() as  $value){
             $s = strtotime(date("Y-m-d H:i:s")) - strtotime($value->getCreatedAt()->format('Y-m-d H:i:s')) ; /* on formate pour recuperer correctement les donnees */
             $nombreJours = intval( $s / 86400 ) ;  /* on obtient le nombre de jours */
             
            if($nombreJours >= 0 && $nombreJours <= 7){
                $nbUtilisateurs += 1 ; /* si nombre de jour entre 0 et 7 on incremente */
            }
            
            if($nombreJours >=0 && $nombreJours <= 30){
                $nbUtilisateursSecond += 1 ; /* si nombre de jour entre 7 et 30 on incremente */
            }
            
        }
        
        if($boolean){
            return $nbUtilisateursSecond ;
        }
        else{
            return $nbUtilisateurs ;
        }
    }
    
    private function nombreTotalQuizz($repQuizz){
        return count($repQuizz->findAll()) ; /* on retourne le nombre user présent en bdd */
    }
    
   private function scoreMoyenDesJoueurs($repQuizzResult){
        $quizzEffectuees = $this->nombreQuizzsLancesParLesJoueurs($repQuizzResult);
        $scoreTotal = 0 ;
        
         foreach($repQuizzResult->findAll() as $value){
             $scoreTotal = $scoreTotal + $value->getWinPoints() ;
         }
                     
         if($quizzEffectuees == 0 && $scoreTotal = 0 ){ /* si score des joueurs impossible à calculer on retourne un message d'erreur */
             return "Score moyen des joueurs impossible à calculer !";
         }
         else{
            $scoreMoyen = $scoreTotal / $quizzEffectuees ; 
            return  intval($scoreMoyen) ; /* si score possible à calculer on renvoie un entier */
         }
    }
    
   private function nombreQuizzsLancesParLesJoueurs($repQuizzResult){
        
         $allQuizzResult = $repQuizzResult->findAll();
         $quizzEffectuees = 0 ;
         
         foreach($allQuizzResult as $value){
             if($value->getDateStart()->format('Y-m-d H:i:s') != null){
                 $quizzEffectuees += 1 ;
             }
         }
         
         return $quizzEffectuees ;
    }
    
    
    private function calculeTop3desQuizzLesPlusUtilises($repQuizzResult){
        
       $tableauStockageIdQuizz = array(); 
       foreach($repQuizzResult->findAll() as $value){
            if($value->getDateStart()->format('Y-m-d H:i:s') != null){
                $tableauStockageIdQuizz[] = $value->getQuizz()->getId(); /* Sauvegarde des id des quizz répondus */
            }
         }
         
         /*calcul des 3 id les plus présents*/
        $toDeleteTop1 = $this->retourneIdTopQuizz($tableauStockageIdQuizz); /* retourne top id des quizz présents en bdd */
        $arraytmpForLookForTop2 = array_diff( $tableauStockageIdQuizz, array($toDeleteTop1[0])); /* une fois le meilleur quizz trouvé on le supprime pour trouver quelle est le 2nd et le 3éme */
          
        $toDeleteTop2 = $this->retourneIdTopQuizz($this->newArray($arraytmpForLookForTop2));
        $arraytmpForLookForTop3 = array_diff( $this->newArray($arraytmpForLookForTop2), array($toDeleteTop2[0]));
        
        $toDeleteTop3 = $this->retourneIdTopQuizz($this->newArray($arraytmpForLookForTop3));
                
        $arrayAllInfosForTopQuizz = array();
        $arrayAllInfosForTopQuizz[0] = $toDeleteTop1[0] ; /* id du quizz le plus utilisé en 1ére position */
        $arrayAllInfosForTopQuizz[1] = $toDeleteTop1[1] ; /* nombre utilisation du 1er quizz */
        $arrayAllInfosForTopQuizz[2] = $toDeleteTop2[0] ; /* id du quizz le plus utilisé en 2nd position */
        $arrayAllInfosForTopQuizz[3] = $toDeleteTop2[1] ; /* nombre utilisation du 2nd quizz */
        $arrayAllInfosForTopQuizz[4] = $toDeleteTop3[0] ; /* id du quizz le plus utilisé en 3éme position */
        $arrayAllInfosForTopQuizz[5] = $toDeleteTop3[1] ; /* nombre utilisation du 3éme quizz */
        
         $quizz1 = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:Quizz')->findOneById($arrayAllInfosForTopQuizz[0]);
         $arrayAllInfosForTopQuizz[6] = $quizz1->getTitle();
         
         $quizz2 = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:Quizz')->findOneById($arrayAllInfosForTopQuizz[2]);
         $arrayAllInfosForTopQuizz[7] = $quizz2->getTitle();
        
         $quizz3 = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:Quizz')->findOneById($arrayAllInfosForTopQuizz[4]);
         $arrayAllInfosForTopQuizz[8] = $quizz3->getTitle();
         
         $tableauStockageIdQuizz = array_unique($tableauStockageIdQuizz);

         unset($tableauStockageIdQuizz[array_search($arrayAllInfosForTopQuizz[0], $tableauStockageIdQuizz)]);
         unset($tableauStockageIdQuizz[array_search($arrayAllInfosForTopQuizz[2], $tableauStockageIdQuizz)]);
         unset($tableauStockageIdQuizz[array_search($arrayAllInfosForTopQuizz[4], $tableauStockageIdQuizz)]);
                  
         
        return $arrayAllInfosForTopQuizz;
    }
    
    private function newArray($arraytmp){
    /* Retourne un array avec les indices du tableau correctement placés */  
        $array = array();
         foreach($arraytmp as $value){
             $array[] = $value ;
         }
         return $array ;
    }
    
    private function retourneIdTopQuizz($array){
        /* calcule quelle est l'id du quizz le plus présent dans le tableau passé en paramétre */
         $quantiteMax = 0 ;
         $valeurMax = 0 ;
         foreach($array as $value){
             $quantite = 0 ;
             foreach($array as $value2){
                 if($value == $value2){
                     $quantite += 1 ;
                 }
             }
             if($quantite > $quantiteMax){
                 $valeurMax = $value ;
                 $quantiteMax = $quantite ;
             }
         }
         
         /* retourne id du quizz le plus utlisé avec son nombre utilisation */
         $arrayInfos = array();
         $arrayInfos[] = $valeurMax ;
         $arrayInfos[] = $quantiteMax ;
                  
         return $arrayInfos ;
    }
    
    private function calculeTop3desQuizzLesMoinsUtilises($repQuizzResult){
        $allQuizzResult = $repQuizzResult->findAll();
        
        foreach($allQuizzResult as $value){
            if($value->getDateStart()->format('Y-m-d H:i:s') != null){
                $tableauStockageIdQuizz[] = $value->getQuizz()->getId(); /* Sauvegarde des id des quizz répondus */
            }
         }
         
        /*calcul des 3 id les moins présents*/
        $toDeleteBad1 = $this->retourneIdBadQuizz($tableauStockageIdQuizz); /* retourne bad id des quizz présents en bdd */
        $arraytmpForLookForBad2 = array_diff( $tableauStockageIdQuizz, array($toDeleteBad1[0])); /* une fois le pire quizz trouvé on le supprime pour trouver quelle est le 2nd et le 3éme */

        $toDeleteBad2 = $this->retourneIdBadQuizz($this->newArray($arraytmpForLookForBad2));
        $arraytmpForLookForBad3 = array_diff( $this->newArray($arraytmpForLookForBad2), array($toDeleteBad2[0]));
  
        $toDeleteBad3 = $this->retourneIdBadQuizz($this->newArray($arraytmpForLookForBad3));
        
        $arrayAllInfosForBadQuizz = array();
        $arrayAllInfosForBadQuizz[0] = $toDeleteBad1[0]; /* id du quizz le moins utilisé en 1ére position */
        $arrayAllInfosForBadQuizz[1] = $toDeleteBad1[1]; /* nombre utilisation du 1er quizz  */
        $arrayAllInfosForBadQuizz[2] = $toDeleteBad2[0]; /* id du quizz le moins utilisé en 2nd position */
        $arrayAllInfosForBadQuizz[3] = $toDeleteBad2[1]; /* nombre utilisation du 2nd quizz  */
        $arrayAllInfosForBadQuizz[4] = $toDeleteBad3[0]; /* id du quizz le moins utilisé en 3éme position */
        $arrayAllInfosForBadQuizz[5] = $toDeleteBad3[1]; /* nombre utilisation du 3éme quizz  */
        
        $quizz1 = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:Quizz')->findOneById($arrayAllInfosForBadQuizz[0]);
         $arrayAllInfosForBadQuizz[6] = $quizz1->getTitle();
         
         $quizz2 = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:Quizz')->findOneById($arrayAllInfosForBadQuizz[2]);
         $arrayAllInfosForBadQuizz[7] = $quizz2->getTitle();
        
         $quizz3 = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:Quizz')->findOneById($arrayAllInfosForBadQuizz[4]);
         $arrayAllInfosForBadQuizz[8] = $quizz3->getTitle();
        
        return $arrayAllInfosForBadQuizz ;
    }
    
    private function retourneIdBadQuizz($array){
         /* calcule quelle est l'id du quizz le moins présent dans le tableau passé en paramétre */
         $quantiteMin = 0 ;
         $valeurMin = 0 ;
         foreach($array as $value){
             $quantite = 0 ;
             foreach($array as $value2){
                 if($value != $value2){
                     $quantite += 1 ;
                 }
             }
             if($quantite > $quantiteMin){
                 $valeurMin = $value ;
                 $quantiteMin = $quantite ;
             }
         } 
         
         
        /* retourne id du quizz le moins utlisé avec son nombre utilisation */
         $arrayInfos = array();
         $arrayInfos[] = $valeurMin ;
         $arrayInfos[] = count($array) - $quantiteMin ;
                  
         return $arrayInfos ;
    }
}
