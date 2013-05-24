<?php

    namespace Metinet\Bundle\FacebookBundle\Form\Type\Answer;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Doctrine\ORM\EntityRepository;
    
class AnswerAdd extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('title', 'text',array('label' => 'Titre de la réponse'))
        ->add('isCorrect','checkbox', array(
                'label'     => 'Réponse correcte ?',
                'required'  => false,
                                            ))
        ->add('question','entity', array(
                'class' => 'MetinetFacebookBundle:Question',
                'property' => 'title'))
        ;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Metinet\Bundle\FacebookBundle\Entity\Answer'
        ));
    }

    public function getName()
    {
        return 'add_answer';
    }
    
}

?>
