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

1) Afficher la liste touites en version courte en ordre chronologique inverse. (Kerrian) 

6) Les utilisateurs anonymes peuvent créer un compte à partir de l’onglet inscription.. (Kerrian)

7) Les utilisateurs possédant un compte Touiteur peuvent se connecter à leur compte en passant par l’onglet connexion. ()
    
4) Lorsque l’on clique sur le nom d’un auteur, on affiche tous les touites de cette personne. (Nicka)

5) L’utilisateur peut afficher les touites associés à un tag.

14)Un utilisateur peut s'abonner à un tag, puis s'aff.(Bastien)
   
12) Le mur d’un utilisateur : c’est la page d’accueil de l’utilisateur. Elle affiche les touites qui l’intéressent le plus. (Bastien)

16) Associer une image à un touite : On peut ajouter une image lorsqu’on rédige un nouveau touite. (Kerrian / Bastien)

9) Évaluer un touite : un utilisateur peut incrémenter (like) ou réduire (dislike) de 1 la note d’un touite qu’il consulte. L’utilisateur ne peut évaluer un même touite qu’une seule fois. (Baptiste)

8) Les utilisateurs authentifiés peuvent publier un touite. (Kerrian)

2) Afficher un touite en détail : lorsqu’on clique sur un touite dans une liste, on affiche le
touite en détail avec toutes ses informations

3) Paginer la liste des touites (Baptiste)

4) Afficher les touites d'une personne donnée (Nicka)

5) Afficher les touites associer à un tag (Nicka)

13) Suivre / Unfollow des utilisateurs avec le bouton suivre présent sur les touites. (Nicka)





