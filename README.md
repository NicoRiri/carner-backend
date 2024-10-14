# Carner Backend

## 🚀 Utilisation
Changer les valeurs du .env.example pour commencer
````shell
mv .env.example .env
````
````shell
docker compose up
````

## 🛣️ Routes

Toutes les requêtes doivent être accompagné d'un access token défini comme Bearer Token.

### 🟩 GET
- /api/cart
  - Renvoie la liste actuelle de l'utilisateur
- /api/article
  - Renvoie tous les articles qui existent

### 🟨 POST
- /api/cart/{article_id}
  - Ajoute l'article correspondant à l'id dans la liste de l'utilisateur
- /api/article
  - Ajoute un article dans la base de données
    - body requis :
      - "file" : "image.png",
      - "name" : "nom"

### 🟪 PATCH
- /api/cart/{article_id}/{0|1}
  - Change le status de l'article correspondant à l'id dans la liste de l'utilisateur (0: non traité, 1: traité)

### 🟥 DELETE
- /api/cart
  - Supprime l'intégralité du contenu du panier
- /api/cart/{article_id}
  - Supprime l'article correspondant à l'id dans la liste de l'utilisateur

## ⚙️ Ports
- API : http://127.0.0.1
- Adminer : http://127.0.0.1:8080

## 📱 Application mobile
https://github.com/NicoRiri/carner-mobile