# SAE3.02_fumeronl1u_gesnel2u_jallais3u_ratovobo1u

Rapport Sujet Développement Web

Données utiles au test de l’application
Pour la base de donnée, il faut créer un fichier db.config.ini dans src/classes/db/ sous la forme suivant : 
dsn=nom_base
user=user
password=mdp

sinon cela prendra automatiquement cette base de phpmyadmin : 
dsn=sae
user=root
password=

Url du dépôt git : https://github.com/baptisteFL/SAE3.02_fumeronl1u_gesnel2u_jallais3u_ratovobo1u

Les fonctionnalités que nous implémentons pour l'application Touiteur sont les suivantes. 

Afficher la liste touites en version courte en ordre chronologique inverse. (Kerrian) 

Les utilisateurs anonymes peuvent créer un compte à partir de l’onglet inscription.. (Kerrian)

Les utilisateurs possédant un compte Touiteur peuvent se connecter à leur compte en passant par l’onglet connexion. (Kerrian)
    
Lorsque l’on clique sur le nom d’un auteur, on affiche tous les touites de cette personne. (Nicka)

L’utilisateur peut afficher les touites associés à un tag (Nicka -> clique sur un tag / Bastien -> MyTag).

Un utilisateur peut s'abonner à un tag, puis s'aff.(Bastien)
   
Le mur d’un utilisateur : c’est la page d’accueil de l’utilisateur. Elle affiche les touites qui l’intéressent le plus. (Bastien)

Associer une image à un touite : On peut ajouter une image lorsqu’on rédige un nouveau touite. (Kerrian / Bastien)

Évaluer un touite : un utilisateur peut incrémenter (like) ou réduire (dislike) de 1 la note d’un touite qu’il consulte. L’utilisateur ne peut évaluer un même touite qu’une seule fois. (Baptiste)

Les utilisateurs authentifiés peuvent publier un touite. (Kerrian)

Afficher un touite en détail : lorsqu’on clique sur un touite dans une liste, on affiche le
touite en détail avec toutes ses informations

Paginer la liste des touites (Baptiste)

Afficher les touites d'une personne donnée (Nicka)

Afficher les touites associer à un tag (Nicka)

Suivre / Unfollow des utilisateurs avec le bouton suivre présent sur les touites. (Nicka)





