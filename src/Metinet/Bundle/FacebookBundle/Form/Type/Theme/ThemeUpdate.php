<?php

    namespace Metinet\Bundle\FacebookBundle\Form\Type\Theme;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Doctrine\ORM\EntityRepository;
    
class ThemeUpdate extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('title', 'text',array('label' => 'Titre du thème'))
        ->add('shortDesc', 'textarea',array('label' => 'Petite description du thème'))
        ->add('longDesc', 'textarea',array('label' => 'Longue description du thème'))
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
        return 'update_theme';
    }
}

?>
