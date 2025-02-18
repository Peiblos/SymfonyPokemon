<?php

namespace App\Controller;

use App\Entity\Pokedex;
use App\Form\PokedexType;
use App\Repository\PokedexRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pokedex')]
final class PokedexController extends AbstractController
{
    #[Route(name: 'pokedex_user', methods: ['GET'])]
    public function showPokedex(PokedexRepository $pokedexRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $pokedex = $pokedexRepository->findAliveByUser($this->getUser()->getId());

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

    #[Route('/resurrection', name: 'pokedex_user_dead', methods: ['GET'])]
    public function showPokedexDead(PokedexRepository $pokedexRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

    //    $pokedex = $user->getPokedexes();

    //     if (!$pokedex) {
    //         return $this->render('pokedex/index.html.twig', [
    //             'pokedex' => [],
    //         ]);
    //     }
            
        // Obtener los Pokémon caidos de la Pokédex
        $pokedex = $pokedexRepository->findFaintedByUser($this->getUser()->getId());

        return $this->render('pokedex/indexDeadPokedex.html.twig', [
            'pokedex' => $pokedex,
        ]);
    }

    #[Route('/resurrection/{id}', name: 'app_pokedexDead_show', methods: ['GET'])]
    public function resurrection(Pokedex $pokedex, EntityManagerInterface $entityManager): Response
    {
        $pokedex->setAlive(1);
        $entityManager->persist($pokedex);
        $entityManager->flush();

        $user = $this->getUser();
        $pokedex = $user->getPokedexes();
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
    
    #[Route('/leveling/{id}', name: 'app_pokedex_leveling', methods: ['GET', 'POST'])]
    public function leveling(Pokedex $pokedex, PokedexRepository $pokedexRepository, PokemonRepository $pokemonRepository,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response("Necesitas estar autenticado para entrenar al Pokémon", Response::HTTP_FORBIDDEN);
        }

        // $pokemon = $pokedexRepository->find($id);
        // if (!$pokemon) {
        //     return new Response("Pokémon no encontrado", Response::HTTP_NOT_FOUND);
        // }

        // Entrenar el Pokémon sumando 10 puntos a su fuerza
        $pokedex->setLevel($pokedex->getLevel() + 1);
        if($pokedex->getLevel() >= 10) {
            $level = $pokedex->getLevel();
            $strong = $pokedex->getStrong();
            // Si el nivel del pokemon es 10, buscar la evolución del pokemon
            //pokemon relacionado con el pokemon en la pokedex
            $pokemon = $pokemonRepository->findPokemonById($pokedex->getPokemon()->getId());
            $idPokemonEvo = $pokemon->getEvolution();
            // dd($idPokemonEvo);
            if($idPokemonEvo){
                // Si hay una evolución, buscar el nuevo pokemon y actualizar la pokedex
                $pokemonEvo = $pokemonRepository->find($idPokemonEvo);
                $pokedex->setPokemon($pokemonEvo);
                $pokedex->setLevel($level);
                $pokedex->setStrong($strong);
            } 
        }
        $entityManager->persist($pokedex);
        $entityManager->flush();

        return $this->redirectToRoute('pokedex_user');
    }


    #[Route('/fighting/{id}', name: 'app_pokedex_fighting', methods: ['GET', 'POST'])]
    public function fighting(Pokedex $pokedex, PokemonRepository $pokemonRepository, EntityManagerInterface $entityManager): Response
    {
        $pokemonArray = $pokemonRepository->findAll();
        shuffle($pokemonArray);
        $wildPokemon = $pokemonArray[0];

        return $this->render('pokedex/showbattle.html.twig', [
            'wildPokemon' => $wildPokemon,
            'pokemon' => $pokedex,
        ]);
    }

    #[Route('/battle/{pokedex}/{wildpokemon}', name: 'app_fighting', methods: ['GET', 'POST'])]
    public function battle(Pokedex $pokedex, int $wildpokemon, PokemonRepository $pokemonRepository, EntityManagerInterface $entityManager): Response
    {

        $wildpokemonEntity = $pokemonRepository->find($wildpokemon);

        $levelPokemonPlayer = $pokedex->getLevel();
        $strongPokemonPlayer = $pokedex->getStrong();
        $scorePokemonPlayer = $levelPokemonPlayer*$strongPokemonPlayer;

        $levelPokemonWild = $wildpokemonEntity->getLevel();
        $strongPokemonWild = $wildpokemonEntity->getStrong();
        $scorePokemonWild = $levelPokemonWild*$strongPokemonWild;

        if($scorePokemonPlayer > $scorePokemonWild || $scorePokemonPlayer == $scorePokemonWild){
            // $pokedex->setLevel($pokedex->getLevel()+1);
            $winner = $pokedex;
            $loser = $wildpokemonEntity;
            $result = 'Victoria';
        }else{
            $wildpokemonEntity->setLevel($wildpokemonEntity->getLevel()+1);
            $winner = $wildpokemonEntity;
            $loser = $pokedex;
            $pokedex->setAlive(0);
            $result = 'Derrota';
        }
        $entityManager->persist($pokedex);
        $entityManager->flush();

        return $this->render('pokedex/result.html.twig', [
            'opponent' => $wildpokemonEntity,
            'pokemon' => $pokedex,
            'winner' => $winner,
            'loser' => $loser,
           'result' => $result,
        ]);
    }


}