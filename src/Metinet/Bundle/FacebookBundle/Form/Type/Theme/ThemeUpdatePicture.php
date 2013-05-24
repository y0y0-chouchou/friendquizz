<?php

    namespace Metinet\Bundle\FacebookBundle\Form\Type\Theme;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Doctrine\ORM\EntityRepository;
    
class ThemeUpdatePicture extends AbstractType
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
            'data_class' => 'Metinet\Bundle\FacebookBundle\Entity\Theme'
        ));
    }

    public function getName()
    {
        return 'update_theme_picture';
    }
}

?>
