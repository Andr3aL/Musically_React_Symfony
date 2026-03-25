FROM node:20-alpine

# Installation de git
RUN apk add --no-cache git

# Répertoire de travail
WORKDIR /app

# Exposition du port Vite
EXPOSE 5173

# Commande par défaut
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
