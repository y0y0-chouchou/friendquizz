<?php

    namespace Metinet\Bundle\FacebookBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Metinet\Bundle\FacebookBundle\Entity\Question ;
    use Metinet\Bundle\FacebookBundle\Form\Type\Question\QuestionAdd ;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

class QuestionController extends Controller
{

    /**
     * @Route("/admin/question",name="adminQuestions")
     * @Template()
     */
    public function questionAction()
    {
       return array(); 
    }
    
     /**
     * @Route("/admin/question/ajout", name="addQuestion")
     * @Template()
     */
    public function ajoutAction()
    {
       
       $question = new Question();
       $form = $this->createForm(new QuestionAdd(), $question);
       $request = $this->getRequest(); 
       
       if ($request->getMethod() == "POST") { 
        $form->bindRequest($request); 
            if ($form->isValid()) {
                
                $nomTmpPicture = $question->getPicture() ;
                
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($question);
                $em->flush();
                
                $nouveauNomPicture = $question->getPath();
                $this->updateQuestionPicture($nomTmpPicture,$nouveauNomPicture,$em);
                
                return $this->redirect($this->generateUrl('adminQuestions')); 

        }
    }
       
       return array("form" => $form->createView(),
                    "question" => $question); 
    }
    
     protected function updateQuestionPicture($nomTmpPicture,$nouveauNomPicture,$em)
    {
       $questionUpdate = $em->getRepository('MetinetFacebookBundle:Question')->findOneByPicture($nomTmpPicture);
       $questionUpdate->setPicture($nouveauNomPicture);
       $em->flush();
    }
    
     protected function toutesLesQuestions()
    {
        
        $repQuestion = $this->getDoctrine()->getRepository('MetinetFacebookBundle:Question'); 
        $allQuestion = $repQuestion->findAll();
        
        return $allQuestion ;
    }  
    
}

?>
