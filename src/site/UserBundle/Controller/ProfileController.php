<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace site\UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as FEBsecurity;

use FOS\UserBundle\Controller\ProfileController as BaseController;

/**
 * Controller managing the user profile
 * @FEBsecurity("has_role('ROLE_USER')")
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends BaseController {

    /**
     * Show the user
     */
    public function showAction() {
        $data['user'] = $this->getUser();
        if (!is_object($data['user']) || !$data['user'] instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        return $this->render('FOSUserBundle:Profile:show.html.twig', $data);
    }

    /**
     * Edit the user
     */
    public function editAction(Request $request) {
        $data['user'] = $this->getUser();
        if (!is_object($data['user']) || !$data['user'] instanceof UserInterface) {
            throw new AccessDeniedException('This user "'.$data['user']->getUsername().'" does not have access to this section.');
        } else {

            /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
            $dispatcher = $this->get('event_dispatcher');

            $event = new GetResponseUserEvent($data['user'], $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
            $formFactory = $this->get('fos_user.profile.form.factory');

            $form = $formFactory->createForm();
            $form->setData($data['user']);

            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
                $userManager = $this->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                $image = $data['user']->getAvatar();
                if(is_object($image)) {
                    $infoForPersist = $image->getInfoForPersist();
                    // $this->container->get('aetools.aeDebug')->debugFile($infoForPersist);
                    if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
                        // Supression de l'image
                        $data['user']->setAvatar(null);
                    } else {
                        // Gestion de l'image
                        // $service = $this->container->get('aetools.aeServiceBaseEntity')->getEntityService($image);
                        // $service->checkAfterChange($image);
                    }
                }

                $userManager->updateUser($data['user']);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_profile_show');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($data['user'], $request, $response));

                return $response;
            }
            $data['form'] = $form->createView();
            return $this->render('FOSUserBundle:Profile:edit.html.twig', $data);
        }
    }
}
