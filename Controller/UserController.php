<?php
namespace Puzzle\Admin\UserBundle\Controller;

use Puzzle\ConnectBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Puzzle\ConnectBundle\Event\UserEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\UserBundle\Form\Type\UserCreateType;
use Puzzle\Admin\UserBundle\Form\Type\UserUpdateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Puzzle\Admin\UserBundle\Form\Type\ResettingPasswordType;
use Puzzle\ConnectBundle\Util\TokenGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Puzzle\Admin\UserBundle\Form\Type\UserSettingsType;
use Puzzle\Admin\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Puzzle\ConnectBundle\Entity\Group;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\UserEvents;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class UserController extends Controller
{
    use TargetPathTrait;
    
    /***
     * Show Users
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request) {
        $users = $this->getDoctrine()->getRepository(User::class)->customFindBy(null, null, []);
        return $this->render('PuzzleAdminUserBundle:User:list.html.twig', ['users' => $users]);
    }
    
    /***
     * Show Users
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
     */
    public function listForAutocompleteAction(Request $request) {
        $data =  $request->request->all();
        $criteria = $array = [];
        
        foreach ($data as $key => $value) {
            $criteria[] = [$key, $value];
        }
        
        $users = $this->getDoctrine()->getRepository(User::class)->customFindBy(null, null, $criteria);
        
        foreach ($users as $user) {
            $array[] = [
                'id'        => $user->getId(),
                'fullName'  => $user->getFirstName().' '. $user->getLastName(),
                'email'     => $user->getEmail()
            ];
        }
        
        return new JsonResponse($array);
    }
    
    /**
     * Show users list for Datatable
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
     */
    public function listForDatatableAction(Request $request)
    {
        $params = $request->request->all();
        $criteria = [];
        if (isset($params['search']['value']) && $params['search']['value']) {
            $search = $params['search']['value'];
            $criteria = [
                ['firstName', '%'.$search.'%', 'LIKE'],
                ['lastName', $search.'%', 'LIKE'],
                ['email', $search.'%', 'LIKE']
            ];
        }
        
        $limit = isset($params['length']) && $params['length'] ? (int) $params['length'] : null;
        $offset = isset($params['start']) && $params['start'] ? (int) $params['start'] : null;
        $array = [];
        
        $em = $this->getDoctrine()->getManager();
        $er = $em->getRepository(User::class);
        
        $users = $er->customFindBy(null, null, $criteria, ['firstName' => 'ASC'], $limit, $offset);
        foreach ($users as $user) {
            $array[] = [
                'id'                        => $user->getId(),
                'full_name'                 => $user->getFullName(),
                'email'                     => $user->getEmail(),
                'account_expires_at'        => $user->getAccountExpiresAt() ?? 'Non defini',
                'credentials_expires_at'    => $user->getAccountExpiresAt() ?? 'Non defini',
                'status'                    => $this->renderView("PuzzleAdminUserBundle:User:helper_checkbox_button.html.twig", ['value' => $user->isEnabled()]),
                'locked'                    => $this->renderView("PuzzleAdminUserBundle:User:helper_checkbox_button.html.twig", ['value' => $user->isLocked()]),
                'actions'                   => $this->renderView("PuzzleAdminUserBundle:User:helper_list_buttons.html.twig", ['user' => $user])
            ];
        }
        
        return new JsonResponse([
//             "draw"            => intval($params['draw']),
            "recordsTotal"    => count($users),
            "recordsFiltered" => intval(count($array)),
            "aaData"          => $array
        ]);
    }
    
    /***
     * Create user
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request){
        $user = new User();
        
        $modulesAvailables = explode(',', $this->getParameter('puzzle_admin.modules_availables'));
        $roles = [];
        
        foreach ($modulesAvailables as $moduleAvailable) {
            $rolesAvailables = $this->getParameter('admin_'.strtolower($moduleAvailable).'.roles');
            
            foreach ($rolesAvailables as $role) {
                $roles[$this->get('translator')->trans($role['description'], [], 'admin')] = $role['label'];
            }
        }
        
        $groupId = $request->get('group');
        
        $form = $this->createForm(UserCreateType::class, $user, [
            'method' => 'POST',
            'action' => $groupId ? $this->generateUrl('admin_user_create', ['group' => $groupId]) : 
                                   $this->generateUrl('admin_user_create')
        ]);
        
        $form->add('roles', ChoiceType::class, array(
            'translation_domain' => 'admin',
            'label' => 'user.label.user.roles',
            'label_attr' => ['class' => 'form-label'],
            'choices' => $roles,
            'attr' => [
                'class' => 'select'
            ],
            'multiple' => true,
            'required' => false
        ));
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.default_entity_manager');
            
            $user->setEnabled(false);
            $event = new UserEvent($user);
            $dispatcher->dispatch(UserEvents::USER_CREATING, $event);
            $plainPassword = $user->getPlainPassword();
            
            $em->persist($user);
            $em->flush();
            
            $event = new UserEvent($user, ['plainPassword' => $plainPassword]);
            $dispatcher->dispatch(UserEvents::USER_CREATED, $event);
            
            $groupId = $request->get('group');
            
            if ($groupId && $group = $em->getRepository(Group::class)->find($groupId)) {
                $group->addUser($user);
                $em->flush();
                
                return $this->redirect($this->generateUrl('admin_user_group_show', ['id' => $groupId]));
            }
            
            return $this->redirect($this->generateUrl('admin_user_show', ['id' => $user->getId()]));
        }
        
        return $this->render('PuzzleAdminUserBundle:User:create.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpKernel\Exception\NotFoundHttpException|\Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        /** @var User $user */
        if (!$user = $this->get('doctrine.orm.default_entity_manager')->find(User::class, $id)) {
            throw $this->createNotFoundException();
        }

        return $this->render('PuzzleAdminUserBundle:User:show.html.twig', ['user' => $user]);
    }
    
    /**
	 * @param Request $request
	 * @param int $id
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
	 */
	public function updateAction(Request $request, $id) {
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = $this->get('doctrine.orm.default_entity_manager');
		
		if (!$user = $em->find(User::class, $id)) {
			throw $this->createNotFoundException();
		}
		
		$modulesAvailables = explode(',', $this->getParameter('puzzle_admin.modules_availables'));
		$roles = [];
		
		foreach ($modulesAvailables as $moduleAvailable) {
		    $rolesAvailables = $this->getParameter('admin_'.strtolower($moduleAvailable).'.roles');
		    
		    foreach ($rolesAvailables as $role) {
		        $roles[$this->get('translator')->trans($role['description'], [], 'admin')] = $role['label'];
		    }
		}
		
		$form = $this->createForm(UserCreateType::class, $user, [
		    'method' => 'POST',
		    'action' => $this->generateUrl('admin_user_update', ['id' => $id])
		]);
		
		$form->add('roles', ChoiceType::class, array(
		    'translation_domain' => 'admin',
		    'label' => 'user.label.user.roles',
		    'label_attr' => ['class' => 'form-label'],
		    'choices' => $roles,
		    'attr' => [
		        'class' => 'select'
		    ],
		    'multiple' => true,
		    'required' => false
		));
		
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
		    /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		    $dispatcher = $this->get('event_dispatcher');
		    $dispatcher->dispatch(UserEvents::USER_UPDATING, new UserEvent($user));
		    $this->get('doctrine.orm.default_entity_manager')->flush();
		    
		    $event = new UserEvent($user, ['plainPassword' => $user->getPlainPassword()]);
		    $dispatcher->dispatch(UserEvents::USER_UPDATED, $event);
		    
		    return $this->redirect($this->generateUrl('admin_user_show', ['id' => $user->getId()]));
		}
		
		return $this->render('PuzzleAdminUserBundle:User:update.html.twig', [
		    'form' => $form->createView(), 
		    'user' => $user,
		    'modules' => $rolesAvailables
		]);
	}
	
	/**
	 * @param Request $request
	 * @param int $id
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
	 */
	public function lockAction(Request $request, $id) {
		return $this->lock($id, true);
	}
	
	/**
	 * @param Request $request
	 * @param int $id
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
	 */
	public function unlockAction(Request $request, $id) {
		return $this->lock($id, false);
	}
	
	private function lock($id, bool $locked) {
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = $this->get('doctrine.orm.default_entity_manager');
		
		if (!$user = $em->find(User::class, $id)) {
			return new JsonResponse(null, 404);
		}
		
		$user->setLocked($locked);
		$em->flush();
		
		return new JsonResponse(null, 200);
	}
	
	/**
	 * @param Request $request
	 * @param int $id
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @Security("has_role('ROLE_USER_MANAGE') or has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = $this->get('doctrine.orm.default_entity_manager');
		
		/** @var User user */
		if (!$user = $em->find(User::class, $id)) {
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse(null, 404);
			}
			
			throw $this->createNotFoundException();
		}
		
		$em->remove($user);
		$em->flush();
		
		if ($request->isXmlHttpRequest() === true) {
		    return new JsonResponse(['status' => true]);
		}
		
		return $this->redirect($this->generateUrl('admin_user_list'));
	}
	
    
    /**
     * Confirm
     *
     * @param Request $request
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmAction(Request $request, $token) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        /** @var \ApiBundle\Repository\UserRepository $er */
        $er = $em->getRepository(User::class);
        
        /** @var User $user */
        if (!$user = $er->findOneBy(['confirmationToken' => $token])) {
            throw $this->createNotFoundException();
        }
        
        $this->saveTargetPath($request->getSession(), 'main', $this->generateUrl('admin_user_change_password', [], UrlGenerator::ABSOLUTE_URL));
        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $em->flush();
        
        return $this->render('PuzzleAdminUserBundle:User:confirmed.html.twig', ['username' => (string) $user]);
    }
    
    /**
     * @param Request $request
     * @Security("has_role('ROLE_USER')")
     */
    public function profilAction(Request $request) {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return $this->render('PuzzleAdminUserBundle:User:show.html.twig', ['user' =>$currentUser]);
    }
    
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsAction(Request $request) {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $form = $this->createForm(UserSettingsType::class, $currentUser, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_user_settings')
        ]);
        
        $form->add('submit', SubmitType::class, ['label' => 'settings.submit', 'attr' => ['class' => 'btn btn-success mr5']]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->flush();
            
            return $this->redirect($this->generateUrl('admin_user_profil'));
        }
        
        return $this->render('PuzzleAdminUserBundle:User:settings.html.twig', ['form' => $form->createView()]);
    }
    
    /**
     * @param Request $request
     * @Security("is_granted('IS_AUTHENTICATED_FULLY') and has_role('ROLE_USER')")
     */
    public function changePasswordAction(Request $request) {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $currentUser, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_user_change_password')
        ]);
        
        $form->add('submit', SubmitType::class, ['label' => 'change_password.submit', 'attr' => ['class' => 'btn btn-success mr5']]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.default_entity_manager');
            
            $currentUser->setPasswordRequestedAt(null);
            $currentUser->setConfirmationToken(null);
            $currentUser->setPasswordChanged(true);
            $em->flush();
            
            if ($uri = $request->getSession()->get('change_password.on_success.redirect_to')) {
                $request->getSession()->remove('change_password.on_success.redirect_to');
            } else {
                $uri = $this->generateUrl('admin_user_profil');
            }
            
            return $this->redirect($uri);
        }
        
        return $this->render('PuzzleAdminUserBundle:User:change_password.html.twig', ['form' => $form->createView()]);
    }
    
    /**
     * Resetting reset user password: show form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resettingRequestAction(Request $request) {
        return $this->render('PuzzleAdminUserBundle:User:resetting_request.html.twig');
    }
    
    /**
     * Resetting reset user password: send email
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resettingSendEmailAction(Request $request) {
        if ($username = $request->request->get('username')) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.default_entity_manager');
            /** @var \ApiBundle\Repository\UserRepository $er */
            $er = $em->getRepository(User::class);
            /** @var User $user */
            $user = $er->loadUserByUsername($username);
            
            if (null !== $user && false === $user->isPasswordRequestNonExpired($this->getParameter('admin.resetting.retry_ttl'))) {
                if (null === $user->getConfirmationToken()) {
                    $user->setConfirmationToken(TokenGenerator::generate(12));
                }
                
                /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
                $translator = $this->get('translator');
                $subject = $translator->trans('resetting.email.subject', [], 'admin');
                $body = $this->renderView('PuzzleUserBundle:User:resetting_email.txt.twig', [
                    'username' => $username,
                    'confirmationUrl' => $this->generateUrl('admin_user_resetting_reset', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);
                $this->sendEmail($this->getParameter('admin.resetting.address'), $user->getEmail(), $subject, $body);
                
                $user->setPasswordRequestedAt(new \DateTime());
                $em->flush();
            }
        }
        
        return new RedirectResponse($this->generateUrl('admin_user_resetting_check_email', ['username' => $username]));
    }
    
    /**
     * Resetting reset user password: check email
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resettingCheckEmailAction(Request $request) {
        if (!$request->query->get('username')) {
            return new RedirectResponse($this->generateUrl('admin_user_resetting_request'));
        }
        
        return $this->render('PuzzleAdminUserBundle:User:resetting_check_email.html.twig', [
            'tokenLifetime' => ceil($this->getParameter('admin.resetting.retry_ttl') / 3600)
        ]);
    }
    
    /**
     * Resetting reset user password: show form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resettingResetAction(Request $request, $token) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        /** @var \ApiBundle\Repository\UserRepository $er */
        $er = $em->getRepository(User::class);
        
        /** @var User $user */
        if (!$user = $er->findOneBy(['confirmationToken' => $token])) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(ResettingPasswordType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_user_resetting_reset', ['token' => $token])
        ]);
        
        $form->add('submit', SubmitType::class, ['label' => 'resetting.reset.submit', 'attr' => ['class' => 'btn btn-success btn-block']]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPasswordRequestedAt(null);
            $user->setConfirmationToken(null);
            $user->setPasswordChanged(true);
            $user->setEnabled(true);
            $em->flush();
            
            return $this->redirect($this->generateUrl('puzzle_security_login'));
        }
        
        return $this->render('PuzzleAdminUserBundle:User:resetting_reset.html.twig', ['form' => $form->createView()]);
    }
    
    private function sendEmail($from, $to, string $subject, string $body) {
        $message = \Swift_Message::newInstance()
        ->setFrom($from)
        ->setTo($to)
        ->setSubject($subject)
        ->setBody($body);
        
        $this->get('mailer')->send($message);
    }
}
