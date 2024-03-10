<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{
    /**
     * Homepage of profile
     *
     * @Route("/profile", name="profile")
     */
    public function profileAction()
    {
        return $this->render('profile/profile.html.twig');
    }

    /**
     * Updates of profile
     *
     * @Route("/profile/update/{id}", name="profile_update")
     */
    public function profileUpdateAction($id, Request $request, ObjectManager $manager)
    {
        // Gets customer
        $user = $manager->find(User::class, $id);

        // Creates form
        $form = $this->createForm(UserType::class, $user, ['roles' => $user->getRoles()]);
        $form->handleRequest($request);

        // Updates profile
        if ($form->isSubmitted() && $form->isValid()) {
          $manager->persist($user);
          $manager->flush();
          $this->addFlash('success', 'Votre profil a bien été mise à jour');
          return $this->redirectToRoute('profile');
        }

        $params = [
          'user' => $user,
          'userForm' => $form->createView()
        ];

        return $this->render('profile/form.html.twig', $params);
    }
}
