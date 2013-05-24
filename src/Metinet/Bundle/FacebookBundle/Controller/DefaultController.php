<?php

namespace Metinet\Bundle\FacebookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function indexAction()
    {

        /*
        *  Quizz et Profil
        */
        $repQuizz = $this->getDoctrine()->getRepository('MetinetFacebookBundle:Quizz'); /* initialsation repository quizz */

        $user = $this->objectUserEnCours(); /* object user en cour */
        $quizzs = $this->quatreDernierQuizz($repQuizz); /* 4 dernier quizzs */
        $quizzMiEnAvant = $this->calculeQuizzMisEnAvant(); /* fonction charge quizz ispromoted = 1 */



        /*
        *  CLASSEMENT
        */

        $rep = $this->getDoctrine()->getRepository('MetinetFacebookBundle:User');
        $classementGeneral = $this->classementGeneral($rep);
        $userIdenCour = $this->getIdUserConnecter();
        $MyFriends = $this->container->get('metinet.manager.fbuser')->getUserFriends("me"); /* on recupere tous les ami de utilisateur en cour */
        $stockIdMyFriends = $this->returnStockIdMyFriends();
       
       $arrayClassementMyFriend = array(); /* ami de user qui sont presents en bdd */
       foreach($stockIdMyFriends as $key => $value){
           foreach($classementGeneral as $value2){
               if($value == $value2->getFbUid()){
                   $arrayClassementMyFriend[] = $this->getDoctrine()->getManager()->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($value);
                   unset($MyFriends['data'][$key]); /* myfriends contiendra tous les ami de user sauf ceux qui sont deja presents dans le classement afin de pouvoir les defier */
               }
           }
       } 
        
       
      $arrayClassementMyFriendFinal = $this->returnArrayClassementMyFriendFinal($arrayClassementMyFriend,$this->classementGeneral($this->getDoctrine()->getRepository('MetinetFacebookBundle:User'))); 
      
                  
        $positon = 0 ;
        $nombrePoints = 0 ;
        foreach($arrayClassementMyFriendFinal as $key => $value){
            if($userIdenCour == $value->getId()){ /* calcul de sa position et de son nombre de points */
                 $position = $key + 1 ;
                 $nombrePoints = $value->getPoints();
            }
        }
        
        
        return array("classementGeneral" => $arrayClassementMyFriendFinal,
                     "userIdenCour" => $userIdenCour,
                     "nbUser" => count($arrayClassementMyFriendFinal),
                     "fbUid" => $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id'),
                     "position" => $position,
                     "nombrePoints" => $nombrePoints,
                     "MyFriends" => $MyFriends,
                     "user" => $user,
                     "quizzs" => $quizzs,
                     "QuizzMiEnAvant" => $quizzMiEnAvant);
    }


    /**
     * @Route("/classement/classementgeneralamis",name="classementgeneralamis")
     * @Template()
     */
    public function classementgeneralamisAction()
    {   
        $rep = $this->getDoctrine()->getRepository('MetinetFacebookBundle:User');
        $classementGeneral = $this->classementGeneral($rep);
        $userIdenCour = $this->getIdUserConnecter();
        $MyFriends = $this->container->get('metinet.manager.fbuser')->getUserFriends("me"); /* on recupere tous les ami de utilisateur en cour */
        $stockIdMyFriends = $this->returnStockIdMyFriends();
       
       $arrayClassementMyFriend = array(); /* ami de user qui sont presents en bdd */
       foreach($stockIdMyFriends as $key => $value){
           foreach($classementGeneral as $value2){
               if($value == $value2->getFbUid()){
                   $arrayClassementMyFriend[] = $this->getDoctrine()->getManager()->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($value);
                   unset($MyFriends['data'][$key]); /* myfriends contiendra tous les ami de user sauf ceux qui sont deja presents dans le classement afin de pouvoir les defier */
               }
           }
       } 
        
       
      $arrayClassementMyFriendFinal = $this->returnArrayClassementMyFriendFinal($arrayClassementMyFriend,$this->classementGeneral($this->getDoctrine()->getRepository('MetinetFacebookBundle:User'))); 
      
                  
        $positon = 0 ;
        $nombrePoints = 0 ;
        foreach($arrayClassementMyFriendFinal as $key => $value){
            if($userIdenCour == $value->getId()){ /* calcul de sa position et de son nombre de points */
                 $position = $key + 1 ;
                 $nombrePoints = $value->getPoints();
            }
        }
        
        
        return array("classementGeneral" => $arrayClassementMyFriendFinal,
                     "userIdenCour" => $userIdenCour,
                     "nbUser" => count($arrayClassementMyFriendFinal),
                     "fbUid" => $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id'),
                     "position" => $position,
                     "nombrePoints" => $nombrePoints,
                     "MyFriends" => $MyFriends);
    }


     /**
     * @Route("/invitation",name="indexInvitation")
     * @Template()
     */
    public function invitationAction()
    {
        return array();
    }
    
    /**
     * @Route("/invitation/friend",name="invitationFriend")
     * @Template()
     */
    public function friendAction()
    {
        $MyFriends = $this->container->get('metinet.manager.fbuser')->getUserFriends("me");

        return array("friend" => $MyFriends);
    }


    protected function returnArrayClassementMyFriendFinal($arrayClassementMyFriend,$classementDifference)
    {
        $arrayClassementMyFriendFinal = array();
         foreach($classementDifference as $value){
           foreach($arrayClassementMyFriend as $value2){               
                    if($value->getId() == $value2->getId() || $this->getIdUserConnecter() == $value->getId()){
                        $user = $this->getDoctrine()->getManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value->getId());
                        if(!in_array($user ,$arrayClassementMyFriendFinal)){
                                $arrayClassementMyFriendFinal[] = $user ; /* affiche classement des amis de user */
                        }
                    }
                }
            }
            
            return $arrayClassementMyFriendFinal ;
    }
    
    
    protected function returnIndiceMilleuForDiff($classementDifference,$userIdenCour)
    {
        $indiceMileu = 0 ;
         foreach($classementDifference as $key => $value){
            if($value->getId() == $userIdenCour){
                $indiceMileu = $key ;
              }
        }
        return $indiceMileu ; /* retourne position de user en cour pour calculer par la suite sa position +5/-5 general et +2/-2 amis */
    }

    protected function getIdUserConnecter()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');
        $user = $em->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($fbUid);
        $userIdenCour = $user->getId(); /* id sql de user en cour pour calculer par la suite sa position dans le classement */
    
        return $userIdenCour;
        
    }
    
    protected function returnStockIdMyFriends()
    {
        $MyFriends = $this->container->get('metinet.manager.fbuser')->getUserFriends("me"); /* on recupere tous les ami de utilisateur en cour */
       $stockIdMyFriends = array();
       for($i = 0 ; $i < count($MyFriends['data']) ; $i++){
           $stockIdMyFriends[] = $MyFriends['data'][$i]['id']; /* on stocke id = fbuid de user en cour */
       }
       
       return  $stockIdMyFriends ;
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


     /* calcule la position de tous les utilisateurs en fonction des points et du temps */
    private function classementGeneral($rep)
    {
                
       $stockagePoints = array();
       foreach($rep->findAll() as $value)
       {
           $stockagePoints[] = $value->getPoints();
       }
        
        rsort($stockagePoints) ; /* on trie par ordre croissant pour obtenir le classement dans le bon ordre */
        
       $stockUser = array();
       foreach($stockagePoints  as $key =>$value){
           foreach($rep->findAll() as $key2 => $value2){
               if($value2->getPoints() == $value || $key == $key2){
                   if($value2->getPoints() == $value){ /* enleve doublons */                          
                       if(!in_array( $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value2->getId()),$stockUser)){
                            $stockUser[] =  $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value2->getId());
                        }
                    }
                }
            }
       }
       
       $stockUserTmp = array();
       foreach($stockUser as $value){
          $userInformations = array();
          $userInformations['id'] = $value->getId();
          $userInformations['points'] = $value->getPoints();
          $userInformations['averageTime'] = $value->getAverageTime();
          $stockUserTmp[] = $userInformations ;
       }
       
       $stockUserFinal = array();
       foreach($stockUserTmp as $key =>$value){
           foreach($stockUserTmp as $key2 => $value2){
                if($value['points'] == $value2['points'] && $key == ($key2+1)){ /* si points egaux */
                    if($value['averageTime'] < $value2['averageTime']){ /* on teste quel utilisateur a réalisé le meilleur temps */
                        $stockUserFinal[] = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value['id']);
                        $stockUserFinal[] = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value2['id']);
                    }
                    elseif($value['averageTime'] > $value2['averageTime']){
                        $stockUserFinal[] = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value2['id']);
                        $stockUserFinal[] = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value['id']);
                    }
                     
                }
                 elseif($value['points'] != $value2['points'] && $key+1 == $key2){ /* si points sont differents pas besoins de tester le temps mi par user*/
                     if(!in_array($this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value['id']),$stockUserFinal)){
                         $stockUserFinal[] = $this->getDoctrine()->getEntityManager()->getRepository('MetinetFacebookBundle:User')->findOneById($value['id']);
                     }
                }
            }
       }
       
       $stockUserFinal[] = end($stockUser);
       
       return $stockUserFinal ;
        
    }



}
