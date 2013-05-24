<?php

    namespace Metinet\Bundle\FacebookBundle\Form\Type\Quizz;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Doctrine\ORM\EntityRepository;
    
class QuizzUpdate extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('title', 'text',array('label' => 'Titre du quizz'))
        ->add('shortDesc', 'textarea',array('label' => 'Petite description du quizz'))
        ->add('longDesc', 'textarea',array('label' => 'Grande description du quizz'))
        ->add('winPoints','integer',array('label' => 'Nombre de points à gagner'))
        ->add('averageTime','integer',array('label' => 'Temps moyen du quizz'))
        ->add('txtWin1','textarea',array('label' => 'Texte bonne réponse 0 - 25 %'))
        ->add('txtWin2','textarea',array('label' => 'Texte bonne réponse 25 - 50 %'))
        ->add('txtWin3','textarea',array('label' => 'Texte bonne réponse 50 - 75 %'))
        ->add('txtWin4','textarea',array('label' => 'Texte bonne réponse 75 - 100 %'))
        ->add('shareWallTitle','text',array('label' => 'Titre pour la viralité'))
        ->add('shareWallDesc','text',array('label' => 'Description pour la viralité'))
        ->add('theme','entity', array(
                'class' => 'MetinetFacebookBundle:Theme',
                'property' => 'title'))
        ;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Metinet\Bundle\FacebookBundle\Entity\Quizz'
        ));
    }

    public function getName()
    {
        return 'update_quizz';
    }
}

?>
