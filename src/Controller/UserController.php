<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface as PasswordEncoder;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @Route("/register", name="api_register", methods={"POST"})
     * @param ObjectManager $objectMan
     * @param PasswordEncoder $passwordEncoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function register(ObjectManager $objectMan, PasswordEncoder $passwordEncoder, Request $request)
    {
        try {
            $user = new User();
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $password_confirmation = $request->request->get('password_confirmation');
            $user->passwordConfirm($password, $password_confirmation);
            $user->passwordLen($password);
            $passwordEncode = $passwordEncoder->encodePassword($user, $password);
            $user->setEmail($email);
            $user->setPassword($passwordEncode);
            $objectMan->persist($user);
            $objectMan->flush();
            return $this->json([
                'user' => $user
            ], 201,
                [],
                ['groups' => ['api']
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $e->getMessage();
        }
    }

    /**
     * @Route("/login", name="api_login", methods={"POST"})
     */
    public function login()
    {
        return $this->json(['result' => true]);
    }

    /**
     * @Route("/profile", name="api_profile")
     * @IsGranted("ROLE_USER")
     */
    public function profile()
    {
        return $this->json([
            'user' => $this->getUser(),
        ],
            200,
            [],
            [
                'groups' => ['api']
            ]);
    }

    /**
     * @Route("/", name="api_home")
     */
    public function home()
    {
        return $this->json(['result' => true]);
    }
}
