import pandas as pd

data1 = pd.read_csv(r'./DATA/owid-monkeypox-data.csv')
    
# Cette colonne ne nous sert à rien et va donc prendre de la place dans la BDD pour rien
data1 = data1.drop(columns=['iso_code',
                            'new_cases_per_million',
                            'total_cases_per_million',
                            'new_cases_smoothed_per_million',
                            'new_deaths_per_million',
                            'total_deaths_per_million',
                            'new_deaths_smoothed_per_million'
                            ])

# On renomme les colonnes en Francais
data1.rename(columns={
    'location': 'pays',
    'total_cases': 'nb_cas_totaux',
    'total_deaths': 'nb_mort_totaux',
    'new_cases': 'nb_nouveau_cas',
    'new_deaths': 'nb_nouvelle_mort',
    'new_cases_smoothed': 'moyenne_nouveau_cas',
    'new_deaths_smoothed': 'moyenne_nouvelle_mort',
}, inplace=True)

# On supprime les duplications
data1.drop_duplicates(inplace=True)

# On met à 0 les valeurs vides (meme remarque que precedemment)
data1.fillna({'nb_cas_totaux': 0,
              'nb_mort_totaux': 0,
              'nb_nouveau_cas': 0,
              'nb_nouvelle_mort': 0,
              'moyenne_nouveau_cas': 0,
              'moyenne_nouvelle_mort': 0,
              }, inplace=True)

# On verifie si il y a bien toutes les données
assert data1.isnull().sum().sum() == 0, "Il y a des valeurs manquantes!"

# Fichier de sortie
data1.to_csv(r'.\owid-monkeypox-data-final.csv', index=False)
