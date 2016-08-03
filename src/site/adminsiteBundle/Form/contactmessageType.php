<?php

namespace site\adminsiteBundle\Form;

use Labo\Bundle\AdminBundle\Form\baseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Transformer
use Symfony\Component\Form\CallbackTransformer;
// User
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as SecurityContext;
// Paramétrage de formulaire
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class contactmessageType extends baseType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        // ajout de action si défini
        $this->initBuilder($builder);
        // user
        if(is_object($this->controller->getUser())) {
            $user['nom'] = $this->controller->getUser()->getNom();
            $user['prenom'] = $this->controller->getUser()->getPrenom();
            $user['email'] = $this->controller->getUser()->getEmail();
            $user['telephone'] = $this->controller->getUser()->getTelephone();
            $disabled = true;
        } else {
            $user['nom'] = null;
            $user['prenom'] = null;
            $user['email'] = null;
            $user['telephone'] = null;
            $disabled = false;
        }
        // Builder…
        $builder
            ->add('nom', 'text', array(
                'data' => $user['nom'],
                'label' => 'form.nom',
                'translation_domain' => 'messages',
                'required' => false,
                'disabled' => $disabled,
                'attr' => array(
                    'placeholder' => 'form.nom',
                    )
                ))
            ->add('prenom', 'text', array(
                'data' => $user['prenom'],
                'label' => 'form.prenom',
                'translation_domain' => 'messages',
                'required' => false,
                'disabled' => $disabled,
                'attr' => array(
                    'placeholder' => 'form.prenom',
                    )
                ))
            ->add('email', 'email', array(
                'data' => $user['email'],
                'label' => 'form.email',
                'translation_domain' => 'messages',
                'required' => true,
                'disabled' => $disabled,
                'attr' => array(
                    'placeholder' => 'form.email',
                    )
                ))
            ->add('telephone', 'text', array(
                'data' => $user['telephone'],
                'label' => 'form.telephone',
                'translation_domain' => 'messages',
                'required' => false,
                // 'disabled' => $disabled,
                'attr' => array(
                    'placeholder' => 'form.telephone',
                    )
                ))
            ->add('objet', 'text', array(
                'label' => 'form.objet',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'form.objet',
                    )
                ))
            ->add('message', 'textarea', array(
                'label' => 'form.message',
                'translation_domain' => 'messages',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'form.message',
                    'class' => 'message',
                    'rows' => 8,
                    )
                ))
            // ->add('creation')
            // ->add('ip')
        ;
        // ajoute les valeurs hidden, passés en paramètre
        $this->addHiddenValues($builder, true, 'form.envoyer');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'site\adminsiteBundle\Entity\message'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'site_adminsitebundle_contactmessage';
    }
}
