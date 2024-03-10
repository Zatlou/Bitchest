<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Account;

use App\Form\UserType;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Homepage of users administration
     *
     * @Route("/admin/users", name="admin_users")
     */
    public function adminUsersAction()
    {
        // Gets users
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();

        $params = [
          'users' => $users
        ];

        return $this->render('admin/users/users.html.twig', $params);
    }

    /**
     * Add an user
     *
     * @Route("/admin/user/add", name="admin_user_add")
     */
     public function adminUserAddAction(Request $request, ObjectManager $manager)
     {
        // Creates new user
        $user = new User();

        // Creates form
        $form = $this->createForm(UserType::class, $user, ['roles' => ['ROLE_ADMIN'], 'action' => 'add']);
        $form->handleRequest($request);

        // Saves user
        if ($form->isSubmitted() && $form->isValid()) {

          // Encodes user's password
          $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

          // Only a customer can create an account
          if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            // Creates new account
            $account = new Account();
            $account->setUserId($user)
                    ->setCredit(400); // Customer bonus every time an account is created
            $manager->persist($account);
          }

          $manager->persist($user);
          $manager->flush();
          $this->addFlash('success', 'L\'utilisateur a bien été crée');

          return $this->redirectToRoute('admin_users');

        }

        $params = [
          'title' => 'Ajouter un utilisateur',
          'user' => $user,
          'userForm' => $form->createView()
        ];

        return $this->render('admin/users/form.html.twig', $params);
     }

    /**
     * Updates an user
     *
     * @Route("/admin/user/update/{id}", name="admin_user_update")
     */
    public function adminUserUpdateAction($id, Request $request, ObjectManager $manager)
    {
        // Gets user
        $user = $manager->find(User::class, $id);

        // Creates form
        $form = $this->createForm(UserType::class, $user, ['roles' => $user->getRoles()]);
        $form->handleRequest($request);

        // Updates user
        if ($form->isSubmitted() && $form->isValid()) {

          // Encodes user's password
          $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

          $manager->persist($user);
          $manager->flush();
          $this->addFlash('success', 'L\'utilisateur a bien été mise à jour');
          return $this->redirectToRoute('admin_users');

        }

        $params = [
          'title' => 'Mettre à jour',
          'user' => $user,
          'userForm' => $form->createView()
        ];

        return $this->render('admin/users/form.html.twig', $params);
    }

    /**
    * Deletes an user
    *
    * @Route("/admin/user/delete/{id}/", name="admin_user_delete")
    */
    public function adminUserDeleteAction($id, Request $request, ObjectManager $manager){

      // Gets user
      $user = $manager->find(User::class, $id);

      // Delete user
      $manager->remove($user);
      $manager->flush();

      $this->addFlash('success', 'L\'utilisateur a bien été supprimé');

      return $this -> redirectToRoute('admin_users');
    }
}
