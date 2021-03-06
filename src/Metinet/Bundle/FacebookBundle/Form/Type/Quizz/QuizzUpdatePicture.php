<?php

    namespace Metinet\Bundle\FacebookBundle\Form\Type\Quizz;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Doctrine\ORM\EntityRepository;
    
class QuizzUpdatePicture extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('picture')
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
        return 'update_quizz_picture';
    }
}

?>
