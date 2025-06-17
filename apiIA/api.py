from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List
import numpy as np
from datetime import datetime
from sklearn.ensemble import RandomForestRegressor

app = FastAPI(title="IA Prediction API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

def encode_date(date_str: str) -> int:
    base = datetime(2020, 1, 1)
    date = datetime.strptime(date_str, "%Y-%m-%d")
    return (date - base).days

# Exemple de données d'entraînement
# colonnes: cumulCasTotaux, nouveauCasJournalier, casActif,
# cumulMortTotaux, nouvelleMortJournaliere, id_pays, days_since
X_train = np.array([
    [16.0, 5.0, 15.0, 0.0, 0.0, 1, encode_date("2020-03-15")],
    [171076.0, 472.0, 13748.0, 7501.0, 5.0, 1, encode_date("2022-02-14")],
    [494.0, 19.0, 218.0, 25.0, 1.0, 3, encode_date("2020-04-15")],
    [228777.0, 2179.0, 19131.0, 3262.0, 7.0, 3, encode_date("2022-01-14")],
    [6629.0, 187.0, 2822.0, 536.0, 7.0, 4, encode_date("2020-05-15")],
    [213288.0, 230.0, 60431.0, 6155.0, 4.0, 4, encode_date("2021-12-14")],
])

# Cibles associées (3 cibles => mois+1, mois+2, mois+3)
y_train = np.array([
    [784.0, 6053.0, 25527.0],
    [176463.0, 178355.0, 179243.0],
    [916.0, 1590.0, 3667.0],
    [268304.0, 272689.0, 274429.0],
    [11031.0, 20770.0, 38133.0],
    [224979.0, 262165.0, 265457.0],
])

model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

class PredictionRequest(BaseModel):
    features: List[List[float]]

class PredictionResponse(BaseModel):
    predictions: List[List[float]]

@app.post("/predict", response_model=PredictionResponse)
async def predict(data: PredictionRequest):
    try:
        input_data = np.array(data.features)
        if input_data.shape[1] != 7:
            raise ValueError("Chaque exemple doit contenir exactement 7 valeurs : 6 features + 1 pour la date encodée.")

        prediction = model.predict(input_data)
        
        corrected_predictions = []
        for pred_row, features in zip(prediction, input_data):
            cumul_cas_totaux = features[0]
            corrected_row = []
            for pred_val in pred_row:
                if pred_val < cumul_cas_totaux:
                    corrected_row.append(float(cumul_cas_totaux))
                else:
                    corrected_row.append(float(pred_val))
            corrected_predictions.append(corrected_row)

        return PredictionResponse(predictions=corrected_predictions)

    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
