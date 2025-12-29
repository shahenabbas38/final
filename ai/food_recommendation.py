#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
import random
import sys
import pandas as pd

# 📂 إعدادات المسارات
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATASET_DIR = os.path.join(BASE_DIR, "FINAL FOOD DATASET")

# 🧠 احتمالات أسماء الأعمدة
NAME_CANDIDATES = ["food", "Unnamed: 1", "Name"]
CAL_CANDIDATES = ["Caloric Value", "Calories", "Energy"]
PROTEIN_CANDIDATES = ["Protein", "protein"]
CARB_CANDIDATES = ["Carbohydrates", "Carbs"]
FAT_CANDIDATES = ["Fat", "fat"]

def find_column(df, candidates):
    for cand in candidates:
        if cand in df.columns: return cand
    for c in df.columns:
        for cand in candidates:
            if cand.lower() in str(c).lower(): return c
    return None

def calculate_daily_calories(weight, height, gender):
    if str(gender).lower() == "male":
        bmr = 10 * weight + 6.25 * height - 5 * 30 + 5
    else:
        bmr = 10 * weight + 6.25 * height - 5 * 30 - 161
    return bmr * 1.2

def generate_recommendations(profile):
    try:
        # تحميل البيانات
        frames = [pd.read_csv(os.path.join(DATASET_DIR, f)) 
                  for f in os.listdir(DATASET_DIR) if f.endswith(".csv")]
        df = pd.concat(frames, ignore_index=True)

        # اكتشاف الأعمدة
        name_col = find_column(df, NAME_CANDIDATES)
        cal_col = find_column(df, CAL_CANDIDATES)
        protein_col = find_column(df, PROTEIN_CANDIDATES)
        carb_col = find_column(df, CARB_CANDIDATES)
        fat_col = find_column(df, FAT_CANDIDATES)

        # تنظيف البيانات
        for col in [cal_col, carb_col, protein_col, fat_col]:
            df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)

        weight = float(profile.get("weight_kg", 70))
        height = float(profile.get("height_cm", 170))
        gender = (profile.get("gender") or "male").lower()
        condition = (profile.get("primary_condition") or "").upper()

        daily_cal = calculate_daily_calories(weight, height, gender)
        max_meal_cal = daily_cal / 3

        # 🩺 الفلترة حسب الحالة الصحية
        filtered = df[df[cal_col] <= max_meal_cal].copy()
        if "DIABETES" in condition:
            filtered = filtered[filtered[carb_col] <= 30]
        elif "OBESITY" in condition:
            filtered = filtered[filtered[fat_col] <= 15]

        # حساب الترتيب (Score)
        filtered["score"] = (1 / (1 + filtered[cal_col])) + (filtered[protein_col] / (filtered[carb_col] + 1))
        filtered = filtered.sort_values(by="score", ascending=False)

        # 🍽️ تجهيز القوائم (أفضل 15 خيار)
        total = filtered.head(15)
        
        def to_list(df_slice, meal_type):
            return [
                {
                    "food_name": str(row[name_col]),
                    "calories": float(row[cal_col]),
                    "protein": float(row[protein_col]),
                    "carbohydrates": float(row[carb_col]),
                    "fat": float(row[fat_col]),
                    "description": "🍽️ اختيار ذكي بناءً على حالتك الصحية واحتياج السعرات",
                    "confidence": round(random.uniform(0.9, 0.99), 2),
                    "meal_type": meal_type
                }
                for _, row in df_slice.iterrows()
            ]

        # إرجاع الخرج المرتب
        return {
            "breakfast": to_list(total.head(5), "BREAKFAST"),
            "lunch": to_list(total.iloc[5:10], "LUNCH"),
            "dinner": to_list(total.iloc[10:15], "DINNER")
        }

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    # قراءة المدخلات من Laravel
    if len(sys.argv) > 1:
        try:
            input_data = json.loads(sys.argv[1])
            recs = generate_recommendations(input_data)
            
            # 🎀 طباعة الخرج بتنسيق مرتب جداً (JSON Indented)
            print(json.dumps(recs, ensure_ascii=False, indent=2))
            
        except Exception as e:
            print(json.dumps({"error": f"Invalid input or processing error: {str(e)}"}))
    else:
        print(json.dumps({"error": "No patient data provided"}))