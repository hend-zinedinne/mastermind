<?php

// ===================================================================================
// 1. BLOC DE CONFIGURATION ET D'INITIALISATION
// ===================================================================================

// Définition des constantes pour la configuration du jeu
const LONGUEUR_CODE = 4;
const MAX_TENTATIVES = 12;

// Tableaux indexés des couleurs disponibles
// NOTE: Les deux tableaux doivent avoir le même ordre pour maintenir la correspondance !
$initialesCouleurs = ['R', 'V', 'B', 'J', 'P', 'N']; // Les initiales que le joueur saisit
$emojisCouleurs = ['🔴', '🟢', '🔵', '🟡', '🟣', '⚫']; // Les emojis pour l'affichage

// Emojis pour les indices
const CLE_BIEN_PLACE = '🔑';
const PION_MAL_PLACE = '⚪';

$combinaisonSecrete = [];
$propositionJoueur = '';
$initialesPropositionJoueur = [];
$nombreTotalTentatives = 0;
$valide = false;
$historiquePlateauPropositions = [];
$tempHistoriquePropositions;
$historiquePlateauIndices = [];
$tempHistoriqueIndices = [];

echo "
================================================================
           MASTERMIND EN CONSOLE PHP (BTS SIO 1)
================================================================
Objectif : Deviner la combinaison secrète de " . LONGUEUR_CODE . " pions en " . MAX_TENTATIVES . " tentatives maximum.
Couleurs disponibles : ";

// Affichage des options de couleur pour le joueur

foreach ($emojisCouleurs as $index => $couleurs) {
    echo $emojisCouleurs[$index] . " (";
    echo $initialesCouleurs[$index] . ") ";
}

// ===================================================================================
// 2. GÉNÉRATION DE LA COMBINAISON SECRÈTE
// ===================================================================================

for ($longueur = 1; $longueur <= LONGUEUR_CODE; $longueur++) {
    $combinaisonSecrete[] = array_rand($initialesCouleurs);
}

foreach ($combinaisonSecrete as $index => $combinaison) {
    $combinaisonSecrete[$index] = $initialesCouleurs[$combinaison];
}

// ===================================================================================
// 3. BOUCLE PRINCIPALE DU JEU
// ===================================================================================

$victoire = false;

// La boucle tourne tant que le joueur n'a pas gagné ET que le nombre max de tentatives n'est pas atteint
for ($tentative = 1; $tentative <= MAX_TENTATIVES; $tentative++) {
    echo "\n--- Tentative $tentative / " . MAX_TENTATIVES . " ---\n";

    // -------------------------------------------------------------------------------
    // 3.1. BLOC DE SAISIE ET VALIDATION
    // -------------------------------------------------------------------------------

    while (!$valide) {
        $propositionJoueur = readline("Entrez votre proposition (4 initiales, ex: RVBJ) : ");
        $propositionJoueur = trim($propositionJoueur);
        $propositionJoueur = strtoupper($propositionJoueur);
        $initialesPropositionJoueur = str_split($propositionJoueur);
        if (strlen($propositionJoueur) === LONGUEUR_CODE && !in_array($initialesPropositionJoueur, $initialesCouleurs)) {
            $valide = true;
        } else {
            echo "Erreur : Proposition invalide. Veuillez réessayer.", PHP_EOL;
        }
    }

    $valide = false;

    // -------------------------------------------------------------------------------
    // 3.2. BLOC D'ANALYSE (ALGORITHME MASTERMIND)
    // -------------------------------------------------------------------------------

    $bienPlace = 0;
    $malPlace = 0;

    // On sauvegarde la proposition pour l'affichage (elle sera modifiée pendant les calculs)

    $propositionAffichage = $propositionJoueur;

    // On fait une copie de la combinaison secrète pour pouvoir marquer (mettre à null) les pions
    // qui ont déjà été utilisés sans modifier l'original, ce qui permet de respecter
    // la règle du compte unique de Mastermind.
    // NOTE: $proposition peut être modifiée directement car elle est réinitialisée à chaque tentative.
    $secreteTravail = $combinaisonSecrete;

    // ÉTAPE 1 : CALCUL DES BIEN PLACÉ (Clés Noires 🔑)
    // On utilise un simple "for" pour comparer position par position.

    foreach ($initialesPropositionJoueur as $index => $initiale) {
        if ($initiale == $combinaisonSecrete[$index]) {

            $bienPlace++;
            $secreteTravail[$index] = NULL;

            // ÉTAPE 2 : CALCUL DES MAL PLACÉ (Pions Blancs ⚪)
            // On compare les éléments non NULL restants.

        } else if (in_array($initiale, $secreteTravail)) {

            $malPlace++;

        }
    }

    $tempHistoriquePropositions = str_split($propositionJoueur);

    $historiquePlateauPropositions[] = "$tentative.";

    foreach ($tempHistoriquePropositions as $index => $initialePlateauPropositions) {
        foreach ($emojisCouleurs as $indexEmoji => $emoji) {
            if ($initialePlateauPropositions == $initialesCouleurs[$indexEmoji]) {
                $historiquePlateauPropositions[$tentative - 1] .= "$emoji ";
            }
        }
    }

    $historiquePlateauIndices[] = " ";


    for ($nombreIndicesBienPlace = 1; $nombreIndicesBienPlace <= $bienPlace; $nombreIndicesBienPlace++) {
        $historiquePlateauIndices[$tentative - 1] .= CLE_BIEN_PLACE . " ";
    }

    for ($nombreIndicesMalPlace = 1; $nombreIndicesMalPlace <= $malPlace; $nombreIndicesMalPlace++) {
        $historiquePlateauIndices[$tentative - 1] .= PION_MAL_PLACE . " ";
    }

    // -------------------------------------------------------------------------------
    // 3.3. BLOC D'AFFICHAGE ET GESTION DE LA FIN DE PARTIE
    // -------------------------------------------------------------------------------

    // Affichage de la proposition du joueur en emojis

    echo "--- Plateau de jeu ---", PHP_EOL;
    echo "-------------------------------------------------------------------------------";

    // foreach ($initialesPropositionJoueur as $index => $initiale) {
    //     foreach ($emojisCouleurs as $indexEmoji => $emoji) {
    //         if ($initiale == $initialesCouleurs[$indexEmoji]) {
    //             echo $emoji, " ";
    //         }
    //     }
    // }

    foreach ($historiquePlateauPropositions as $index => $tentativePrecedente) {
        echo PHP_EOL, "$tentativePrecedente |" . $historiquePlateauIndices[$index];
    }

    // Affichage des indices


    if ($initialesPropositionJoueur == $combinaisonSecrete) {
        $victoire = true;
        break;
    } else {
        $nombreTotalTentatives++;
    }

    echo PHP_EOL, "-------------------------------------------------------------------------------", PHP_EOL;

} // Fin de la boucle principale

// ===================================================================================
// 4. BLOC DE RÉSULTAT FINAL
// ===================================================================================

// Affichage de la combinaison secrète à la fin (Victoire ou Défaite)

echo PHP_EOL;

echo "================================================================", PHP_EOL;
if ($victoire) {
    echo "🎉 FÉLICITATIONS ! Vous avez trouvé la combinaison secrète en $nombreTotalTentatives tentatives !", PHP_EOL;
} else {
    echo "😭 DOMMAGE ! Vous avez atteint la limite de 12 tentatives.", PHP_EOL;
}
echo "La combinaison secrète était : ";
foreach ($combinaisonSecrete as $index => $initiale) {
    foreach ($emojisCouleurs as $indexEmoji => $emoji)
        if ($combinaisonSecrete[$index] == $initialesCouleurs[$indexEmoji]) {
            echo $emoji, " ";
        }
}
echo PHP_EOL, "================================================================", PHP_EOL;