<?php

namespace App\Controller;

use App\Util\Mailer;
use App\Entity\Message;
use App\Repository\UserRepository;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/message")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/new", name="message_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, UserRepository $userRepository)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupère les infos du user connecté
        $currentUser = $this->getUser();

        // Si formulaire POST soumis
        if(!empty($_POST))
        {
            $message = new Message();

            // Récupération des valeurs du formulaire New
            $userFromId = $request->request->get('userFrom');
            $userToId = $request->request->get('userTo');
            $messageObject = $request->request->get('messageObject');
            $messageBody = $request->request->get('messageBody');

            // dd($_POST);

            if(empty($userFromId) || empty($userToId) || empty($messageObject) || empty($messageBody))
            {
                $this->addFlash(
                    'danger',
                    'Informations manquantes'
                );

                return $this->redirectToRoute('message_new', ['userFrom' => $userFromId, 'userTo' => $userToId]);
            }

            // Je récupère l'objet User (pour from et to)
            // avec l'id récupéré dans le formulaire
            $userFrom = $userRepository->find($userFromId);
            $userTo = $userRepository->find($userToId);

            $message->setObject($messageObject);
            $message->setBody($messageBody);
            $message->setUserFrom($userFrom);
            $message->setUserTo($userTo);

            // dd($message);

            $em->persist($message);
            $em->flush();

            // Début traitement envoi email au destinataire

                // https://github.com/PHPMailer/PHPMailer
                // https://forum.alsacreations.com/topic-20-79170-1-Probleme-avec-PHPMailer-sur-Mutu-OVH.html

                $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
                try {
                //Server settings
                $mail->isMail();                                      // Set mailer to use SMTP
                $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'keeppetcool@gmail.com';                 // SMTP username
                $mail->Password = 'keeppetcool4!';                           // SMTP password
                $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 465;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom('keeppetcool@gmail.com', 'KeepPetCool');
                $mail->addAddress($userTo->getEmail(), $userTo->getFirstname() . ' ' . $userTo->getLastname());

                //Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'KeepPetCool - Nouveau message !';
                $mail->Body    = 'Bonjour, <br> Vous avez reçu un nouveau message sur votre messagerie KeepPetCool. <br> Connectez-vous à votre compte pour y accéder. <br> A bientôt, <br> L\'équipe KeepPetCool';
                $mail->AltBody = 'Bonjour, Vous avez reçu un nouveau message sur votre messagerie KeepPetCool. Connectez-vous à votre compte pour y accéder. A bientôt, L\'équipe KeepPetCool';

                $mail->send();
                    // dump('Message envoyé OK');
                } catch (Exception $e) {
                    // dump('Message could not be sent. Mailer Error: ', $mail->ErrorInfo);
                }
                    // Fin traitement envoi email au destinataire

                    $this->addFlash(
                        'success',
                        'Votre message a correctement été envoyé'
                    );
                    
                    return $this->redirectToRoute('messenging');
                }

        // Récupération des valeurs du bouton "Envoyer"
        // transmises en GET
        $userFromId = $request->query->get('userFrom');
        $userToId = $request->query->get('userTo');

        $userFrom = $userRepository->find($userFromId);
        $userTo = $userRepository->find($userToId);

        // On vérifie que l'utilisateur connecté
        // soit bien l'expéditeur du message
        if($currentUser !== $userFrom)
        {
            $this->addFlash(
                'danger',
                'Vous ne pouvez pas envoyer un message à la place d\'un tiers !'
            );

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('message/new.html.twig', [
            'userFrom' => $userFrom,
            'userTo' => $userTo,
        ]);
    }

    /**
     * @Route("/messenging", name="messenging", methods={"GET", "POST"})
     */
    public function messenging(MessageRepository $messageRepository)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupère les infos du user connecté
        $currentUser = $this->getUser();

        $messages = $messageRepository->allMessagesByUser($currentUser);

        return $this->render('message/messenging.html.twig', [
            'messages' => $messages
        ]);
    }

    /**
     * @Route("/conversation/{user1Id}/{user2Id}", name="conversation", methods={"GET", "POST"}, requirements={"user1"="\d+", "user2"="\d+"})
     */
    public function conversation($user1Id, $user2Id, UserRepository $userRepository, MessageRepository $messageRepository, EntityManagerInterface $em)
    {

        $user1Id = intval($user1Id);
        $user2Id = intval($user2Id);
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupère les infos du user connecté
        $currentUser = $this->getUser();
        
        // On vérifie que le user connecté soit concerné
        // par la conversation
        if($currentUser->getId() !== $user1Id && $currentUser->getId() !== $user2Id)
        {
            $this->addFlash(
                'danger',
                'Vous n\'avez pas les droits pour accéder à cette page !'
            );
            
            return $this->redirectToRoute('messenging');
        }
        
        // On recherche l'objet User pour chaque id transmis
        $user1 = $userRepository->find($user1Id);
        $user2 = $userRepository->find($user2Id);

        // Requête custom pour rechercher l'ensemble des messages
        // associés à la conversation
        $messages = $messageRepository->conversationBetween($user1, $user2);

        // Tous les messages de la conversation sont settés
        // comme étant lus
        foreach($messages as $message)
        {
            // Ne passe en lu uniquement si c'est le 
            // destinataire du message qui le lit
            if($currentUser === $message->getUserTo())
            {
                $message->setReaden(true);
            }
        }

        $em->flush();

        return $this->render('message/conversation.html.twig', [
            'user1' => $user1,
            'user2' => $user2,
            'messages' => $messages
        ]);
    }
}
