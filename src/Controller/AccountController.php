<?php

namespace App\Controller;

use Faker;
use Cocur\Slugify\Slugify;
use App\Form\UserProfileType;
use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use App\Message\UserNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends ApplicationController
{
    /**
     * @Route("/account", name="account")
     */
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     *
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function profile(Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $lastAvatar = $user->getAvatar();
        //$lastLogo = $user->getEnterpriseLogo();

        $filesystem = new Filesystem();

        $slugify = new Slugify();
        //dump($this->getParameter('avatar_directory'));
        $form = $this->createForm(UserProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatar')->getData();

            // this condition is needed because the 'avatar' field is not required
            // so the Image file must be processed only when a file is uploaded
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                // Move the file to the directory where avatars are stored
                try {
                    $avatarFile->move(
                        $this->getParameter('avatar_directory'),
                        $newFilename
                    );
                    $path = $this->getParameter('avatar_directory') . '/' . $lastAvatar;
                    if ($lastAvatar != NULL) $filesystem->remove($path);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setAvatar($newFilename);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Profile changes have been successfully saved."
            );
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     * 
     * @Route("/account/password-update", name="account_password")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $passwordUpdate = new PasswordUpdate();

        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //1. Vérifier que le oldpassword soit le même que celui de l'user
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                //Gérer l'erreur
                $form->get('oldPassword')->addError(new FormError("The password entered is not your current password"));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);

                $user->setPassword($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Your password has been changed successfully"
                );

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Permet d'envoyer un code réinitialisation de mot de passe d'un utilisateur à son adresse email
     * 
     * @Route("/account/recover/password", name="account_recoverpw")
     *
     * @return Response
     */
    public function recoverPassword()
    {
        return $this->render('account/recoverpw.html.twig');
    }

    /**
     * Permet de vérifier le code réinitialisation de mot de passe d'un utilisateur
     * 
     * @Route("/account/recover/password/code-verification", name="account_codeverification")
     *
     * @return void
     */
    public function codeVerification()
    {
        return $this->render('account/codeverification.html.twig');
    }

    /**
     * Permet de vérifier si l'adresse email entrer pour la réinitialisation de mot appartient à un utilisateur du site
     *
     * @Route("/account/recover/password/user-verification", name="account_userverification")
     * 
     * @param Request $request
     * @param UserRepository $userRepo
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     * 
     */
    public function userVerification(Request $request, UserRepository $userRepo, EntityManagerInterface $manager, MessageBusInterface $messageBus): JsonResponse
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        dump($request->getContent());
        $email = $paramJSON['email'];
        //dump($email);
        $user = $userRepo->findOneBy(['email' => $email]);
        if ($user != null) {
            $status = 200;
            $mess   = 'User exists';
            $faker = Faker\Factory::create('fr_FR');
            $codeVerification = $faker->randomNumber($nbDigits = 5, $strict = false);
            $user->setVerificationcode($codeVerification)
                ->setVerified(false);
            $manager->persist($user);
            $manager->flush();
            $code = 'PowerMon-' . $codeVerification . $user->getId();
            //dump($code);
            //$object = "PASSWORD RESET";
            $message = 'Your verification code is ' . $code;
            $message .= "\nWe heard that you lost your password. Sorry about that !

But don’t worry ! You can use the following code to reset your password : " . $code . "

Thanks,
The ST DIGITAL Power Monitoring Team";
            //$this->sendEmail($mailer, $email, $object, $message);
            $messageBus->dispatch(new UserNotificationMessage($user->getId(), $message, 'Reset'));
        } else if ($paramJSON['codeVerif'] != null) {
            $Verificationcode = $paramJSON['codeVerif'];
            $id = substr($Verificationcode, 5);
            $Verificationcode = substr($Verificationcode, 0, 5);
            $user = $userRepo->findOneBy(['id' => $id]);
            //dump($id);
            //dump($Verificationcode);
            //dump($user);
            if ($user != null && $user->getVerified() == false) {
                $userCode = $user->getVerificationcode();
                if ($userCode == $Verificationcode) {
                    $status = 200;
                    $mess   = $id;
                }
            } else {
                $status = 403;
                $mess   = $Verificationcode;
            }
        } else if ($paramJSON['codeVerif'] == null) {
            $status = 403;
            $mess   = "User don't exists";
        }
        //$status = 200;
        //$mess = 'received email : ' . $email;
        return $this->json(
            [
                'code'    => $status,
                'message' => $mess,
            ],
            200
        );
    }

    /**
     * Permet de vérifier si l'adresse email entrer pour la réinitialisation de mot appartient à un utilisateur du site
     *
     * @Route("/account/recover/password/reset", name="account_passwordReset")
     * 
     * @param Request $request
     * @param UserRepository $userRepo
     * @param EntityManagerInterface $manager
     * @return Response
     * 
     */
    public function passwordReset(Request $request, UserRepository $userRepo, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $passwordUpdate = new PasswordUpdate();

        $user = $this->getUser();
        $id = $request->query->get('d');
        //dump($id);
        $user = $userRepo->findOneBy(['id' => $id]);
        //dump($user);

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getVerified() == false) {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);

                $user->setPassword($hash)
                    ->setVerificationcode("")
                    ->setVerified(true);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Your password has been changed"
                );

                return $this->redirectToRoute('account_login');
            } else {
                $this->addFlash(
                    'danger',
                    "Unauthorized Modification"
                );
                /*return $this->render('account/resetpassword.html.twig', [
                    'form' => $form->createView()
                ]);*/
            }
        }

        return $this->render('account/resetpassword.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
