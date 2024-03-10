<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\CallbackTransformer;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = $options['roles'];
        $action = $options['action'] ?? '';

        $builder
            ->add('firstname', TextType::class, ['label' => 'Prénom'])
            ->add('lastname', TextType::class, ['label' => 'Nom'])
            ->add('username', TextType::class, ['label' => 'Nom d\'utilisateur'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('password', PasswordType::class, ['label' => 'Mot de passe'])
            ->add('address', TextType::class, ['label' => 'Adresse'])
            ->add('district', TextType::class, ['label' => 'Région'])
            ->add('postalCode', IntegerType::class, ['label' => 'Code postal'])
            ->add('city', TextType::class, ['label' => 'Ville'])
            ->add('country', TextType::class, ['label' => 'Pays'])
            ->add('phone', TextType::class, ['label' => 'Téléphone'])
        ;

        if (in_array('ROLE_ADMIN', $roles)) {
          $builder
              ->add('enabled', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['Activé' => 1, 'En cours de validation' => 0],
              ])
              ->add('location', TextType::class, ['label' => 'Localisation'])
              ->add('ip', TextType::class, ['label' => 'IP'])
          ;
          /*
          Role can be added only when a user is created
          For the moment, a simple administrator will not be able to change the role of a user.
          If an admin becomes a customer, he must have an account in our application.
          In the future, a super administrator can be created to test by being both admin and client,
          a fictitious account associated.
          */
          if ($action === 'add') {
            $builder->add('roles',  ChoiceType::class, [
              'label' => 'Rôles',
              'choices' => ['Utilisateur' => 'ROLE_USER', 'Administrateur' => 'ROLE_ADMIN']
            ]);
            $builder
                ->get('roles')
                ->addModelTransformer(new CallbackTransformer(
                  // Transform the array to a string
                  function ($rolesArray) {
                     return count($rolesArray) ? $rolesArray[0] : null;
                  },
                  // Transform the string back to an array
                  function ($rolesString) {
                     return [$rolesString];
                  }
            ));
          }
        }

        $builder->add('save', SubmitType::class, [
          'label' => 'Valider',
          'attr' => ['class' => 'btn waves-effect waves-light'],
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'roles' => ['ROLE_USER'],
        ]);
    }
}
