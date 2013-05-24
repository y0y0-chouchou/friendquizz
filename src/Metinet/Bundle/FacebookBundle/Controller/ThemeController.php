<?php

    namespace Metinet\Bundle\FacebookBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Metinet\Bundle\FacebookBundle\Entity\Theme ;
    use Metinet\Bundle\FacebookBundle\Form\Type\Theme\ThemeAdd ;
    use Metinet\Bundle\FacebookBundle\Form\Type\Theme\ThemeUpdate ; 
    use Metinet\Bundle\FacebookBundle\Form\Type\Theme\ThemeUpdatePicture ;     
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    
class ThemeController extends Controller
{   
     /**
     * @Route("/admin/theme",name="adminThemes")
     * @Template()
     */
    public function themeAction()
    {
       return array(); 
    }
    
     /**
     * @Route("/admin/theme/ajout", name="addTheme")
     * @Template()
     */
    public function ajoutAction()
    {
        /* On initialise l'objet Theme et on l'associe
        *  au formulaire ThemeAdd via la méthode createForm */
       $theme = new Theme();
       $form = $this->createForm(new ThemeAdd(), $theme);
       $request = $this->getRequest(); 
       
       if ($request->getMethod() == "POST") { /* Si le formulaire est posté */
        $form->bindRequest($request); /* on récupére les données du formulaire */
            if ($form->isValid()) { /* si form valide insert en bdd */
    
                $theme->setTitle($theme->getTitle()); /*insertion en bdd d'un nouveau théme*/
                $theme->setShortDesc($theme->getShortDesc());
                $theme->setLongDesc($theme->getLongDesc()); 
                $nomTmpPicture = $theme->getPicture() ;
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($theme);
                $em->flush();
                
                $nouveauNomPicture = $theme->getPath();
                $this->updateThemePicture($nomTmpPicture,$nouveauNomPicture,$em);
                
                return $this->redirect($this->generateUrl('adminThemes')); /* Redirection */

        }
    }
       
       /*On rend la vue du formulaire avec CreateView et on
        * passe l'objet theme à notre formulaire*/
       return array("form" => $form->createView(),
                    "theme" => $theme); 
    }
    
     /**
     * @Route("/admin/theme/supression", name="suppTheme")
     * @Template()
     */
    public function supressionAction()
    {
       return array("themes" => $this->tousLesThemes()); 
    }
    
    /**
     * @Route("/admin/theme/supressiontheme/{id}", name="delTheme")
     * @Template()
     */
    public function supressionthemeAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       $theme = $em->getRepository('MetinetFacebookBundle:Theme')->findOneById($id);
       $em->remove($theme);
       $em->flush();
       return $this->redirect($this->generateUrl('adminThemes'));
    }

   /**
     * @Route("/admin/theme/update", name="upTheme")
     * @Template()
     */
    public function updateAction()
    {
       return array("themes" => $this->tousLesThemes()); 
    }
    
    /**
     * @Route("/admin/theme/updatetheme/{id}", name="updateTheme")
     * @Template()
     */
    public function updatethemeAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       $themeSql = $em->getRepository('MetinetFacebookBundle:Theme')->findOneById($id);
       
       $form = $this->createForm(new ThemeUpdate(), $themeSql);
       $request = $this->getRequest(); 
             
       if ($request->getMethod() == "POST") { 
           
           
        $form->bindRequest($request);
            if ($form->isValid()) {
                
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($themeSql);
                $em->flush();
                
                return $this->redirect($this->generateUrl('adminThemes'));

        }
    }
       
       return array("form" => $form->createView(),
                    "theme" => $themeSql,
                    "idTheme" => $id); 

       }
    
    
     /**
     * @Route("theme/all", name="allTheme")
     * @Template()
     */
    public function allAction()
    {
       $nombreDeQuizzsDisponibles = array();
       foreach($this->tousLesThemes() as $value)
       {
           $nombreDeQuizzsDisponibles[] = count($value->getQuizzes());
       }
        
       return array("themes" => $this->tousLesThemes(),
                    "nombredeQuizz" => $nombreDeQuizzsDisponibles); 
    }
    
    /**
     * @Route("/admin/theme/updatepicture/{id}", name="updatePictureTheme")
     * @Template()
     */
    public function updatepictureAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       $themeSql = $em->getRepository('MetinetFacebookBundle:Theme')->findOneById($id);
       $theme = new Theme();
       
       $form = $this->createForm(new ThemeUpdatePicture(), $theme);
       $request = $this->getRequest(); 
             
       if ($request->getMethod() == "POST") { 
           
           
        $form->bindRequest($request);
            if ($form->isValid()) {
                
                $themeSql->setPicture($theme->getPicture());
                $nomTmpPicture = $theme->getPicture() ;
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($themeSql);
                $em->flush();
                
                $nouveauNomPicture = $theme->preUpload();
                $this->updateThemePicture($nomTmpPicture,$nouveauNomPicture,$em);
                
                return $this->redirect($this->generateUrl('adminThemes'));

        }
    }
       
       return array("form" => $form->createView(),
                    "theme" => $themeSql);  
    }
    
    protected function updateThemePicture($nomTmpPicture,$nouveauNomPicture,$em)
    {
       $themeUpdate = $em->getRepository('MetinetFacebookBundle:Theme')->findOneByPicture($nomTmpPicture);
       $themeUpdate->setPicture($nouveauNomPicture);
       $em->flush();
    }
    
    protected function tousLesThemes(){
        
        $repTheme = $this->getDoctrine()->getRepository('MetinetFacebookBundle:Theme'); 
        $allTheme = $repTheme->findAll();
        
        return $allTheme ;
    }
    
    
    
}

?>
