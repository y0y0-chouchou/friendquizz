<?php

    namespace Metinet\Bundle\FacebookBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassementController extends Controller
{
    
    /**
     * @Route("/classement",name="indexClassement")
     * @Template()
     */
    public function indexAction() /* page d'accueil des classements */
    {
        return array();
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
     * @Route("/classement/classementdifference",name="classementdifference")
     * @Template()
     */
    public function classementdifferenceAction()
    {   
        
        $classementGeneralPlus5Moins5 = array(); /* array final qui sera retourner pour affichage du classement */
        $indiceMileu = $this->returnIndiceMilleuForDiff($this->classementGeneral($this->getDoctrine()->getRepository('MetinetFacebookBundle:User')),$this->getIdUserConnecter()); /* recupere position de utilisateur */
        foreach($this->classementGeneral($this->getDoctrine()->getRepository('MetinetFacebookBundle:User')) as $key => $value){              
             if( ($key >= $indiceMileu && $key <= ($indiceMileu + 5)) || ($key <= $indiceMileu && $key >= ($indiceMileu - 5) )){
                    $classementGeneralPlus5Moins5[] = $value ; /* en fonction de la position de user on stock ce dernier plus ceux qui sont avant et aprés lui en +5/-5 */
             }
        }
                
        return array("classementDifference" => $classementGeneralPlus5Moins5,
                     "userIdenCour" => $this->getIdUserConnecter()
                     );
    }
    
    
    /**
     * @Route("/classement/classementdifferenceamis",name="classementdifferenceamis")
     * @Template()
     */
    public function classementdifferenceamisAction()
    {   
        $classementDifference = $this->classementGeneral($this->getDoctrine()->getRepository('MetinetFacebookBundle:User'));        
        $MyFriends = $this->container->get('metinet.manager.fbuser')->getUserFriends("me"); /* on recupere tous les ami de utilisateur en cour */
        $stockIdMyFriends = $this->returnStockIdMyFriends();
        
       $arrayClassementMyFriend = array(); /* ami de user qui sont presents en bdd */
       foreach($stockIdMyFriends as $key => $value){
           foreach($classementDifference as $value2){
               if($value == $value2->getFbUid()){
                   $arrayClassementMyFriend[] = $this->getDoctrine()->getManager()->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($value);
                   unset($MyFriends['data'][$key]); /* myfriends contiendra tous les ami de user sauf ceux qui sont deja presents dans le classement afin de pouvoir les defier */
               }
           }
       } 
        
        $arrayClassementMyFriendFinal = $this->returnArrayClassementMyFriendFinal($arrayClassementMyFriend,$this->classementGeneral($this->getDoctrine()->getRepository('MetinetFacebookBundle:User')));
        $indiceMileu = $this->returnIndiceMilleuForDiff($arrayClassementMyFriendFinal,$this->getIdUserConnecter()); /* recupere position de utilisateur */
        $ArrayFinalClassement = array();
        foreach($arrayClassementMyFriendFinal as $key => $value){              
             if( ($key >= $indiceMileu && $key <= ($indiceMileu + 2)) || ($key <= $indiceMileu && $key >= ($indiceMileu - 2) )){
                    $ArrayFinalClassement[] = $value ;
             }
        }

        return array("classementDifference" => $ArrayFinalClassement,
                     "userIdenCour" => $this->getIdUserConnecter()
                     );
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
    
    
    /**
     * @Route("/classement/classementgeneral",name="classementgeneral")
     * @Template()
     */
    public function classementgeneralAction()
    {   
        $rep = $this->getDoctrine()->getRepository('MetinetFacebookBundle:User');
        $classementGeneral = $this->classementGeneral($rep);
        
        $userIdenCour = $this->getIdUserConnecter();
        
        $positon = 0 ;
        $nombrePoints = 0 ;
        foreach($classementGeneral as $key => $value){
            if($userIdenCour == $value->getId()){
                 $position = $key + 1 ;
                 $nombrePoints = $value->getPoints();
            }
        }
        
        
        return array("classementGeneral" => $classementGeneral,
                     "userIdenCour" => $userIdenCour,
                     "nbUser" => count($allUsers = $rep->findAll()),
                     "fbUid" => $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id'),
                     "position" => $position,
                     "nombrePoints" => $nombrePoints);
    }
    
    /**
     * @Route("/classement/classementtop10",name="classementtop10")
     * @Template()
     */
    public function classementtop10Action()
    {   
        $rep = $this->getDoctrine()->getRepository('MetinetFacebookBundle:User');
        $classementGeneral = $this->classementGeneral($rep);
                
        $userIdenCour = $this->getIdUserConnecter();

        
            $top10vue = array();    
            
            $presenceTop10 = false ;
            $positionTop10 = "";
            $nombrePoints = "";
            foreach($classementGeneral as $key => $value)
            {                
                if($key <= 9){
                    $top10vue[] = $value ; /* on garde que les 10 meilleurs */
                    if($value->getId() == $userIdenCour){
                        $positionTop10 = $key ;
                        $presenceTop10 = true ;
                        $nombrePoints = $value->getPoints();
                    }
                }
            }
        
        return array("classementTop10" => $top10vue,
                     "userIdenCour" => $userIdenCour,
                     "presenceTop10" => $presenceTop10,
                     "positionTop10" => $positionTop10,
                     "fbUid" => $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id'),
                     "nombrePoints" => $nombrePoints
                     );
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

?>
