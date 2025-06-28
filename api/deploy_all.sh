# PAS FINI PAS TOUCHE NE PAS LANCER !!!!!!!!!!!!!!!

#!/bin/bash

echo "🚀 Déploiement multi-pays en cours..."

# 1️⃣ FRANCE
echo "▶️ [France] build & up"
docker-compose -f docker-compose.yml -f docker-compose.fr.yml up --build -d

echo "▶️ [France] création des tables"
docker cp create_tables.sql db-fr:/tmp/create_tables.sql
docker exec -i db-fr sh -c 'cat /tmp/create_tables.sql | mysql -uroot -prootpassword bdd_mspr_api'

echo "▶️ [France] import des CSV"
docker exec -it api-fr mkdir -p /mnt/data
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/country_wise_latest_modified_final.csv" api-fr:/mnt/data/country_wise_latest_modified_final.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/continent.csv" api-fr:/mnt/data/continent.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/owid-monkeypox-data-final.csv" api-fr:/mnt/data/owid-monkeypox-data-final.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/pays.csv" api-fr:/mnt/data/pays.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/worldometer_coronavirus_daily_data_final.csv" api-fr:/mnt/data/worldometer_coronavirus_daily_data_final.csv
docker exec -e PAYS_CIBLE=FR -it api-fr bash -c "node import_csv.js"

echo "✅ [France] terminé"

# 2️⃣ USA
echo "▶️ [USA] build & up"
docker-compose -f docker-compose.yml -f docker-compose.us.yml up --build -d

echo "▶️ [USA] création des tables"
docker cp create_tables.sql db-us:/tmp/create_tables.sql
docker exec -i db-us sh -c 'cat /tmp/create_tables.sql | mysql -uroot -prootpassword bdd_mspr_api'

echo "▶️ [USA] import des CSV"
docker exec -it api-us mkdir -p /mnt/data
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/country_wise_latest_modified_final.csv" api-us:/mnt/data/country_wise_latest_modified_final.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/continent.csv" api-us:/mnt/data/continent.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/owid-monkeypox-data-final.csv" api-us:/mnt/data/owid-monkeypox-data-final.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/pays.csv" api-us:/mnt/data/pays.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/worldometer_coronavirus_daily_data_final.csv" api-us:/mnt/data/worldometer_coronavirus_daily_data_final.csv
docker exec -e PAYS_CIBLE=US -it api-us bash -c "node import_csv.js"

echo "✅ [USA] terminé"

# 3️⃣ SUISSE
echo "▶️ [Suisse] build & up"
docker-compose -f docker-compose.yml -f docker-compose.ch.yml up --build -d

echo "▶️ [Suisse] création des tables"
docker cp create_tables.sql db-ch:/tmp/create_tables.sql
docker exec -i db-ch sh -c 'cat /tmp/create_tables.sql | mysql -uroot -prootpassword bdd_mspr_api'

echo "▶️ [Suisse] import des CSV"
docker exec -it api-ch mkdir -p /mnt/data
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/country_wise_latest_modified_final.csv" api-ch:/mnt/data/country_wise_latest_modified_final.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/continent.csv" api-ch:/mnt/data/continent.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/owid-monkeypox-data-final.csv" api-ch:/mnt/data/owid-monkeypox-data-final.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/pays.csv" api-ch:/mnt/data/pays.csv
docker cp "C:/Users/Tristan/Desktop/epsi taff/mspr/ETL/worldometer_coronavirus_daily_data_final.csv" api-ch:/mnt/data/worldometer_coronavirus_daily_data_final.csv
docker exec -e PAYS_CIBLE=CH -it api-ch bash -c "node import_csv.js"

echo "✅ [Suisse] terminé"

echo "🎉 Déploiement multi-pays terminé avec succès !"
