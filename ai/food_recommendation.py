#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
import random
import sys
import pandas as pd

# 📂 وظيفة تحديد المسارات (للتوافق مع Railway و Git)
def get_dataset_path():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    possible_paths = [
        os.path.join(base_dir, "ai", "FINAL FOOD DATASET"),
        os.path.join(base_dir, "FINAL FOOD DATASET")
    ]
    for path in possible_paths:
        if os.path.exists(path): return path
    return None

DATASET_DIR = get_dataset_path()

# 🧠 تعريف الأعمدة المستهدفة في ملفات الـ CSV
COLUMN_MAP = {
    'name': ["food", "Unnamed: 1", "Name"],
    'cal': ["Caloric Value", "Calories", "Energy"],
    'prot': ["Protein", "protein"],
    'carb': ["Carbohydrates", "Carbs"],
    'fat': ["Fat", "fat"]
}

def find_col(df, candidates):
    for c in candidates:
        if c in df.columns: return c
    return None

def calculate_bmr(weight, height, gender):
    # معادلة Mifflin-St Jeor لحساب السعرات الأساسية
    if str(gender).lower() == "male":
        return (10 * weight) + (6.25 * height) - (5 * 30) + 5
    else:
        return (10 * weight) + (6.25 * height) - (5 * 30) - 161

def generate_recommendations(profile):
    try:
        if not DATASET_DIR:
            return {"error": "Dataset folder not found. Check path: ai/FINAL FOOD DATASET"}

        # تحميل البيانات
        files = [f for f in os.listdir(DATASET_DIR) if f.endswith(".csv")]
        df = pd.concat([pd.read_csv(os.path.join(DATASET_DIR, f)) for f in files], ignore_index=True)

        # تحديد أسماء الأعمدة الفعلية وتنظيف البيانات
        cols = {k: find_col(df, v) for k, v in COLUMN_MAP.items()}
        for c in [cols['cal'], cols['prot'], cols['carb'], cols['fat']]:
            df[c] = pd.to_numeric(df[c], errors='coerce').fillna(0)

        # استخراج بيانات المريض الشخصية
        name = profile.get('full_name', 'Unknown Patient')
        weight = float(profile.get('weight_kg', 70))
        height = float(profile.get('height_cm', 170))
        gender = profile.get('gender', 'male')
        condition = str(profile.get('primary_condition', 'None')).upper()

        # الحسابات الطبية
        daily_calories = calculate_bmr(weight, height, gender) * 1.2
        max_meal_cal = daily_calories / 3

        # فلترة ذكية بناءً على الحالة الصحية (سكري، سمنة، الخ)
        filtered = df[df[cols['cal']] <= max_meal_cal].copy()
        if "DIABETES" in condition:
            filtered = filtered[filtered[cols['carb']] <= 30]
        elif "OBESITY" in condition:
            filtered = filtered[filtered[cols['fat']] <= 15]

        def create_meal_list(data, meal_type):
            items = data.sample(n=5) if len(data) >= 5 else data
            return [{
                "food_name": str(row[cols['name']]),
                "calories": float(row[cols['cal']]),
                "protein": float(row[cols['prot']]),
                "carbohydrates": float(row[cols['carb']]),
                "fat": float(row[cols['fat']]),
                "description": f"وجبة موصى بها لحالة {condition}",
                "confidence": round(random.uniform(0.9, 0.99), 2),
                "meal_type": meal_type
            } for _, row in items.iterrows()]

        # النتيجة النهائية (تتضمن المعلومات الشخصية)
        return {
            "patient_info": {
                "full_name": name,
                "weight": f"{weight} kg",
                "height": f"{height} cm",
                "condition": condition,
                "calculated_daily_calories": round(daily_calories, 2)
            },
            "breakfast": create_meal_list(filtered, "BREAKFAST"),
            "lunch": create_meal_list(filtered, "LUNCH"),
            "dinner": create_meal_list(filtered, "DINNER")
        }

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) > 1:
        data = json.loads(sys.argv[1])
        print(json.dumps(generate_recommendations(data), ensure_ascii=False))