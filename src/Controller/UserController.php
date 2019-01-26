<?php

namespace App\Controller;

use DateTime;
use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard", methods={"GET"})
     */
    public function dashboard(CommentRepository $commentRepository, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit connecté
         $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupération des informations de l'utilisateur
        // actuellement connecté
        $user = $this->getUser();

        // Gestion de la date de dernière connexion
        $user->setConnectedAt(new DateTime());
        $em->flush();

        // Récupération des commentaires à valider
        $commentsToValidate = $commentRepository->findBy(['petsitter' => $user, 'isValidated' => false, 'isDisplayed' =>true], ['createdAt' => 'DESC']);

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'comments' => $commentsToValidate
        ]);
    }

    /**
     * @Route("/signup", name="signup", methods={"GET", "POST"})
     */
    public function signup(Request $request, UserPasswordEncoderInterface $encoder)
    {
        // Si connecté,
        // On redirige sur la page dashboard
        $currentUser = $this->getUser();
        if(isset($currentUser) || !empty($currentUser))
        {
            $this->addFlash(
                'danger',
                'Vous êtes déjà connecté !'
            );
            return $this->redirectToRoute('dashboard');
        }

        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $plainPassword));

            // DEBUT Gestion de l'avatar
            $file = $user->getAvatar();

            if(!is_null($file)){
                
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    
                $file->move(
                        $this->getParameter('avatar_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                dump($e);
                }
               
                $user->setAvatar($fileName);
            }
            else
            {
                $user->setAvatar('default-avatar.png');
            }
            // FIN Gestion de l'avatar

            //$user->setLongitude(4.079306);
            // $user->setLatitude(4.079306);
            
            $defaultRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['code' => 'ROLE_USER']);
            $user->setRole($defaultRole);

            // Si longitude ou latitude vide(s)
            if(empty($user->getLongitude()) || empty($user->getLatitude()))
            {
                $user->setLongitude(-147.349);
                $user->setLatitude(64.751);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre inscription est validée ! Vous pouvez dès maintenant vous connecter'
            );
            return $this->redirectToRoute('login');
        }

        return $this->render('user/signup.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/profile/{id}/edit", name="profile_edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit($id, User $user, Request $request, UserPasswordEncoderInterface $encoder)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Je récupere les informations de l'user connecté
        $currentUser = $this->getUser();

        // Si les données de l'user connecté sont différentes des données de l'user qu'on cherche à éditer, on éjecte et on redirige vers dashboard
        if($currentUser->getId() !== $user->getId())
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à modifier les informations d\'un tiers !'
            );

            return $this->redirectToRoute('dashboard');
        }

        // Je récupère l'ancien avatar
        $oldFile = $user->getAvatar();
        // dump($oldFile);
        if(!empty($oldFile)) {
            // dd(new File($this->getParameter('avatar_directory').'/'.$oldFile));
            $user->setAvatar(
                new File($this->getParameter('avatar_directory').'/'.$oldFile)
            );
        }

        // On récupère le mot de passe actuel
        $oldPassword = $user->getPassword();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {


            // Si je souhaite setté un nouvel avatar pour un user existant
            if(!is_null($user->getAvatar())){
                $file = $user->getAvatar();
            
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('avatar_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    dump($e);
                }
                
                // Je stocke le nouveau nom de fichier
                $user->setAvatar($fileName);
                // Si je remplace mon ancien avatar par un nouveau, je teste dans un premier temps s'il y en avait déjà un à supprimer ;)
                if(!empty($oldFile)){
                    // Fonction native a php qui supprime des fichiers
                    unlink(
                        $this->getParameter('avatar_directory') .'/'.$oldFile
                    );
                }
            } else { // Sinon, on garde l'ancienne valeur que j'avais deja en BDD
                $user->setAvatar($oldFile);//ancien nom de fichier
            }


            if(empty($user->getPassword()))
            {
                // En l'absence de nouveau mot de passe,
                // reprise de l'ancien mot de passe encodé
                $encodedPassword = $oldPassword;
            }
            else
            {
                // On récupère le mot de passe en clair
                $plainPassword = $user->getPassword();

                // On ajoute une condition pour vérifier la longueur du mot de passe
                // car la constraint Length() ne fonctionne pas de manière optimale
                // sans la contrainte NotBlank()
                $countWordsPlainPassword = strlen($plainPassword);
                if($countWordsPlainPassword >= 4 && $countWordsPlainPassword <= 12)
                {
                    // Encodage du mot de passe modifié
                    $encodedPassword = $encoder->encodePassword($user, $plainPassword);
                }
                else
                {
                    $this->addFlash(
                        'danger',
                        'Modification du nouveau mot de passe non prise en compte : doit contenir 4 et 12 caractères.'
                    );
                    // TRES IMPORTANT
                    // On récupère alors l'ancien mot de passe encodé
                    // qu'on réenregistre pour le user
                    $encodedPassword = $oldPassword;
                    $user->setPassword($encodedPassword);
                    $this->getDoctrine()->getManager()->flush();
                    // Si je ne ré-enregistre pas l'ancien (actuel) mot de passe en BDD
                    // le user est alors déconnecté, et la redirection ci-dessous ne fonctionne donc pas => Le user est redirigé vers la page de connexion
                    // De plus, la constraint Length() dans le UserType ne semble pas
                    // fonctionner de manière optimale sans le Blank()
                    return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
                }
                
            }
            
            $user->setPassword($encodedPassword);

            // Si longitude ou latitude vide(s)
            if(is_null($user->getLongitude()) || is_null($user->getLatitude()))
            {
                $user->setLongitude(-147.349);
                $user->setLatitude(64.751);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                'success',
                'Les modifications de vos informations personnelles ont bien été prises en compte'
            );
            
            return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{id}/disable", name="profile_disable", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function disable(User $user, Request $request)
    {
        // On vérifie que l'utilisateur soit admin ou modo
        $this->denyAccessUnlessGranted(['ROLE_ADMIN','ROLE_MODO']);

        // Je récupere les informations de l'user connecté
        $currentUser = $this->getUser();

        if($currentUser->getRole()->getCode() == 'ROLE_ADMIN' || $currentUser->getRole()->getCode() == 'ROLE_MODO')
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à désactiver le compte de ce type d\'utilisateur'
            );

            $this->redirecToRoute('dashboard');
        }

        $this->redirectToRoute('home_page');
    }

    /**
     * @Route("/status", name="status", methods={"GET", "POST"})
     */
    public function status(Request $request, RoleRepository $roleRepository, UserRepository $userRepository)
    {
        if(!empty($_POST))
        {
            // On récupère les informations du formulaire
            $userId = $request->request->get('user');
            $roleId = $request->request->get('role');


            $user = $userRepository->find($userId);
            $role = $roleRepository->find($roleId);

            // On bloque la possibilité de pouvoir modifier le rôle d'un administrateur
            if($user->getRole()->getCode() === 'ROLE_ADMIN')
            {
                $this->addFlash(
                    'danger',
                    'Vous ne pouvez pas modifier le rôle d\'un administrateur !'
                );
    
                return $this->redirectToRoute('status');
            }

            $user->setRole($role);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash(
                'success',
                'La modification du rôle de l\'utilisateur ' . $user->getUsername() . ' a bien été prise en compte !'
            );

            return $this->redirectToRoute('status');

        }

        // On vérifie que l'utilisateur soit connecté
        // et qu'il est un rôle Administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $roles = $roleRepository->findAll();

        $users = $userRepository->findAll();


        return $this->render('user/status.html.twig', [
            'roles' => $roles,
            'users' => $users
        ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/avatar/{id}/delete", name="avatar_delete", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function avatarDelete(User $user)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Je récupere les informations de l'user connecté
        $currentUser = $this->getUser();

        // Si les données de l'user connecté sont différentes des données de l'user qu'on cherche à éditer, on éjecte et on redirige vers dashboard
        if($currentUser->getId() !== $user->getId())
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à supprimer l\'avatar d\'un tiers !'
            );

            return $this->redirectToRoute('dashboard');
        }

        // Je récupère l'ancien avatar
        $oldFile = $user->getAvatar();
        
        // Si le user a déjà l'avatar par défaut,
        // on ne supprime pas l'avatar
        if($oldFile !== 'default-avatar.png')
        {
            // Je supprime l'ancien avatar
            unlink(
                $this->getParameter('avatar_directory') .'/'.$oldFile
            );

            // Je re-sétte l'avatar par défaut
            $user->setAvatar('default-avatar.png');
            $this->getDoctrine()->getManager()->flush();
        }

        // Message confirmation suppression
        $this->addFlash(
            'success',
            'Suppression de l\'avatar correctement effectuée'
        );

        return $this->redirectToRoute('dashboard');
    }

}
