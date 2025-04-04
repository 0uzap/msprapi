# Image officielle Node.js 16
FROM node:16

# MAJ et install client MySQL
RUN apt-get update && apt-get install -y default-mysql-client

# Défini répertoire travail dans container
WORKDIR /usr/src/app

# Copie package.json et package-lock.json depuis le dossier api
COPY api/package*.json ./

# Installe dépendances
RUN npm install

# Copie contenu dossier api dans container
COPY api/. ./

# Copie swagger.yaml dans répertoire de travail
COPY api/swagger.yaml ./api/swagger.yaml

# Exposer port 3001
EXPOSE 3001

# Lance application avec node
CMD ["node", "index.js"]

