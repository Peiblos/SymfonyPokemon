<?php

namespace App\Controller;

use App\Entity\Pokedex;
use App\Form\PokedexType;
use App\Repository\PokedexRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pokedex')]
final class PokedexController extends AbstractController
{
    #[Route(name: 'pokedex_user', methods: ['GET'])]
    public function showPokedex(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

       $pokedex = $user->getPokedexes();

        if (!$pokedex) {
            return $this->render('pokedex/index.html.twig', [
                'pokedex' => [],
            ]);
        }
            
        // Obtener los Pokémon de la Pokédex
       

        return $this->render('pokedex/index.html.twig', [
            'pokedex' => $pokedex,
        ]);
    }

    #[Route('/{id}', name: 'app_pokedex_show', methods: ['GET'])]
    public function show(Pokedex $pokedex): Response
    {
        return $this->render('pokedex/show.html.twig', [
            'pokedex' => $pokedex,
        ]);
    }

    // #[Route('/{id}/new', name: 'app_new_battle', methods: ['GET'])]
    // public function show(Pokedex $pokemonPokedex, PokemonRepository $pokemonRepository, EntityManagerInterface $entityManager): Response
    // {
    //   
    //     $pokemonArray = $pokemonRepository->findAll();
    //     shuffle($pokemonArray);
    //     $wildPokemon = $pokemonArray[0];

    //     $user = $this->getUser();
    //     $pokedex = $user->getPokedexes();
    //     $pokedex->setPokemon($pokemon);
    //     $pokedex->setLevel($pokemon->getLevel());
    //     $pokedex->setStrong($pokemon->getStrong());

    //     $battle->setPlayer($this->getUser());
    //     $battle->setPokemonPlayer($pokemon);
    //     $battle->setPokemonWild($wildPokemon);

    //     $entityManager->persist($battle);
    //     $entityManager->flush();

    //     return $this->render('battle/show.html.twig', [
    //         'battle' => $battle,
    //     ]);
    // }

    #[Route('/training/{id}', name: 'app_pokedex_training', methods: ['GET', 'POST'])]
    public function training(int $id, PokedexRepository $pokedexRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response("Necesitas estar autenticado para entrenar al Pokémon", Response::HTTP_FORBIDDEN);
        }

        $pokemon = $pokedexRepository->find($id);
        if (!$pokemon) {
            return new Response("Pokémon no encontrado", Response::HTTP_NOT_FOUND);
        }

        // Entrenar el Pokémon sumando 10 puntos a su fuerza
        $pokemon->setStrong($pokemon->getStrong() + 10);
        $entityManager->persist($pokemon);
        $entityManager->flush();

        return $this->redirectToRoute('pokedex_user');
    }


}
