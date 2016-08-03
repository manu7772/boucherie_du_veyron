<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace site\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class AdressFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        // if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
        //     $constraint = new UserPassword();
        // } else {
        //     // Symfony 2.1 support with the old constraint class
        //     $constraint = new OldUserPassword();
        // }

        // $this->buildUserForm($builder, $options);

        $builder
            // ->add('current_password', 'password', array(
            //     'label' => 'form.current_password',
            //     'translation_domain' => 'FOSUserBundle',
            //     'mapped' => false,
            //     'constraints' => $constraint,
            //     ))
            ->add('nom', 'text', array(
                'label'     => 'fields.nom',
                'required'  => false,
                'attr' => array(
                    'class' => 'input-sm form-full',
                    ),
                ))
            ->add('prenom', 'text', array(  
                'label'     => 'fields.prenom',
                'required'  => false,
                'attr' => array(
                    'class' => 'input-sm form-full',
                    ),
                ))
        ;
    }

    public function configureOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'site\UserBundle\Entity\User',
            'intention'  => 'profile',
        ));
    }

    public function getName()
    {
        return 'site_user_adress';
    }

}
?>