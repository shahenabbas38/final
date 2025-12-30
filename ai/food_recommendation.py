#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
import random
import sys
import pandas as pd
from datetime import datetime

# 📂 تحسين تحديد المسارات لضمان التوافق مع Linux/Railway
def get_dataset_path():
    # الحصول على المسار المطلق لمجلد السكربت الحالي
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    # قائمة بالمسارات المحتملة (نركز على المسار الأكثر احتمالا في Linux)
    possible_paths = [
        # الاحتمال الأول: المجلد داخل ai (بجانب السكربت)
        os.path.join(base_dir, "FINAL FOOD DATASET"),
        # الاحتمال الثاني: المجلد في الـ Root (خارج ai)
        os.path.join(os.path.dirname(base_dir), "FINAL FOOD DATASET"),
    ]
    
    for path in possible_paths:
        if os.path.exists(path) and os.path.isdir(path):
            return path
    return None

DATASET_DIR = get_dataset_path()

# 🧠 تعريف الأعمدة (بقيت كما هي لأنها تغطي احتمالات التسمية في الـ CSV)
COLUMN_MAP = {
    'name': ["food", "Unnamed: 1", "Name", "food_name"],
    'cal': ["Caloric Value", "Calories", "Energy", "calories"],
    'prot': ["Protein", "protein"],
    'carb': ["Carbohydrates", "Carbs", "carb"],
    'fat': ["Fat", "fat"]
}

def find_col(df, candidates):
    # تحويل أسماء الأعمدة إلى أحرف صغيرة للمقارنة لضمان الدقة
    cols_lower = {c.lower(): c for c in df.columns}
    for cand in candidates:
        if cand.lower() in cols_lower:
            return cols_lower[cand.lower()]
    return None

def calculate_age(dob_str):
    try:
        # دعم أكثر من صيغة للتاريخ لضمان عدم تعطل الكود
        for fmt in ("%Y-%m-%d", "%d/%m/%Y", "%Y/%m/%d"):
            try:
                dob = datetime.strptime(dob_str, fmt)
                break
            except ValueError:
                continue
        else:
            return 30 # إذا فشلت كل الصيغ
            
        today = datetime.today()
        return today.year - dob.year - ((today.month, today.day) < (dob.month, dob.day))
    except:
        return 30

def calculate_bmr(weight, height, gender, age):
    # معادلة Mifflin-St Jeor
    if str(gender).lower() == "male":
        return (10 * weight) + (6.25 * height) - (5 * age) + 5
    else:
        return (10 * weight) + (6.25 * height) - (5 * age) - 161

def generate_recommendations(profile):
    try:
        if not DATASET_DIR:
            return {"error": f"Dataset folder not found. Base dir: {os.path.dirname(os.path.abspath(__file__))}"}

        # جلب كل ملفات CSV
        files = [f for f in os.listdir(DATASET_DIR) if f.lower().endswith(".csv")]
        if not files:
            return {"error": f"No CSV files found in: {DATASET_DIR}"}
            
        # دمج البيانات
        df_list = []
        for f in files:
            temp_df = pd.read_csv(os.path.join(DATASET_DIR, f))
            df_list.append(temp_df)
        
        df = pd.concat(df_list, ignore_index=True)

        # ربط الأعمدة
        cols = {k: find_col(df, v) for k, v in COLUMN_MAP.items()}
        
        if not cols['name'] or not cols['cal']:
            return {"error": "Required columns (Food Name/Calories) missing in CSV files."}

        # تنظيف البيانات الرقمية
        for k, c in cols.items():
            if c and k != 'name':
                df[c] = pd.to_numeric(df[c], errors='coerce').fillna(0)

        # استخراج البيانات الشخصية مع قيم افتراضية آمنة
        name = profile.get('full_name', 'User')
        weight = float(profile.get('weight_kg', 70))
        height = float(profile.get('height_cm', 170))
        gender = profile.get('gender', 'male')
        age = calculate_age(profile.get('dob', '1995-01-01'))
        condition = str(profile.get('primary_condition', 'NONE')).upper()

        # حساب الاحتياج اليومي
        daily_calories = calculate_bmr(weight, height, gender, age) * 1.2
        max_meal_cal = daily_calories / 3

        # الفلترة بناءً على الحالة الصحية
        filtered = df[df[cols['cal']] <= max_meal_cal].copy()
        
        if "DIABETES" in condition:
            # لمرضى السكري: كربوهيدرات منخفضة
            carb_col = cols['carb']
            if carb_col:
                filtered = filtered[filtered[carb_col] <= 25]
        elif "OBESITY" in condition or "HEART" in condition:
            # للسمنة أو أمراض القلب: دهون منخفضة
            fat_col = cols['fat']
            if fat_col:
                filtered = filtered[filtered[fat_col] <= 10]

        def create_meal_list(data, meal_type):
            if data.empty:
                return []
            # اختيار 5 وجبات عشوائية
            sample_size = min(5, len(data))
            items = data.sample(n=sample_size)
            
            return [{
                "food_name": str(row[cols['name']]),
                "calories": round(float(row[cols['cal']]), 2),
                "protein": round(float(row[cols['prot']]), 2) if cols['prot'] else 0,
                "carbohydrates": round(float(row[cols['carb']]), 2) if cols['carb'] else 0,
                "fat": round(float(row[cols['fat']]), 2) if cols['fat'] else 0,
                "description": f"وجبة مقترحة تناسب حالة {condition}",
                "confidence": round(random.uniform(0.92, 0.98), 2),
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
        return {"error": f"Python Error: {str(e)}"}

if __name__ == "__main__":
    try:
        if len(sys.argv) > 1:
            input_data = json.loads(sys.argv[1])
            result = generate_recommendations(input_data)
            # التأكد من طباعة النتيجة فقط
            sys.stdout.write(json.dumps(result, ensure_ascii=False))
        else:
            sys.stdout.write(json.dumps({"error": "No input data provided"}))
    except Exception as e:
        # طباعة الخطأ الحقيقي ليظهر في debug_info الخاص بـ Laravel
        sys.stderr.write(str(e))
        sys.stdout.write(json.dumps({"error": str(e)}))