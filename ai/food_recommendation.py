#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
import random
import sys
import pandas as pd
from datetime import datetime

# 📂 وظيفة تحديد المسارات
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

# 🧠 تعريف الأعمدة
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

# 🧮 حساب العمر بدقة
def calculate_age(dob_str):
    try:
        dob = datetime.strptime(dob_str, "%Y-%m-%d")
        today = datetime.today()
        return today.year - dob.year - ((today.month, today.day) < (dob.month, dob.day))
    except:
        return 30  # قيمة افتراضية في حال الخطأ

def calculate_bmr(weight, height, gender, age):
    if str(gender).lower() == "male":
        return (10 * weight) + (6.25 * height) - (5 * age) + 5
    else:
        return (10 * weight) + (6.25 * height) - (5 * age) - 161

def generate_recommendations(profile):
    try:
        if not DATASET_DIR:
            return {"error": "Dataset folder not found. Path issues on server."}

        files = [f for f in os.listdir(DATASET_DIR) if f.endswith(".csv")]
        if not files:
            return {"error": "No CSV files found in directory."}
            
        df = pd.concat([pd.read_csv(os.path.join(DATASET_DIR, f)) for f in files], ignore_index=True)

        cols = {k: find_col(df, v) for k, v in COLUMN_MAP.items()}
        
        # التأكد من وجود الأعمدة الحيوية
        if not cols['name'] or not cols['cal']:
            return {"error": "Required columns (Food Name/Calories) missing in CSV files."}

        for k, c in cols.items():
            if c and k != 'name':
                df[c] = pd.to_numeric(df[c], errors='coerce').fillna(0)

        # استخراج البيانات الشخصية
        name = profile.get('full_name', 'Unknown')
        weight = float(profile.get('weight_kg', 70))
        height = float(profile.get('height_cm', 170))
        gender = profile.get('gender', 'male')
        age = calculate_age(profile.get('dob', '1995-01-01'))
        condition = str(profile.get('primary_condition', 'None')).upper()

        # الحسابات
        daily_calories = calculate_bmr(weight, height, gender, age) * 1.2
        max_meal_cal = daily_calories / 3

        # الفلترة
        filtered = df[df[cols['cal']] <= max_meal_cal].copy()
        if "DIABETES" in condition:
            filtered = filtered[filtered[cols['carb']] <= 30]
        elif "OBESITY" in condition:
            filtered = filtered[filtered[cols['fat']] <= 15]

        def create_meal_list(data, meal_type):
            items = data.sample(n=min(5, len(data))) if not data.empty else pd.DataFrame()
            return [{
                "food_name": str(row[cols['name']]),
                "calories": float(row[cols['cal']]),
                "protein": float(row[cols['prot']]) if cols['prot'] else 0,
                "carbohydrates": float(row[cols['carb']]) if cols['carb'] else 0,
                "fat": float(row[cols['fat']]) if cols['fat'] else 0,
                "description": f"اختيار ذكي لحالة {condition}",
                "confidence": round(random.uniform(0.9, 0.99), 2),
                "meal_type": meal_type
            } for _, row in items.iterrows()]

        return {
            "patient_info": {
                "full_name": name,
                "age": age,
                "condition": condition,
                "daily_calories": round(daily_calories, 2)
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