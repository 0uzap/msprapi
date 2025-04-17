import pandas as pd

data1 = pd.read_csv(r'./DATA/worldometer_coronavirus_daily_data.csv')


# On renomme les colonnes en Francais
data1.rename(columns={
    'country': 'pays',
    'cumulative_total_cases': 'cumul_cas_totaux',
    'daily_new_cases': 'nouveau_cas_journalier',
    'active_cases': 'cas_actif',
    'cumulative_total_deaths': 'cumul_mort_totaux',
    'daily_new_deaths': 'nouvelle_mort_journaliere'
    }, inplace=True)

# On supprime les duplications
data1.drop_duplicates(inplace=True)

# On met à 0 les valeurs vides (meme remarque que precedemment)
data1.fillna({'cumul_cas_totaux': 0,
              'nouveau_cas_journalier': 0,
              'cas_actif': 0,
              'cumul_mort_totaux': 0,
              'nouvelle_mort_journaliere': 0
              }, inplace=True)

# On verifie si il y a bien toutes les données
assert data1.isnull().sum().sum() == 0, "Il y a des valeurs manquantes!"

# Fichier de sortie
data1.to_csv(r'./worldometer_coronavirus_daily_data.csv', index=False)

