<?php

    namespace Metinet\Bundle\FacebookBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Metinet\Bundle\FacebookBundle\Entity\Quizz ;
    use Metinet\Bundle\FacebookBundle\Entity\Theme ;
    use Metinet\Bundle\FacebookBundle\Entity\QuizzResult ;
    use Metinet\Bundle\FacebookBundle\Entity\User ;    
    use Metinet\Bundle\FacebookBundle\Form\Type\Quizz\QuizzAdd ;
    use Metinet\Bundle\FacebookBundle\Form\Type\Quizz\QuizzUpdate ; 
    use Metinet\Bundle\FacebookBundle\Form\Type\Quizz\QuizzUpdatePicture ; 
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\HttpFoundation\Response;

class QuizzController extends Controller
{

    /**
     * @Route("/admin/quizz",name="adminQuizzs")
     * @Template()
     */
    public function quizzAction()
    {
       return array(); 
    }
    
    /**
     * @Route("/admin/quizz/ispromotedupdate/{id}",name="isPromotedupdateQuizz")
     * @Template()
     */
    public function ispromotedupdateAction($id)
    {
        $this->quizzUpdateIspromoted($id,1); /* mise en avant du quizz */
        
        $stockIdQuizzIsPromoZero = array();
        foreach($this->tousLesQuizzs() as $value){
            if($value->getId() != $id){
                $stockIdQuizzIsPromoZero[] = $value->getId(); /* on stock tous les id des quizz dui seront update ispromoted = 0 */
            }
        }
        
        foreach($stockIdQuizzIsPromoZero as $value){
                $this->quizzUpdateIspromoted($value,0);
        }
        
        return $this->redirect($this->generateUrl('adminQuizzs')); 

    }
    
    
    protected function quizzUpdateIspromoted($idQuizz,$valueIsPromoted){
        $em = $this->getDoctrine()->getEntityManager();
        $quizz = $em->getRepository('MetinetFacebookBundle:Quizz')->find($idQuizz);
        
        if (!$quizz) {
            throw $this->createNotFoundException('Aucun quizz !!!');
        }

        $quizz->setIsPromoted($valueIsPromoted);
        $em->flush();
        
        
    }
    
    
    /**
     * @Route("/admin/quizz/ispromoted",name="isPromotedQuizz")
     * @Template()
     */
    public function ispromotedAction()
    {
       return array("quizzs" => $this->tousLesQuizzs()); /* permet à admin de choisir un quizz pour le mettre en avant */ 
    }

     /**
     * @Route("/admin/quizz/ajout", name="quizzAdd")
     * @Template()
     */
   public function ajoutAction()
    {
       $quizz = new Quizz();
       $form = $this->createForm(new QuizzAdd(), $quizz);
       $request = $this->getRequest(); 
       
       if ($request->getMethod() == "POST") { 
        $form->bindRequest($request); 
            if ($form->isValid()) {
                $quizz->setIsPromoted(false);
                
                $nomTmpPicture = $quizz->getPicture() ;
                
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($quizz);
                $em->flush();
                
                $nouveauNomPicture = $quizz->getPath();
                $this->updateQuizzPicture($nomTmpPicture,$nouveauNomPicture,$em);
                
                return $this->redirect($this->generateUrl('adminQuizzs')); 

        }
    }
       
       return array("form" => $form->createView(),
                    "quizz" => $quizz); 
    }
    
    
    
    protected function updateQuizzPicture($nomTmpPicture,$nouveauNomPicture,$em)
    {
       $quizzUpdate = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneByPicture($nomTmpPicture);
       $quizzUpdate->setPicture($nouveauNomPicture);
       $em->flush();
    }
    /**
     * @Route("/admin/quizz/state", name="quizzState")
     * @Template()
     */
    public function stateAction()
    {
       return array("allQuizzs" => $this->tousLesQuizzs()); 
    }
    
    /**
     * @Route("/admin/quizz/active/{id}", name="quizzActive")
     * @Template()
     */
    public function activeAction($id)
    {
       
        $this->updateQuizzState($id,1);
        return $this->redirect($this->generateUrl('adminQuizzs')); 
    }
    
    
    /**
     * @Route("/admin/quizz/desactive/{id}", name="quizzDesactive")
     * @Template()
     */
    public function desactiveAction($id)
    {
       
        $this->updateQuizzState($id,0);
        return $this->redirect($this->generateUrl('adminQuizzs')); 
    }
    
    protected function updateQuizzState($id,$state)
    {
         
        $em = $this->getDoctrine()->getEntityManager();
        $quizz = $em->getRepository('MetinetFacebookBundle:Quizz')->find($id);
        
        if (!$quizz) {
            throw $this->createNotFoundException('Aucun quizz !!!');
        }

        $quizz->setState($state);
        $em->flush();
    }
    

    protected function tousLesQuizzs()
    {
        
        $repQuizz = $this->getDoctrine()->getRepository('MetinetFacebookBundle:Quizz'); 
        $allQuizz = $repQuizz->findAll();


        return $allQuizz ;
    }


    /**
     * @Route("quizz/all", name="allQuizz")
     * @Template()
     */
    public function allQuizzAction()
    {
      $em =  $this->getDoctrine()->getManager();
      $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');
      $user = $em->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($fbUid);
      $userIdenCour = $user->getId();

       return array("user"=> $userIdenCour, "quizzs" => $this->tousLesQuizzs()); 
    }

    
    /**
     * @Route("/admin/quizz/suppression", name="quizzDel")
     * @Template()
     */
    public function supressionAction()
    {
       return array("quizzs" => $this->tousLesQuizzs()); 
    }
    
    /**
     * @Route("/admin/quizz/supressionquizz/{id}", name="quizzDelId")
     * @Template()
     */
    public function supressionquizzAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       $quizz = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);
       $em->remove($quizz);
       $em->flush();
       return $this->redirect($this->generateUrl('adminQuizzs'));
    }
    
    /**
     * @Route("/admin/quizz/update", name="quizzUpdate")
     * @Template()
     */
    public function updateAction()
    {
       return array("quizzs" => $this->tousLesQuizzs()); 
    }
    
    
    /**
     * @Route("/admin/quizz/updatequizz/{id}", name="quizzUpdateId")
     * @Template()
     */
    public function updatequizzAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       $quizzSql = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);
       
       $form = $this->createForm(new QuizzUpdate(), $quizzSql);
       $request = $this->getRequest(); 
             
       if ($request->getMethod() == "POST") { 
        $form->bindRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($quizzSql);
                $em->flush();
                return $this->redirect($this->generateUrl('adminQuizzs'));

        }
    }
       
       return array("form" => $form->createView(),
                    "quizz" => $quizzSql,
                    "idQuizz" => $id); 
    }


    /**
     * @Route("/quizz/quizzrepondre/{id}", name="repQuizzId")
     * @Template()
     */
    public function quizzRepondreAction($id)
    {

      $em = $this->getDoctrine()->getManager();
      $quizz = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);

      //Image du Quizz
      $image = $quizz->getPicture();
  
      // On recupère toutes les questions en fonction du quizz passée en param
      $quizzquestion = $quizz->getQuestions();
      // On insert toutes les questions dans une array
      $questionTab = array();
      // Array pour les questions aléatoires

      // On met tous les titre des questions dans une array
      foreach($quizzquestion as $key => $value){
         
          // Des array Temporaires afin de mettre en place les liens entre réponses et questions
          $arrayTmp = array();
          $allReponse = array();

          $arrayTest = array();


           // On recupere les réponses en fonction des question
          $reponse = $em->getRepository('MetinetFacebookBundle:Question')->findOneById($value->getId());
          $obj_answer = $reponse->getAnswers();

          // On insert dans la première array temp le titre de la question et son image associé
          $arrayTmp['title'] = $value->getTitle();
          // Si la question n'a pas d'image on affiche l'image du quizz
          if($value->getPicture() == ""){
              $arrayTmp['picture'] = $image;
          } else{
              $arrayTmp['picture'] = $value->getPicture();
          }
          

          // On parcours les reponses associé aux questions et on les affecte aux questions qui leur correspond(les réponses)
          foreach($obj_answer as $val){

              $arrayTmp2 = array();

              $arrayTmp2['id'] = $val->getId();
              $arrayTmp2['reponse'] = $val->getTitle();
              // Insert dans l'array la valeur 1 ou 0
              if($val->getIsCorrect() == 0) {
                  $arrayTmp2['isCorrect'] = 0;
              } else{
                  $arrayTmp2['isCorrect'] = $val->getIsCorrect();
              }

              $allReponse[] = $arrayTmp2; 
          }

         
          
          // Fonction permettant de melanger les clé d'une array
          shuffle($allReponse);
          foreach ($allReponse as $all) {
             // On insert le tableau de réponse dans la question qui lui est associé
              $arrayTmp['answer'] = $allReponse;
          }

          // On insert les réponses dans une array, afin de disposer des clé INT et de les mélanger
          $arrayQuestionAleatoire[] = $arrayTmp;
          shuffle($arrayQuestionAleatoire);

          // On affecte les nouvelles valeurs aléatoires au tableau final de questions
          foreach ($arrayQuestionAleatoire as $all) {
             // On insert le tableau de réponse dans la question qui lui est associé
              $questionTab = $arrayQuestionAleatoire;
          }


      }
      
      $nb_question = count($questionTab);

       return array("quizz" => $quizz,
        "question" => $questionTab,
        "nb_question" => $nb_question,
        "image" => $image); 
    }
    

    /**
     * Fonction appelée en AJAX qui va enregistrer la réponse de l'user pour la question du quizz à laquelle il vient de répondre
     * @Route("/quizz/reponse", name="reponse")
     */
    public function reponseAnswerAction(){

      $request = $this->get('request');
      
      // Valeur du formulaire
      $idRep = $request->request->get('id_reponse');

      $em = $this->getDoctrine()->getEntityManager();

    // si la fonction a été appelée par AJAX
    if($request->isXmlHttpRequest()){

              // instanciation des repositories
        $userRepository = $em->getRepository('MetinetFacebookBundle:User');
        $answerRepository = $em->getRepository('MetinetFacebookBundle:Answer');

        // récupération de l'user à partir de sa connection sbook
        $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');
        $user = $em->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($fbUid);  

        // On trouve la réponse associé à l'id
        $answer = $answerRepository->find($idRep);

        // on ajoute l'objet Answer à l'User
        $user->addAnswer($answer);


        
        // on merge l'User en BDD pour enregistrer ses réponses au quizz
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($user);
          $em->flush();
          //sleep(1);
        // on retourne un json disant que l'enregistrement a été fait
        $return=json_encode(array("responseCode"=>200, "idReponse"=>$idRep));

        return new Response($return,200,array('Content-Type'=>'application/json'));
    } 

    else {
      // on retourne un json disant que l'enregistrement a été fait
        $return=json_encode(array("responseCode"=>400, "idReponse"=>$idRep));

        return new Response($return,200,array('Content-Type'=>'application/json'));
    }

  }


 /**
     * @Route("/admin/quizz/updatepicture/{id}")
     * @Template()
     */
    public function updatepictureAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       $quizzSql = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);
       $quizz = new Quizz();
       
       $form = $this->createForm(new QuizzUpdatePicture(), $quizz);
       $request = $this->getRequest(); 
             
       if ($request->getMethod() == "POST") { 
           
           
        $form->bindRequest($request);
            if ($form->isValid()) {
                
                $quizzSql->setPicture($quizz->getPicture());
                $nomTmpPicture = $quizz->getPicture() ;
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($quizzSql);
                $em->flush();
                
                $nouveauNomPicture = $quizz->preUpload();
                $this->updateQuizzPicture($nomTmpPicture,$nouveauNomPicture,$em);
                
                return $this->redirect($this->generateUrl('adminQuizzs'));

        }
    }
       
       return array("form" => $form->createView(),
                    "quizz" => $quizzSql);  
    }












    /**
     * @Route("/adminquizz/choixdetailquizz",name="choixquizz")
     * @Template()
     */
    public function choixdetailquizzAction()
    {
       return array("quizzs" => $this->tousLesQuizzs()); 
    }
    
    /**
     * @Route("quizz/detailquizz/{id}",name="detailquizz")
     * @Template()
     */
    public function detailquizzAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       $quizzSql = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);
           
       $Top10 = $this->calculClassementForOneQuizz($id);
       $top10vue = array();    
       
            foreach($Top10 as $key => $value)
            {
                if($key <= 9)
                    $top10vue[] = $value ; /* on garde que les 10 meilleurs */
            }
            
       $MyFriends = $this->container->get('metinet.manager.fbuser')->getUserFriends("me"); /* on recupere tous les ami de utilisateur en cour */
       $stockIdMyFriends = array();
       for($i = 0 ; $i < count($MyFriends['data']) ; $i++){
           $stockIdMyFriends[] = $MyFriends['data'][$i]['id']; /* on stocke id = fbuid de user en cour */
       }

       
       $arrayClassementMyFriend = array(); /* ami de user qui ont repondu au quizz */
       foreach($stockIdMyFriends as $key => $value){
           foreach($Top10 as $value2){
               if($value == $value2['user']->getFbUid()){
                   $arrayClassementMyFriend[] = $em->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($value);
                   unset($MyFriends['data'][$key]); /* myfriends contiendra tous les ami de user sauf ceux qui ont deja repondu afin de pouvoir defier les autres */
               }
           }
       }   
       
       
       $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');
       $user = $em->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($fbUid);
       $userIdenCour = $user->getId();
       
       $arrayClassementMyFriendFinal = array();
       foreach($Top10 as $value){
           foreach($arrayClassementMyFriend as $value2){               
                    if($value['user']->getId() == $value2->getId() || $userIdenCour == $value['user']->getId()){
                        $tmp = array();
                        $tmp['user'] = $em->getRepository('MetinetFacebookBundle:User')->findOneById($value['user']->getId());
                        $tmp['QuizzResult'] = $value['nombreDePoints'] ;
                        if(!in_array($tmp ,$arrayClassementMyFriendFinal)){
                                $arrayClassementMyFriendFinal[] = $tmp ; /* affiche classement des amis de user */
                        }
                    }
                }
            }
          
              
       
       $quizz = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);
       $nombreDePointsAGagnerSurLeQuizz = $quizz->getWinPoints();
              
       $quizzResult = $quizz->getQuizzResults(); /* on recupere les quizzresult sur le quizz demandé */
       
       $resultQuizzUser = array();
       
       $infoQuizzRepondu = "" ; /* va determiner si user en cour a deja repondu au quizz ou non */
       
       $nombreDePointsDuQuizzTotal = 0;
       $nombreDePointsAddtioner = 0 ;
       
       foreach($quizzResult as $value){
           
           if($value->getQuizz()->getId() == $id){
                $nombreDePointsDuQuizzTotal = $nombreDePointsDuQuizzTotal + $value->getWinPoints();
                $nombreDePointsAddtioner += 1 ;
           }
           
           if($userIdenCour == $value->getUser()->getId() && $value->getQuizz()->getId() == $id){
               
               $infoQuizzRepondu = true ;
               
                $resultQuizzUser[0] = $value->getWinPoints();
                $resultQuizzUser[1] = $value->getAverage();
                $resultQuizzUser[2] = $value->getdateStart();
                $resultQuizzUser[3] = $value->getdateEnd();
                
                    if($resultQuizzUser[1] <= 25 && $resultQuizzUser[1] >= 0){ /* on recupere le texte qui correspond au score */
                        $resultQuizzUser[5] = $quizz->geTtxtWin1();
                    }
                    if($resultQuizzUser[1] <= 50 && $resultQuizzUser[1] >= 26){
                        $resultQuizzUser[5] = $quizz->geTtxtWin2();
                    }
                    if($resultQuizzUser[1] <= 75 && $resultQuizzUser[1] >= 51){
                        $resultQuizzUser[5] = $quizz->geTtxtWin3();
                    }
                    if($resultQuizzUser[1] <= 100 && $resultQuizzUser[1] >=76){
                        $resultQuizzUser[5] = $quizz->geTtxtWin4();
                    }
                
           }
            elseif($value->getUser()->getId() == null){
               $infoQuizzRepondu = false ; 
           }
       }

       if($infoQuizzRepondu){ /* on calcule le temps mi à repondre */
            $resultQuizzUser[4] = strtotime($resultQuizzUser[3]->format('Y-m-d H:i:s')) - strtotime($resultQuizzUser[2]->format('Y-m-d H:i:s')) ;
       }
       
       
       if($nombreDePointsAddtioner != 0){
            $nbMoyen = $nombreDePointsDuQuizzTotal / $nombreDePointsAddtioner ;
       }else{
           $nbMoyen = 0 ;
       }
       
       $u = $this->objectUserEnCours();
       return array("quizz" => $quizzSql,
                    "nombreDeQuestions" => count($quizzSql->getQuestions()),
                    "top" => $top10vue,
                    "place" => ( 10 - count($Top10) ) ,
                    "afficheInfo" => $resultQuizzUser,
                    "infoQuizzRepondu" => $infoQuizzRepondu,
                    "nombreParticipants" => count($Top10),
                    "nombreDePointsMoyenDuQuizz" => ( $nbMoyen ),
                    "nombreDePointsAGagnerSurLeQuizz" => $nombreDePointsAGagnerSurLeQuizz,
                    "fbUid" => $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id'),
                    "MyFriends" => $MyFriends,
                    "arrayClassementMyFriend" => $arrayClassementMyFriendFinal,
                    "idUser" => $u->getId()
                  );
    }
    
    protected function objectUserEnCours(){ /* retourne id sql de user en cour */
       $fbUid = $this->getRequest()->getSession()->get('_fos_facebook_fb_482361481839052_user_id');
       $user = $this->getDoctrine()->getManager()->getRepository('MetinetFacebookBundle:User')->findOneByfbUid($fbUid);
       return $user ;
    }
    
    protected function calculClassementForOneQuizz($id)
    {
       $em = $this->getDoctrine()->getManager();
       $quizz = $em->getRepository('MetinetFacebookBundle:Quizz')->findOneById($id);
       
       $quizzResult = $quizz->getQuizzResults(); /* on recupere les quizzresult sur le quizz demandé */
       
       $stockagePoints = array();
       foreach($quizzResult as $value)
       {
           $stockagePoints[] = $value->getWinPoints();
       }
       
       rsort($stockagePoints) ; /* on trie par ordre croissant pour obtenir le classement dans le bon ordre */
              
       $stockTop10 = array();
       foreach($stockagePoints  as $key =>$value){
           foreach($quizzResult as $key2 => $value2){
               if($value2->getWinPoints() == $value || $key == $key2){
                   if($value2->getWinPoints() == $value){ /* enleve doublons */
                        $result = array();
                        $user = $em->getRepository('MetinetFacebookBundle:User')->findOneById($value2->getUser()->getId());
                        $result['user'] = $user ;
                        $result['nombreDePoints'] = $value ;
                        $result['quizzResult'] = $value2;
                         if(!in_array($result,$stockTop10)){
                            $stockTop10[] = $result;
                        }
                    }
                }
            }
        }
        
       

       return $stockTop10;
       
    }
    
    /**
     * @Route("/admin/quizz/choixtheme",name="choixthemeduquizz")
     * @Template()
     */
    public function choixthemeAction()
    {
       $arrayTheme = array();
       foreach($this->tousLesQuizzs() as $value)
       {
           if (!in_array($value->getTheme(), $arrayTheme)) { /*evite la duplication des themes*/
            
                $arrayTheme[] = $value->getTheme() ;
            
           }
           
       }
       
       return array("arrayTheme" => $arrayTheme); 
    }
    
    /**
     * @Route("quizz/quizzcorrespondant/{theme}/{id}",name="choixduquizzcorrespondant")
     * @Template()
     */
    public function quizzcorrespondantAction($theme,$id)
    {
       $em = $this->getDoctrine()->getManager();
       $themeSql = $em->getRepository('MetinetFacebookBundle:Theme')->findOneByTitle($theme);
       $themeQuizz = $em->getRepository('MetinetFacebookBundle:Theme')->findOneById($id);
       $themeQuizzarray = array();
       
       foreach($themeQuizz->getQuizzes() as $value){
           $themeQuizzarray[] = $value ;
       }
       
       $themeQuizzNewQuizzInFirst = array_reverse($themeQuizzarray); /*permet d'afficher les quizz crées en dernier en premier sur la page*/
       
       return array("nombreDeQuizz" => count($themeQuizzNewQuizzInFirst),
                    "quizz" => $themeQuizzNewQuizzInFirst,
                    "rappelTheme" => $themeSql); 
    }


    /**
     * @Route("/admin/quizz/quizzcorrespondant/{theme}/{id}",name="adminchoixduquizzcorrespondant")
     * @Template()
     */
    public function adminquizzcorrespondantAction($theme,$id)
    {
       $em = $this->getDoctrine()->getManager();
       $themeSql = $em->getRepository('MetinetFacebookBundle:Theme')->findOneByTitle($theme);
       $themeQuizz = $em->getRepository('MetinetFacebookBundle:Theme')->findOneById($id);
       $themeQuizzarray = array();
       
       foreach($themeQuizz->getQuizzes() as $value){
           $themeQuizzarray[] = $value ;
       }
       
       $themeQuizzNewQuizzInFirst = array_reverse($themeQuizzarray); /*permet d'afficher les quizz crées en dernier en premier sur la page*/
       
       return array("nombreDeQuizz" => count($themeQuizzNewQuizzInFirst),
                    "quizz" => $themeQuizzNewQuizzInFirst,
                    "rappelTheme" => $themeSql); 
    }






}

?>
