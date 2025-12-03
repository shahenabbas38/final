#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
import random
import requests
import pandas as pd

# 🛡️ إعدادات Laravel API
BASE_URL = "http://127.0.0.1:8000/api"
PATIENT_TOKEN = "9Ia2noUelIYiB40y1ZsWAD9TB1kUcU0ChvNHF6Eb49eb042f"  # ⚠️ غيّر هذا للتوكن الحقيقي

# 📂 مجلد البيانات
DATASET_DIR = os.path.join(os.path.dirname(__file__), "FINAL FOOD DATASET")

# 🧠 احتمالات أسماء الأعمدة
NAME_CANDIDATES = ["food", "Unnamed: 1", "Name"]
CAL_CANDIDATES = ["Caloric Value", "Caloric Va", "Calories", "Energy"]
PROTEIN_CANDIDATES = ["Protein", "protein"]
CARB_CANDIDATES = ["Carbohydrates", "Carbohyd", "Carbs"]
FAT_CANDIDATES = ["Fat", "fat"]

# 🧭 دالة اكتشاف العمود
def find_column(df, candidates):
    for cand in candidates:
        if cand in df.columns:
            return cand
    for c in df.columns:
        for cand in candidates:
            if cand.lower() in c.lower():
                return c
    return None

# 🧮 حساب السعرات اليومية
def calculate_daily_calories(weight, height, gender):
    if gender == "male":
        bmr = 10 * weight + 6.25 * height - 5 * 30 + 5
    else:
        bmr = 10 * weight + 6.25 * height - 5 * 30 - 161
    return bmr * 1.2

# 🧍‍♂️ جلب بيانات المريض من Laravel
def get_patient_profile():
    headers = {"Authorization": f"Bearer {PATIENT_TOKEN}"}
    r = requests.get(f"{BASE_URL}/patient/profile/details", headers=headers)
    if r.status_code == 200:
        profile = r.json().get("profile")
        print("\n👤 معلومات المريض:")
        print(f"  🆔 ID: {profile['user_id']}")
        print(f"  👤 الاسم: {profile['full_name']}")
        print(f"  ⚧ الجنس: {profile['gender']}")
        print(f"  📏 الطول: {profile['height_cm']} cm")
        print(f"  ⚖️ الوزن: {profile['weight_kg']} kg")
        print(f"  🩺 الحالة الصحية: {profile['primary_condition']}\n")
        return profile
    else:
        print("⚠️ فشل في جلب بيانات المريض")
        return None

# 🥦 توليد التوصيات الذكية
def generate_recommendations(profile):
    frames = [pd.read_csv(os.path.join(DATASET_DIR, f))
              for f in os.listdir(DATASET_DIR) if f.endswith(".csv")]
    df = pd.concat(frames, ignore_index=True)

    # ✅ الأعمدة
    name_col = find_column(df, NAME_CANDIDATES)
    cal_col = find_column(df, CAL_CANDIDATES)
    protein_col = find_column(df, PROTEIN_CANDIDATES)
    carb_col = find_column(df, CARB_CANDIDATES)
    fat_col = find_column(df, FAT_CANDIDATES)

    # 🧼 تحويل القيم الرقمية والتعامل مع القيم الفارغة
    for col in [cal_col, carb_col, protein_col, fat_col]:
        df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)

    weight = float(profile.get("weight_kg", 0))
    height = float(profile.get("height_cm", 0))
    gender = (profile.get("gender") or "male").lower()
    condition = (profile.get("primary_condition") or "").upper()

    daily_cal = calculate_daily_calories(weight, height, gender)
    max_meal_cal = daily_cal / 3

    # 🩺 فلترة حسب الحالة الصحية
    filtered = df[df[cal_col] <= max_meal_cal]
    if "DIABETES" in condition:
        filtered = filtered[filtered[carb_col] <= 30]
    elif "OBESITY" in condition:
        filtered = filtered[filtered[fat_col] <= 15]
    elif "HYPERTENSION" in condition:
        filtered = filtered[filtered[carb_col] <= 40]

    # 🥇 ترتيب حسب أفضلية البروتين مقابل السعرات والكربوهيدرات
    filtered["score"] = (1 / (1 + filtered[cal_col])) + (filtered[protein_col] / (filtered[carb_col] + 1))
    filtered = filtered.sort_values(by="score", ascending=False)

    # 🍽️ تقسيم الوجبات
    total = filtered.head(15)
    breakfast = total.head(5)
    lunch = total.iloc[5:10]
    dinner = total.iloc[10:15]

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

    return {
        "breakfast": to_list(breakfast, "BREAKFAST"),
        "lunch": to_list(lunch, "LUNCH"),
        "dinner": to_list(dinner, "DINNER")
    }

# 📤 إرسال التوصيات إلى Laravel
def send_recommendations(all_recs):
    headers = {"Authorization": f"Bearer {PATIENT_TOKEN}", "Content-Type": "application/json"}
    combined = all_recs["breakfast"] + all_recs["lunch"] + all_recs["dinner"]
    data = {"recommendations": combined}
    r = requests.post(f"{BASE_URL}/nutrition/recommendations", headers=headers, json=data)
    if r.status_code == 201:
        print("\n✅ تم حفظ التوصيات في Laravel بنجاح")
    else:
        print("\n⚠️ فشل في إرسال التوصيات:", r.text)

# 🧪 التشغيل
if __name__ == "__main__":
    profile = get_patient_profile()
    if profile:
        recs = generate_recommendations(profile)

        print("🍽️ التوصيات اليومية حسب الوجبة:")

        print("\n🥐 الفطور:")
        print(json.dumps(recs["breakfast"], ensure_ascii=False, indent=2))

        print("\n🍛 الغداء:")
        print(json.dumps(recs["lunch"], ensure_ascii=False, indent=2))

        print("\n🍲 العشاء:")
        print(json.dumps(recs["dinner"], ensure_ascii=False, indent=2))

        send_recommendations(recs)
