<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/user", name="admin_user_index")
     */
    public function index(UserRepository $userRepository): Response
    {
        $userRoles = [];

        $userRoles['ROLE_USER'] = 'USER';
        $userRoles['ROLE_CUSTOMER'] = 'CUSTOMER';
        $userRoles['ROLE_MANAGER'] = 'MANAGEMENT';
        $userRoles['ROLE_NOC_SUPERVISOR'] = 'NOC-SUPERVISOR';
        $userRoles['ROLE_ADMIN'] = 'ADMINISTRATOR';
        $userRoles['ROLE_SUPER_ADMIN'] = 'SUPER ADMINISTRATOR';

        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'userRoles' => $userRoles
        ]);
    }

    /**
     * @Route("admin/user/new", name="admin_user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        //$user->setEnterprise($this->getUser()->getEnterprise());
        $form = $this->createForm(AdminUserType::class, $user, [
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
            return $this->redirectToRoute('admin_user_index');
        }
        //dd($user);

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'isEdit'     => false,
        ]);
    }

    /**
     * @Route("admin/user/{id<\d+>}", name="admin_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("admin/user/{id<\d+>}/edit", name="admin_user_edit", methods={"GET","POST"})
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

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'isEdit'     => true,
        ]);
    }

    /**
     * @Route("admin/user/{id<\d+>}/delete", name="admin_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_user_index');
    }
}
