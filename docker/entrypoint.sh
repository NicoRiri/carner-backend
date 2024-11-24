#!/bin/bash

# Démarrer PHP-FPM en arrière-plan
php-fpm &

# Démarrer Nginx en mode foreground
nginx -g "daemon off;"
