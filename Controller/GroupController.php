<?php

namespace Puzzle\Admin\UserBundle\Controller;

use Puzzle\ConnectBundle\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\UserBundle\Form\Type\GroupCreateType;
use Puzzle\Admin\UserBundle\Form\Type\GroupUpdateType;

/**
 * @author AGNES Gnagne Cedric <cecenho55gmail.com>
 */
class GroupController extends Controller
{
	/***
	 * Show groups
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_USER_MANAGE')")
	 */
	public function listAction(Request $request, $current = null) {
	    $er = $this->getDoctrine()->getRepository(Group::class);
	    $currentGroup = $current ? $er->find($current) : null;
		$groups = $er->findBy([], ['name' => 'ASC']);
		
		return $this->render('PuzzleAdminUserBundle:Group:list.html.twig', [
		    'groups'  => $groups,
		    'currentGroup' => $currentGroup
		]);
	}
	
	/***
	 * Create group
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_USER_MANAGE')")
	 */
	public function createAction(Request $request)
	{
	    $group = new Group();
	    $form = $this->createForm(GroupCreateType::class, $group, [
	        'method' => 'POST',
	        'action' => $this->generateUrl('admin_user_group_create')
	    ]);
	    $form->handleRequest($request);
	    
	    if ($form->isSubmitted() === true && $form->isValid() === true) {
	        $em = $this->getDoctrine()->getManager();
	        $em->persist($group);
	        $em->flush();
	        
	        if ($request->isXmlHttpRequest() === true) {
	            return new JsonResponse(['status' => true]);
	        }
	        
	        return $this->redirectToRoute('admin_user_group_list');
	    }
	    
	    return $this->render('PuzzleAdminUserBundle:Group:create.html.twig', ['form' => $form->createView()]);
	}
	
	/***
	 * Show group
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_USER_MANAGE')")
	 */
	public function showAction(Request $request, Group $group){
	    $er = $this->getDoctrine()->getRepository(Group::class);
	    return $this->render('PuzzleAdminUserBundle:Group:show.html.twig', array(
	        'group'    => $group,
	        'groups'   => $er->findBy([], ['name' => 'ASC']),
	        'users'    => $group->getUsers()
	    ));
	}
	
	
	/***
	 * Update group
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_USER_MANAGE')")
	 */
	public function updateAction(Request $request, Group $group)
	{
	    $form = $this->createForm(GroupUpdateType::class, $group, [
	        'method' => 'POST',
	        'action' => $this->generateUrl('admin_user_group_update', ['id' => $group->getId()])
	    ]);
	    $form->handleRequest($request);
	    
	    if ($form->isSubmitted() === true && $form->isValid() === true) {
	        $em = $this->getDoctrine()->getManager();
	        $em->flush();
	        
	        if ($request->isXmlHttpRequest() === true) {
	            return new JsonResponse(['status' => true]);
	        }
	        
	        return $this->redirectToRoute('admin_user_group_list');
	    }
	    
	    return $this->render('PuzzleAdminUserBundle:Group:update.html.twig', [
	        'group'    => $group,
	        'form'     => $form->createView()
	    ]);
	}
	
	
	/**
	 * Delete a group
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @Security("has_role('ROLE_USER_MANAGE')")
	 */
	public function deleteAction(Request $request, Group $group) {
		$em = $this->getDoctrine()->getManager();
		$em->remove($group);
		$em->flush();
		
		if ($request->isXmlHttpRequest() === true) {
		    return new JsonResponse(['status' => true]);
		}
		
		return $this->redirectToRoute('admin_user_group_list');
	}
}
