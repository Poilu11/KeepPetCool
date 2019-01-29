<?php

namespace App\Controller;

use DateTime;
use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Util\CoordResolver;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PresentationRepository;
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

        // Si utilisateur bloqué, redirection vers la page logout
        if(!$user->getIsActive())
        {
            return $this->redirectToRoute('logout');
        }

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
    public function signup(Request $request, UserPasswordEncoderInterface $encoder, CoordResolver $coordResolv)
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

            // preg_match renvoit 1 si il y a correspondance entre la regex et la valeur de test. Ainsi la multiplication du retour des deux test sera forcément égal à 1 si les deux tests sont ok. Réciproquement, si l'une des valeur ne correspond pas à la regex, alors la multiplication des deux tests sera différente de 1.
            $AreCoordValid = preg_match('/(\d+\.?\d*)/', $user->getLongitude());
            $AreCoordValid *= preg_match('/(\d+\.?\d*)/', $user->getLatitude());

            if($AreCoordValid !== 1)
            {
                $adress = str_replace(' ', '+', $user->getAddress());
                $adress .= '+'.str_replace(' ', '+', $user->getZipCode());
                $adress .= '+'.str_replace(' ', '+', $user->getCity());

                $coords = $coordResolv->getCoords($adress);

                if($coords[0] === 'NC' || $coords[1] === 'NC')
                {
                    $this->addFlash('danger', 'Impossible de géolocaliser votre adresse. Celle-ci étant nécessaire à l\'utilisation de nos service, nous vous conseillons d\'éditer vos informations personnelles avec une adresse valide.');

                    $user->setLongitude(-147.349);
                    $user->setLatitude(64.751);
                }
                else
                {
                    $user->setLatitude(floatval($coords[0]));
                    $user->setLongitude(floatval($coords[1]));
                }
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
    public function edit($id, User $user, Request $request, UserPasswordEncoderInterface $encoder, CoordResolver $coordResolv)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Je récupere les informations de l'user connecté
        $currentUser = $this->getUser();
        $oldAdress = $currentUser->getAddress();
        $oldZipCode = $currentUser->getZipCode();
        $oldCity = $currentUser->getCity();
        $oldLatitude = $currentUser->getLatitude();
        $oldLongitude = $currentUser->getLongitude();
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

                    // Mise en place condition pour ne pas supprimer
                    // l'avatar par défaut
                    if($oldFile !== 'default-avatar.png')
                    {
                        unlink(
                            $this->getParameter('avatar_directory') .'/'.$oldFile
                        );
                    }

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

            $isAdressModified = false;
            $isAdressModified = ($oldAdress != $user->getAddress()) ? true : $isAdressModified;
            $isAdressModified = ($oldZipCode != $user->getZipCode()) ? true : $isAdressModified;
            $isAdressModified = ($oldCity != $user->getCity()) ? true : $isAdressModified;

            $areCoordModified = false;
            $areCoordModified = ($oldLatitude != $user->getLatitude()) ? true : $areCoordModified;
            $areCoordModified = ($oldLongitude != $user->getLongitude()) ? true : $areCoordModified;

            // preg_match renvoit 1 si il y a correspondance entre la regex et la valeur de test. Ainsi la multiplication du retour des deux test sera forcément égal à 1 si les deux tests sont ok. Réciproquement, si l'une des valeur ne correspond pas à la regex, alors la multiplication des deux tests sera différente de 1.
            $areCoordValid = preg_match('/(\d+\.?\d*)/', $user->getLongitude());
            $areCoordValid *= preg_match('/(\d+\.?\d*)/', $user->getLatitude());

            if( ($isAdressModified && !$areCoordModified) || $areCoordValid !== 1)
            {   
                $adress = str_replace(' ', '+', $user->getAddress());
                $adress .= '+'.str_replace(' ', '+', $user->getZipCode());
                $adress .= '+'.str_replace(' ', '+', $user->getCity());

                $coords = $coordResolv->getCoords($adress);

                if($coords[0] === 'NC' || $coords[1] === 'NC')
                {
                    $this->addFlash('danger', 'Impossible de géolocaliser votre adresse. Celle-ci étant nécessaire à l\'utilisation de nos service, nous vous conseillons d\'éditer vos informations personnelles avec une adresse valide.');

                    $user->setLongitude(-147.349);
                    $user->setLatitude(64.751);
                }
                else
                {
                    $user->setLatitude(floatval($coords[0]));
                    $user->setLongitude(floatval($coords[1]));
                }
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
    public function disable(User $user, PresentationRepository $presentationRepository, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit admin ou modo
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        // Je récupere les informations de l'user connecté
        $currentUser = $this->getUser();

        // Je vérifie que le User n'ait pas un statut autre que admin ou modo
        if($currentUser->getRole()->getCode() !== 'ROLE_ADMIN')
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à désactiver des comptes !'
            );

            return $this->redirecToRoute('dashboard');
        }

        if($user->getRole()->getCode() === 'ROLE_ADMIN')
        {
            $this->addFlash(
                'danger',
                'Un compte administrateur ne peut pas être désactivé !'
            );

            return $this->redirectToRoute('status');
        }

        if($user->getIsActive())
        {
            $user->setIsActive(false);
            $presentation = $presentationRepository->findOneBy(['user' => $user]);

            if(isset($presentation))
            {
                $presentation->setIsActive(false);
            }
            
            $em->flush();

            $this->addFlash(
                'success',
                'Utilisateur désactivé avec succès'
            );

            return $this->redirectToRoute('status');
        }
        else
        {
            $user->setIsActive(true);
            $presentation = $presentationRepository->findOneBy(['user' => $user]);

            if(isset($presentation))
            {
                $presentation->setIsActive(true);
            }
            
            $em->flush();

            $this->addFlash(
                'success',
                'Utilisateur activé avec succès'
            );

            return $this->redirectToRoute('status');
        }

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

            if(!isset($roleId) || empty($roleId))
            {
                $this->addFlash(
                    'danger',
                    'Informations manquantes !'
                );
    
                return $this->redirectToRoute('status');
            }

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

    /**
     * @Route("/user/validate/{id}", name="user_validate", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function validate(User $user, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('ROLE_MODO');

        if(!$user->getIsValidated())
        {
            $user->setIsValidated(true);

            $em->flush();

            $this->addFlash(
                'success',
                'Validation de l\'utilisateur ' . $user->getUsername() . ' validée avec succès'
            );
        }
        else
        {
            $user->setIsValidated(false);

            $em->flush();

            $this->addFlash(
                'success',
                'Dévalidation de l\'utilisateur ' . $user->getUsername() . ' validée avec succès'
            );
        }

        return $this->redirectToRoute('status');
    }

}
