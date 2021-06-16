<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $userRoles = [];

        $userRoles['ROLE_USER'] = 'USER';
        $userRoles['ROLE_CUSTOMER'] = 'CUSTOMER';
        $userRoles['ROLE_MANAGER'] = 'MANAGEMENT';
        $userRoles['ROLE_NOC_SUPERVISOR'] = 'NOC-SUPERVISOR';
        $userRoles['ROLE_ADMIN'] = 'ADMINISTRATOR';

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy(['enterprise' => $this->getUser()->getEnterprise()]),
            'userRoles' => $userRoles
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $user->setEnterprise($this->getUser()->getEnterprise());
        $form = $this->createForm(UserType::class, $user, [
            'isEdit'     => false,
        ]);
        $form->handleRequest($request);
        //dump($form->isSubmitted());
        //dd($form->isValid());
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($user);
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "The user <strong> {$user->getFirstName()} {$user->getLastName()} </strong> has been successfully registered "
            );
            return $this->redirectToRoute('user_index');
        }
        //dd($user);

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'isEdit'     => false,
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'isEdit'     => true,
        ]);
        $form->handleRequest($request);
        //dump($user);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                'success',
                "The modifications of user <strong> {$user->getFirstName()} {$user->getLastName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'isEdit'     => true,
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
