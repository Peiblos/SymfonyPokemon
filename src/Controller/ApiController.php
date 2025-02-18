<?php

namespace App\Controller;

use App\Entity\Pokedex;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/user/pokemons', name: 'user_pokemons', methods: ['GET'])]
    public function getUserPokemons(EntityManagerInterface $entityManager): JsonResponse
    {
        // Check if user is authenticated
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        // Get user's pokedex entries
        $pokedexRepository = $entityManager->getRepository(Pokedex::class);
        $userPokemons = $pokedexRepository->findAllByUser($user->getId());

        // Format the response
        $formattedPokemons = array_map(function($pokedexEntry) {
            return [
                'id' => $pokedexEntry->getPokemon()->getId(),
                'number' => $pokedexEntry->getPokemon()->getNumber(),
                'name' => $pokedexEntry->getPokemon()->getName(),
                'tipo' => $pokedexEntry->getPokemon()->getTipo(),
                'level' => $pokedexEntry->getPokemon()->getLevel(),
                'strong' => $pokedexEntry->getPokemon()->getStrong(),
                'image' => $pokedexEntry->getPokemon()->getImage(),
            ];
        }, $userPokemons);

        return $this->json([
            'user_id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'pokemons' => $formattedPokemons
        ]);
    }
}
