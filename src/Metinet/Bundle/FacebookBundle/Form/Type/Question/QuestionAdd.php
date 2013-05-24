<?php

    namespace Metinet\Bundle\FacebookBundle\Form\Type\Question;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Doctrine\ORM\EntityRepository;
    
class QuestionAdd extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('title', 'text',array('label' => 'Titre de la question'))
        ->add('picture') /* pas besoin de preciser le type */
        ->add('quizz','entity', array(
                'class' => 'MetinetFacebookBundle:Quizz',
                'property' => 'title'))
        ;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Metinet\Bundle\FacebookBundle\Entity\Question'
        ));
    }

    public function getName()
    {
        return 'add_question';
    }
    
}

?>
