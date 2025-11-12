-- Migration: Ajout de la formation PPA (Permis de Port d'Arme)
-- Date: 2025-11-12

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Insertion de la formation PPA
INSERT INTO formations (
    titre,
    description,
    duree_heures,
    type,
    validite_mois,
    contenu_formation,
    questions_qcm,
    note_passage,
    statut
) VALUES (
    'Formation PPA – Permis de Port d\'Arme (arme de poing)',
    'Formation sur le port d\'arme de poing dans le cadre professionnel Brink\'s. Garantit la sécurité de l\'agent, de ses collègues et du public, tout en respectant les protocoles internes.',
    6,
    'obligatoire',
    12,
    'Introduction

Le PPA (Permis de Port d\'Arme) autorise l\'agent à porter une arme de poing dans le cadre professionnel Brink\'s.
Cette formation vise à garantir la sécurité de l\'agent, de ses collègues et du public, tout en respectant les protocoles internes.


Module 1 - Législation et responsabilité

- Utilisation autorisée uniquement dans le cadre professionnel.
- La légitime défense doit être proportionnelle à la menace.
- Toute utilisation inappropriée peut entraîner des sanctions.


Module 2 - Sécurité de manipulation

- Toujours vérifier l\'arme avant et après usage.
- Stocker l\'arme dans un coffre sécurisé lorsqu\'elle n\'est pas utilisée.
- Maintenir le doigt hors de la détente jusqu\'au tir.
- Communiquer avant de dégainer ("Arme !").


Module 3 - Maniement pratique

- Posture de tir : stable, protégée.
- Simulation de tir sur cible factice.
- Coordination avec l\'équipe via radio.
- Réagir calmement sous pression, ne jamais courir sans raison.


Module 4 - Gestion du stress

- Conserver le sang-froid lors d\'une attaque ou alerte.
- Prioriser la sécurité des collègues et des fonds avant tout.
- Suivre les procédures de signalement d\'incident.',
    '[
        {
            "question": "Dans quel cas pouvez-vous utiliser votre arme ?",
            "reponses": [
                "À tout moment",
                "Seulement pour la sécurité professionnelle",
                "Pour impressionner un camarade"
            ],
            "correct": 1
        },
        {
            "question": "Que devez-vous faire avant de dégainer ?",
            "reponses": [
                "Prévenir l\'équipe par radio",
                "Tenter de surprendre l\'ennemi",
                "Courir vers l\'ennemi"
            ],
            "correct": 0
        },
        {
            "question": "Comment stockez-vous votre arme lorsqu\'elle n\'est pas utilisée ?",
            "reponses": [
                "Dans votre sac",
                "Dans un coffre sécurisé",
                "Sur le siège du véhicule"
            ],
            "correct": 1
        },
        {
            "question": "Lors d\'une attaque, quelle est votre priorité ?",
            "reponses": [
                "Sauver les fonds",
                "Sauver vos collègues et vous-même",
                "Faire fuir les ennemis"
            ],
            "correct": 1
        },
        {
            "question": "Le doigt doit être sur la détente…",
            "reponses": [
                "Tout le temps",
                "Seulement en cas de tir",
                "Jamais"
            ],
            "correct": 1
        },
        {
            "question": "La légitime défense doit être…",
            "reponses": [
                "Proportionnelle à la menace",
                "Toujours maximale",
                "Optionnelle"
            ],
            "correct": 0
        },
        {
            "question": "Quel canal de communication utilisez-vous pour alerter vos collègues ?",
            "reponses": [
                "Radio 42",
                "Radio Discord",
                "Un site de mobilier urbain"
            ],
            "correct": 0
        },
        {
            "question": "Quelle action est interdite ?",
            "reponses": [
                "Tirer sur une cible factice lors d\'une formation",
                "Tirer sur un citoyen sans raison",
                "Vérifier son arme"
            ],
            "correct": 1
        },
        {
            "question": "Que faire après un incident ?",
            "reponses": [
                "Continuer comme si rien ne s\'était passé",
                "Faire un rapport",
                "Quitter la ville"
            ],
            "correct": 1
        },
        {
            "question": "Que devez-vous faire si vous êtes séparé de votre équipe en mission ?",
            "reponses": [
                "Rejoindre l\'équipe via point de rendez-vous",
                "Agir seul contre l\'ennemi",
                "Abandonner les fonds"
            ],
            "correct": 0
        }
    ]',
    70,
    'active'
);
