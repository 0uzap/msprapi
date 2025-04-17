import pandas as pd

data1 = pd.read_csv(r'./DATA/country_wise_latest.csv')


# On renomme les colonnes en Francais
data1.rename(columns={
    'Country/Region': 'pays',
    'Confirmed': 'nb_cas',
    'Deaths': 'nb_mort',
    'Recovered': 'nb_soigne',
    'Active': 'nb_actif',
    'New cases': 'nb_new_cas',
    'New deaths': 'nb_new_mort',
    'New recovered': 'nb_new_soigne',
    'Deaths / 100 Cases': 'mort_100cas',
    'Recovered / 100 Cases': 'soigne_100cas',
    'Deaths / 100 Recovered': 'mort_100soigne',
    'Confirmed last week': 'derniere_semaine_confirme',
    '1 week change': 'changement_semaine',
    '1 week % increase': 'pourcentage_augmentation_semaine',
    'WHO Region': 'continent'
    }, inplace=True)

# On supprime les duplications
data1.drop_duplicates(inplace=True)

# On met à 0 les valeurs vides
data1.fillna({'nb_cas': 0,
              'nb_mort': 0,
              'nb_soigne': 0,
              'nb_actif': 0,
              'nb_new_cas': 0,
              'nb_new_mort': 0,
              'nb_new_soigne': 0,
              'mort100_cas': 0,
              'soigne100_cas': 0,
              'mort100_soigne': 0,
              'derniere_semaine_confirme': 0,
              'changement_semaine': 0,
              'pourcentage_augmentation_semaine': 0
              }, inplace=True)

region_to_continent = {
    'Africa': 'Afrique',
    'Eastern Mediterranean': 'Asie',
    'Europe': 'Europe',
    'Americas': 'Amerique',
    'South-East Asia': 'Asie',
    'Western Pacific': 'Oceanie'
}

# On met les continents en Francais
data1['continent'] = data1['continent'].map(region_to_continent)

# On verifie si il y a bien toutes les données
assert data1.isnull().sum().sum() == 0, "Il y a des valeurs manquantes!"

# Fichier de sortie
data1.to_csv(r'.\country_wise_latest_modified_final.csv', index=False)