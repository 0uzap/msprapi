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
    [16.0, 5.0, 15.0, 0.0, 0.0, 1, encode_date("2020-3-15")],
    [171076.0, 472.0, 13748.0, 7501.0, 5.0, 1, encode_date("2022-2-14")],
    [494.0, 19.0, 218.0, 25.0, 1.0, 3, encode_date("2020-4-15")],
    [228777.0, 2179.0, 19131.0, 3262.0, 7.0, 3, encode_date("2022-1-14")],
    [6629.0, 187.0, 2822.0, 536.0, 7.0, 4, encode_date("2020-5-15")],
    [213288.0, 230.0, 60431.0, 6155.0, 4.0, 4, encode_date("2021-12-14")],
    [853.0, 0.0, 13.0, 51.0, 0.0, 5, encode_date("2020-6-15")],
    [15819.0, 0.0, 246.0, 130.0, 0.0, 5, encode_date("2021-11-14")],
    [576.0, 35.0, 425.0, 27.0, 1.0, 6, encode_date("2020-7-15")],
    [62385.0, 242.0, 10429.0, 1653.0, 3.0, 6, encode_date("2021-10-14")],
    [3.0, 0.0, 0.0, 0.0, 0.0, 7, encode_date("2020-8-15")],
    [318.0, 0.0, 17.0, 0.0, 0.0, 7, encode_date("2021-9-14")],
    [95.0, 0.0, 1.0, 3.0, 0.0, 8, encode_date("2020-9-15")],
    [1378.0, 6.0, 75.0, 43.0, 0.0, 8, encode_date("2021-8-14")],
    [949063.0, 17096.0, 158862.0, 25342.0, 421.0, 10, encode_date("2020-10-15")],
    [4702657.0, 19697.0, 276004.0, 100250.0, 610.0, 10, encode_date("2021-7-14")],
    [117337.0, 1482.0, 40590.0, 1763.0, 25.0, 11, encode_date("2020-11-15")],
    [223723.0, 41.0, 4007.0, 4488.0, 4.0, 11, encode_date("2021-6-14")],
    [5079.0, 23.0, 122.0, 46.0, 0.0, 12, encode_date("2020-12-15")],
    [10838.0, 12.0, 95.0, 104.0, 0.0, 12, encode_date("2021-5-14")],
    [28665.0, 7.0, 222.0, 947.0, 0.0, 14, encode_date("2021-1-15")],
    [29451.0, 14.0, 109.0, 948.0, 0.0, 14, encode_date("2021-4-14")],
    [434712.0, 1225.0, 13270.0, 8985.0, 11.0, 15, encode_date("2021-2-15")],
    [493568.0, 2503.0, 26602.0, 9700.0, 19.0, 15, encode_date("2021-3-14")],
    [240671.0, 376.0, 6324.0, 3286.0, 4.0, 16, encode_date("2021-3-15")],
    [232123.0, 128.0, 2289.0, 3180.0, 2.0, 16, encode_date("2021-2-14")],
    #17
])

# Cibles associées (3 cibles => mois+1, mois+2, mois+3)
y_train = np.array([
    [784.0, 6053.0, 25527.0],
    [176463.0, 178355.0, 179243.0],
    [916.0, 1590.0, 3667.0],
    [268304.0, 272689.0, 274429.0],
    [11031.0, 20770.0, 38133.0],
    [224979.0, 262165.0, 265457.0],
    [862.0, 989.0, 1438.0],
    [20136.0, 29888.0, 37277.0],
    [1879.0, 3569.0, 7096.0],
    [64913.0, 65475.0, 93302.0],
    [3.0, 3.0, 3.0],
    [644.0, 1137.0, 1592.0],
    [112.0, 134.0, 148.0],
    [2304.0, 3830.0, 4118.0],
    [1310491.0, 1510203.0, 1783047.0],
    [5080908.0, 5229848.0, 5270003.0],
    [149120.0, 163972.0, 169255.0],
    [226756.0, 234227.0, 249803.0],
    [6350.0, 7438.0, 8421.0],
    [11081.0, 11186.0, 13279.0],
    [28900.0, 29130.0, 29469.0],
    [29957.0, 30262.0, 31431.0],
    [495464.0, 586883.0, 636424.0],
    [584205.0, 635780.0, 648732.0],
    [294211.0, 329371.0, 335264.0],
    [240295.0, 291894.0, 328994.0],
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
