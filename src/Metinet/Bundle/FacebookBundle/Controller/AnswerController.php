<?php

    namespace Metinet\Bundle\FacebookBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Metinet\Bundle\FacebookBundle\Entity\Answer ;
    use Metinet\Bundle\FacebookBundle\Form\Type\Answer\AnswerAdd ;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    
class AnswerController extends Controller
{
    /**
     * @Route("/admin/answer",name="adminAnswers")
     * @Template()
     */
    public function answerAction()
    {
       return array(); 
    }
    
    /**
     * @Route("/admin/answer/ajout",name="ajoutAnswer")
     * @Template()
     */
    public function ajoutAction()
    {
       
       $answer = new Answer();
       $form = $this->createForm(new AnswerAdd(), $answer);
       $request = $this->getRequest(); 
       
       if ($request->getMethod() == "POST") { 
        $form->bindRequest($request); 
            if ($form->isValid()) {
                                
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($answer);
                $em->flush();
                
                return $this->redirect($this->generateUrl('adminAnswers')); 

        }
    }
       
       return array("form" => $form->createView(),
                    "answer" => $answer); 
    }
    
    protected function toutesLesAnswers()
    {
        $repAnswer = $this->getDoctrine()->getRepository('MetinetFacebookBundle:Answer'); 
        $allAnswer = $repAnswer->findAll();
        
        return $allAnswer ;
    }

}

?>
