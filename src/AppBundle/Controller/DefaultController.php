<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/profile/friends/add", name="friends_add")
     */
    public function addFriendsAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('username', TextType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $friend = $em->getRepository('AppBundle:User')
                ->findOneBy(array('username' => $data['username']));
            if ($friend) {
                $user = $this->getUser();
                if ($friend == $user)
                    $this->addFlash('warning','Tu ne peux pas t\'ajouter en amis');
                else {
                    $user->addMyFriend($friend);
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash(
                        'notice',
                        $friend->getUsername() . ' est maintenant ton amis !'
                    );
                }
                return $this->redirectToRoute('fos_user_profile_show');
            } else
                $this->addFlash('warning', $data['username'] . ' est introuvable');
        }

        // replace this example code with whatever you need
        return $this->render('default/addFriends.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/profile/friends/remove/{id}", name="friends_rm")
     */
    public function removeFriendsAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $friend = $em->getRepository('AppBundle:User')->find($id);
        if ($friend) {
            $user = $this->getUser();
            $user->removeMyFriend($friend);
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice',$friend->getUsername() . ' n\'est plus ton amis !');
        } else
            $this->addFlash('warning','Ton amis est introuvable');

        return $this->redirectToRoute('fos_user_profile_show');
    }
}
