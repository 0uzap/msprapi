# main.py

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List
import numpy as np
import joblib

app = FastAPI(title="IA Prediction API")

# CORS middleware (pour que le frontend puisse appeler l'API)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # à restreindre en production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Chargement du modèle (doit exister sous ce nom dans le dossier)
model = joblib.load("model.pkl")

# Schéma pour la requête
class PredictionRequest(BaseModel):
    features: List[float]

# Schéma pour la réponse
class PredictionResponse(BaseModel):
    prediction: float

# Endpoint de prédiction
@app.post("/predict", response_model=PredictionResponse)
async def predict(data: PredictionRequest):
    try:
        input_data = np.array(data.features).reshape(1, -1)
        result = model.predict(input_data)
        return PredictionResponse(prediction=float(result[0]))
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))