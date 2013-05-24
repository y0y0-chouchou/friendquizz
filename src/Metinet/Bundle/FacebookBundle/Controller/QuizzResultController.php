<?php

namespace Metinet\Bundle\FacebookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Metinet\Bundle\FacebookBundle\Entity\Quizz ;
use Metinet\Bundle\FacebookBundle\Entity\Theme ;
use Metinet\Bundle\FacebookBundle\Entity\QuizzResult ;
use Metinet\Bundle\FacebookBundle\Entity\User ;
use Symfony\Component\HttpFoundation\Response;

class QuizzResultController extends Controller
{

  /**
  * @Route("/quizzresult",name="quizzresultindex")
  * @Template()
  */
  public function quizzresultAction()
  {
    return array();
  }


     /**
     * @Route("/quizzresult/ajoutDebut/{id_user}/{id_quizz}", name="quizzResultAddDebut")
     * @Template()
     */
    public function ajoutDebutAction($id_user,$id_quizz)
    {
       

       $quizzResult = new QuizzResult(); 
       $date = new \DateTime("now");

       // On récupère l'user et le quizz
       $user = $this->userCo($id_user);
       $quizz = $this->quizzSpecifique($id_quizz);
       
       //On récupère tous les quizz_results en fonction du user_id et du quizz_id
       $em = $this->getDoctrine()->getEntityManager();
       $quizzRes = $em->getRepository('MetinetFacebookBundle:QuizzResult');

       $alreadyPlay = $quizzRes->hasPlayedThisQuizz($quizz,$user);


       if( !empty($quizzRes) && $alreadyPlay == true){
          return $this->redirect($this->generateUrl('allQuizz'));
       } else{

        $quizzResult->setUser($user);
        $quizzResult->setQuizz($quizz);
        $quizzResult->setDateStart($date);

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($quizzResult);
        $em->flush();


        return $this->redirect($this->generateUrl('repQuizzId', array('id' => $id_quizz))); 
       }


      

      //return array();

    }

    /**
     * @Route("/quizzresult/resultat/{id}/{nb_question}", name="resultatQuizz")
     * @Template()
     */
    public function resultatQuizzAction($id, $nb_question)
    {

        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request');
        // Valeur du formulaire
        $res = $request->request->get('resultat');
        $time = $request->request->get('btn_time');

        // Quizz selectionnée par l'user
        $quizz = $this->quizzSpecifique($id);
        /* 
        *  On crée une array avec le STRING envoyé par le paramétre. Ce string est envoyé par le formulaire du quizz
        *  et la value de ce formulaire (voir résultat) est rempli en javascript dans form_validation.js 
        */
        $resultat = explode(",",$res);


        // Ces arrays permet de carlculer le resultat final du l'utilisateur
        $tmp = array();
        $myAnswer = array();

        $title_quizz = "";
        $phrase_taux = "";

        // On parcours l'array des resultats pour récupérer toutes les finos nécéssaires
        foreach($resultat as $v){

          // On veut récupérer toutes les données concernant la réponse (titre, suestion id etc...)
          $ans = $em->getRepository('MetinetFacebookBundle:Answer')->findOneById($v);
          // Infos du quizz
          $title_quizz = $quizz->getTitle();

          // Donnée nécéssaire pour calculer le resultat final
          $tmp['title'] = $ans->getTitle();
          if( $ans->getisCorrect() == 0){
              $tmp['isCorrect'] = 0;
          } else{
              $tmp['isCorrect'] = $ans->getisCorrect();
          }
          
          

          $myAnswer[] = $tmp;
        }


        // On récupere le nombre de point du quizz
        $win_points = $quizz->getWinPoints();
        // Récupération du nombre de question
        $nbr_ques = $nb_question;
        // Calcul du nombre de point par question
        $nbPtParQuestion = $win_points / $nbr_ques;
        // Variable pour le résultat final
        $resultatFinal = 0;

        $i=0;
        foreach($myAnswer as $v){
            if($v['isCorrect'] == 1){
               $resultatFinal += $nbPtParQuestion;
               $i++;
            } 
        }

        /*echo "<pre>";
        print_r($re);
        echo "</pre>";*/

        /*echo "<pre>";
        print_r($myAnswer);
        echo "</pre>";*/

        // Calcul pour avoir le taux de réussite
        $taux = ( $resultatFinal * 100 ) / $win_points;

        if($taux >= 0 || $taux <= 100){
          if( $taux >= 0 && $taux <= 25 ){
              $phrase_taux = $quizz->getTxtWin1();
          } 
          else if( $taux > 25 && $taux <= 50 ){
              $phrase_taux = $quizz->getTxtWin2();
          }
          else if( $taux > 50 && $taux <= 75 ){
              $phrase_taux = $quizz->getTxtWin3();
          }
          else if( $taux > 75 && $taux <= 100 ){
              $phrase_taux = $quizz->getTxtWin4();
          }
        }
        else{
          $phrase_taux = "Mauvais calcul de taux";
        }

        // Id de user FB pour le partage sur son mur
        $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');

        // Texte pour partager sur le mur. Description de la page
        $wall_title = $quizz->getShareWallTitle();
        $wall_desc = $quizz->getShareWallDesc();

        // Image quizz
        $image = $quizz->getPicture();




        // Update de quizz Result
        $date_end = new \DateTime("now");
        $user = $this->userCo($this->idUserCo($em));
        //On récupère tous les quizz_results en fonction du user_id et du quizz_id
        $quizzRepository = $em->getRepository('MetinetFacebookBundle:QuizzResult');
        $quizzResults = $quizzRepository->updateResultQuizz($quizz, $user);

        $point = $quizzResults->getWinPoints();
        $date_start = $quizzResults->getDateStart();

        $average = strtotime($date_end->format('Y-m-d H:i:s')) - strtotime($date_start->format('Y-m-d H:i:s')) ;

        //print_r($quizzResults->getId());
        $quizzResults->setDateEnd($date_end);
        $quizzResults->setAverage($taux);
        $quizzResults->setWinPoints($resultatFinal);

        if(!$user){
          throw $this->createNotFoundException("Aucun User trouvé en BDD");
        } 

        $em->persist($quizzResults);
        $em->flush();

        $this->bonusMalusPoints($id);


        $quizzUser = $quizzRepository->findBy(array("user" => $user));
        $pointTotal = 0;
        $tempsTotal = 0;
        foreach($quizzUser as $v){
            $pointTotal += $v->getWinPoints();
            $tempsTotal += $v->getAverage();

        }
        $tempsTotal = intval($tempsTotal / count($quizzUser));

        $user->setPoints($pointTotal);
        $user->setAverageTime($tempsTotal);
        $user->setNbQuizz(count($quizzUser));
        $em->persist($user);
        $em->flush();

        return array(
          "win_points" => $win_points,
          "nbQuestion" => $nbr_ques,
          "nbPoint" => $nbPtParQuestion,
          "resultatFinal" => $resultatFinal,
          "nbBonneReponse" => $i,
          "title_quizz" => $title_quizz,
          "taux" => $taux,
          "phrase" => $phrase_taux,
          "fbUid" => $fbUid,
          "wall_title" => $wall_title,
          "wall_desc" => $wall_desc,
          "image" => $image,
          "quizz" => $quizz
          );
    }

  protected function ansQuizz($resultat)
  {
    $em = $this->getDoctrine()->getEntityManager();
    $ans = $em->getRepository('MetinetFacebookBundle:Answer')->findBy(array("id" => $resultat));
    return $ans ;
  }

  protected function quizzSpecifique($id)
  {
    $em = $this->getDoctrine()->getEntityManager();
    $quizz = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);
    return $quizz ;
  }


  protected function idUserCo($em)
  {
    $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');
    $user = $em->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($fbUid);
    $userIdenCour = $user->getId();
    return $userIdenCour ;
  }

  protected function userCo($id)
  {
    $em = $this->getDoctrine()->getEntityManager();
    $user = $em->getRepository('MetinetFacebookBundle:User')->findOneById($id);
    return $user ;
  }



  protected function bonusMalusPoints($id){

  $quizz = $this->quizzSpecifique($id);
  $quizzResult = $quizz->getQuizzResults(); /* on recupere les quizzresult sur le quizz demandé */

  $em = $this->getDoctrine()->getEntityManager();

  $userIdenCour = $this->idUserCo($em);

  $tempsUtilisateur = "" ;
  $winPointsUser = "" ;
  $averageUser = "" ;
  $idQuizzResult = "" ;

    foreach($quizzResult as $value){
      if($userIdenCour == $value->getUser()->getId() && $value->getQuizz()->getId() == $id){ /* on recupere les informations de la table quizz result pour recuperer les informations concernant id du quizz souhaité et id de l'user */
        $tempsUtilisateur = strtotime($value->getdateEnd()->format('Y-m-d H:i:s')) - strtotime($value->getdateStart()->format('Y-m-d H:i:s')) ; /* temps user mi a repondre en secondes */
        $winPointsUser = $value->getWinPoints(); /* stockage de winpoints de user */
        $averageUser = $value->getAverage();
        $idQuizzResult = $value->getId(); /* stockage de id quizzresult pour update du nombre de points */
      }
  }


  $tempsMoyenDuQuizz = $quizz->getAverageTime(); /* temps moyen du quizz */

  if($tempsUtilisateur < $tempsMoyenDuQuizz ){ /* on teste si user a repondu a toutes les questions avt le temps moyen */
    if($averageUser >= 75){ /* si 75% de bonne réponses */
      $nombreDePointsEnPlus = $quizz->getWinPoints() * 25 / 100 ; /* calcul des points en plus */
      $nouveauNombreDePoints = $winPointsUser + $nombreDePointsEnPlus ; /* nouveau nombre de points */
      $this->updateWinPointsQuizzResult($idQuizzResult,$nouveauNombreDePoints);
    }
  }

  else if($tempsUtilisateur > $tempsMoyenDuQuizz ){ /* si utilisateur repond aux questions apres le temps imparti => malus */
      $nombreDePointsEnMoins = $quizz->getWinPoints() * 15 / 100 ; /* calcul des points en moins */
      $nouveauNombreDePoints = $winPointsUser - $nombreDePointsEnMoins ; /* nouveau nombre de points */
      $this->updateWinPointsQuizzResult($idQuizzResult,$nouveauNombreDePoints);
  }

  }


  protected function updateWinPointsQuizzResult($id,$nb) /* fonction qui va update le nombre de points en fonction du bonus / malus */
  {
    $em = $this->getDoctrine()->getEntityManager();
    $quizzResultSql = $em->getRepository('MetinetFacebookBundle:QuizzResult')->findOneById($id);

    if (!$quizzResultSql) {
      throw $this->createNotFoundException('Aucun quizz Result !!!');
    }

    $quizzResultSql->setWinPoints($nb); /* update du nouveau nombre de points */
    $em->flush();
  }


}

?>