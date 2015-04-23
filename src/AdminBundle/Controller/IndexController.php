<?php

namespace AdminBundle\Controller;

//entiry classes
use MainBundle\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;

class IndexController extends Controller
{
    public function indexAction()
    {

    	$s_username	= '';
		$s_password	= '';
    	$session 	= $this->getRequest()->getSession();
        $request 	= $this->get('request');
        if ($request->getMethod() == 'POST') {

        	$s_username	= trim($request->request->get('txt_username'));
			$s_password	= trim($request->request->get('txt_password'));

			$user = new User();
			$user->setUsername($s_username);

			if($s_password != '') {
				$s_plain_password 	= $s_password;
				$encoder 			= $this->container->get('security.password_encoder');
				$s_password 		= $encoder->encodePassword($user, $s_plain_password);
			}
			
			$user->setPassword($s_password);

			$a_error_list   = $this->get('validator')->validate($user);
			$s_error_msg    = "";
			if (count($a_error_list) > 0) {
				foreach ($a_error_list as $err) {
					$s_error_msg.= $err->getMessage();
					break;
				}
			}

			if($s_error_msg != '') {
				return $this->render('AdminBundle:Index:index.html.twig',array('s_error' => $s_error_msg,'s_username' => $s_username));
			} else {
				$a_parameters = array('username' 	=> $s_username,
								  	  'password'	=> $s_password);

				$doctrine 	= $this->getDoctrine()->getManager();
				$user 		= $doctrine->getRepository('MainBundle\Entity\User')->findOneBy($a_parameters);

				if($user) {

					// $s_expiration_date 	= date('Y-m-d',strtotime($user->getExpirationDate()->format('Y-m-d')));
					// $s_current_date 	= date('Y-m-d');

					// if($s_expiration_date != '' && $s_expiration_date <= $s_current_date) {

					// 	return $this->render('AdminBundle:Index:index.html.twig',array('s_error' => 'Your account is expired','s_username' => $s_username));

					// } else {

						//clear previous session
						$session->clear();

						// create the session
						$session = new Session();
						$session->set('id',$user->getId());
						$session->set('first_name',$user->getFirstName());
						$session->set('last_name',$user->getLastName());

						return $this->redirectToRoute('therapeutic_area_user');				
					// }	
				} else {
					return $this->render('AdminBundle:Index:index.html.twig',array('s_error' => 'Invalid Credentials','s_username' => $s_username));
				}
			}
        }

        if(!$session->has('id')) {
			return $this->render('AdminBundle:Index:index.html.twig',array('s_error' => '','s_username' => $s_username));
		} else {
			return $this->redirectToRoute('therapeutic_area_user');
		}
    }

    public function logoutAction()
    {
    	//clear previous session
		$session = $this->getRequest()->getSession();
		$session->clear();
		return $this->redirectToRoute('admin');
    }
}
