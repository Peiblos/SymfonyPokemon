<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/rol', name: 'updaterol')]
    public function rol(EntityManagerInterface $em): Response
    {
        // Obtener el usuario actualmente autenticado
        $user = $this->getUser();

        // Verificar si el usuario está autenticado
        if (!$user) {
            return new Response('No estás autenticado', Response::HTTP_UNAUTHORIZED);
        }

        // Asignar el nuevo rol de administrador
        $ROLES = ['ROLE_ADMIN','ROLE_USER'];
        // $user->setRoles($ROLES); // Se pasa un array con el rol

        // Persistir y hacer el flush para guardar los cambios
        $em->persist($user);
        $em->flush();

        return new Response('Rol actualizado correctamente a ADMIN');
    }

}
