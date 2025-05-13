import pandas as pd

# === Chargement des données sources ===
monkeypox_df = pd.read_csv(r'./DATA/owid-monkeypox-data.csv')
covid_global_df = pd.read_csv(r'./DATA/country_wise_latest.csv')
covid_daily_df = pd.read_csv(r'./DATA/worldometer_coronavirus_daily_data.csv')

# === Création de la table des continents ===
region_to_continent = {
    'Africa': 'Afrique',
    'Eastern Mediterranean': 'Asie',
    'Europe': 'Europe',
    'Americas': 'Amerique',
    'South-East Asia': 'Asie',
    'Western Pacific': 'Oceanie'
}
continent_fr = sorted(set(region_to_continent.values()))
continent_df = pd.DataFrame({
    'idContinent': [0] + list(range(1, len(continent_fr)+1)),
    'continent': ['Inconnu'] + continent_fr
})
continent_df.to_csv(r'..\ETL\continent.csv', index=False)

# === Table de référence des pays avec idContinent ===
covid_global_df['continent'] = covid_global_df['WHO Region'].map(region_to_continent)
continents_pays_df = covid_global_df[['Country/Region', 'continent']].drop_duplicates()

# Tous les pays uniques
pays_monkeypox = monkeypox_df['location'].unique()
pays_global = covid_global_df['Country/Region'].unique()
pays_daily = covid_daily_df['country'].unique()
tous_les_pays = pd.Series(list(set(pays_monkeypox) | set(pays_global) | set(pays_daily)), name='pays').sort_values()

# Table pays avec ID
pays_df = pd.DataFrame({
    'id_pays': range(1, len(tous_les_pays)+1),
    'pays': tous_les_pays.values
})
pays_df = pays_df.merge(continents_pays_df, how='left', left_on='pays', right_on='Country/Region').drop(columns='Country/Region')
pays_df = pays_df.merge(continent_df, how='left', on='continent')

# Remplacement des NaN de idContinent par 0
if 'idContinent' not in pays_df.columns:
    pays_df['idContinent'] = 0
else:
    pays_df['idContinent'] = pays_df['idContinent'].fillna(0).astype(int)
pays_df.drop(columns='continent', inplace=True)
pays_df.to_csv(r'..\ETL\pays.csv', index=False)

# === Fonction d’ajout ID pays et continent à un jeu de données ===
def ajouter_id_pays(df, nom_colonne):
    return df.merge(pays_df[['id_pays', 'pays', 'idContinent']], how='left', left_on=nom_colonne, right_on='pays').drop(columns=[nom_colonne, 'pays'])

# === Fonction de suppressions des lignes vides des jeu de données ===
colonnes_exclues_monkeypox_df = ['id_pays', 'idContinent', 'date']
colonnes_exclues_covid_global_df = ['id_pays', 'idContinent']
colonnes_exclues_covid_daily_df = ['id_pays', 'idContinent', 'date']

def supprimer_lignes_vides(df, colonnes_exclues):
    colonnes_a_verifier = [col for col in df.columns if col not in colonnes_exclues]
    condition = (df[colonnes_a_verifier] == 0).all(axis=1)
    lignes_supprimees = condition.sum()
    df_filtré = df[~condition]
    print(f"🧹 {lignes_supprimees} lignes supprimées (toutes les valeurs = 0 sauf exceptions).")
    return df_filtré

# === Traitement monkeypox ===
data1 = monkeypox_df.copy()
data1 = data1.drop(columns=[
    'iso_code',
    'new_cases_per_million',
    'total_cases_per_million',
    'new_cases_smoothed_per_million',
    'new_deaths_per_million',
    'total_deaths_per_million',
    'new_deaths_smoothed_per_million'
])
data1.rename(columns={
    'location': 'pays',
    'total_cases': 'nbCasTotaux',
    'total_deaths': 'nbMortTotaux',
    'new_cases': 'nbNouveauCas',
    'new_deaths': 'nbNouvelleMort',
    'new_cases_smoothed': 'moyenneNouveauCas',
    'new_deaths_smoothed': 'moyenneNouvelleMort',
}, inplace=True)
data1.drop_duplicates(inplace=True)
data1.fillna(0, inplace=True)
data1 = ajouter_id_pays(data1, 'pays')

data1 = supprimer_lignes_vides(data1, colonnes_exclues_monkeypox_df)

# Vérification des valeurs manquantes monkeypox
nb_nan1 = data1.isnull().sum().sum()
if nb_nan1 > 0:
    print(f"\n🚨 {nb_nan1} valeurs manquantes détectées dans monkeypox après fusion.")
else:
    print("✅ Aucune valeur manquante dans monkeypox.")
data1.to_csv(r'..\ETL\owid-monkeypox-data-final.csv', index=False)

# === Traitement country_wise_latest ===
data2 = covid_global_df.copy()
data2.rename(columns={
    'Country/Region': 'pays',
    'Confirmed': 'nbCas',
    'Deaths': 'nbMort',
    'Recovered': 'nbSoigne',
    'Active': 'nbActif',
    'New cases': 'nbNewCas',
    'New deaths': 'nbNewMort',
    'New recovered': 'nbNewSoigne',
    'Deaths / 100 Cases': 'mort100cas',
    'Recovered / 100 Cases': 'soigne100cas',
    'Deaths / 100 Recovered': 'mort100soigne',
    'Confirmed last week': 'derniereSemaineConfirme',
    '1 week change': 'changementSemaine',
    '1 week % increase': 'pourcentageAugmentationSemaine',
}, inplace=True)
data2.drop_duplicates(inplace=True)
data2.fillna(0, inplace=True)
data2 = ajouter_id_pays(data2, 'pays')
data2.drop(columns=['WHO Region'], inplace=True)

data2 = supprimer_lignes_vides(data2, colonnes_exclues_covid_global_df)

# Vérification des valeurs manquantes country_wise_latest
nb_nan2 = data2.isnull().sum().sum()
if nb_nan2 > 0:
    print(f"\n🚨 {nb_nan2} valeurs manquantes dans country_wise_latest après fusion.")
else:
    print("✅ Aucune valeur manquante dans country_wise_latest.")
data2.to_csv(r'..\ETL\country_wise_latest_modified_final.csv', index=False)

# === Traitement worldometer_coronavirus_daily_data ===
data3 = covid_daily_df.copy()
data3.rename(columns={
    'country': 'pays',
    'cumulative_total_cases': 'cumulCasTotaux',
    'daily_new_cases': 'nouveauCasJournalier',
    'active_cases': 'casActif',
    'cumulative_total_deaths': 'cumulMortTotaux',
    'daily_new_deaths': 'nouvelleMortJournaliere'
}, inplace=True)
data3.drop_duplicates(inplace=True)
data3.fillna(0, inplace=True)
data3 = ajouter_id_pays(data3, 'pays')

data3 = supprimer_lignes_vides(data3, colonnes_exclues_covid_daily_df)

# Vérification des valeurs manquantes worldometer
nb_nan3 = data3.isnull().sum().sum()
if nb_nan3 > 0:
    print(f"\n🚨 {nb_nan3} valeurs manquantes dans worldometer après fusion.")
else:
    print("✅ Aucune valeur manquante dans worldometer.")
data3.to_csv(r'..\ETL\worldometer_coronavirus_daily_data_final.csv', index=False)
