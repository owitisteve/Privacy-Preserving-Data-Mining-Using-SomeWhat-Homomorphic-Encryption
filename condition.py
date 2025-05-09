import matplotlib
matplotlib.use('TkAgg')  # Use the TkAgg backend for interactive plotting

import pandas as pd
import mysql.connector
from textblob import TextBlob
import matplotlib.pyplot as plt
import seaborn as sns

# ========== DB CONNECTION ==========
print("Connecting to database...")
try:
    conn = mysql.connector.connect(
        host='localhost',
        user='root',          # <--- Change this
        password='D_vine@245',  # <--- Change this
        database='ppddm'    # <--- Change this
    )
except mysql.connector.Error as err:
    print(f"Connection failed: {err}")
    exit()

# ========== FETCH DATA ==========
print("Fetching counselor comments...")
query = """
SELECT counselor_comments, department, year_of_study, created_at 
FROM counseling 
WHERE counselor_comments IS NOT NULL
"""

try:
    df = pd.read_sql(query, conn)
    conn.close()
except Exception as e:
    print(f"Error fetching data: {e}")
    exit()

if df.empty:
    print("No counselor comments found.")
    exit()

# ========== SENTIMENT ANALYSIS ==========
print("Analyzing sentiments...")

def analyze_sentiment(text):
    polarity = TextBlob(text).sentiment.polarity
    if polarity > 0.2:
        return "Positive"
    elif polarity < -0.1:
        return "Negative"
    else:
        return "Neutral"

df['sentiment'] = df['counselor_comments'].apply(analyze_sentiment)

# ========== CLEAN & TRANSFORM ==========
df['created_at'] = pd.to_datetime(df['created_at'], errors='coerce')
df['month'] = df['created_at'].dt.to_period('M')

# ========== VISUALIZATION ==========
sns.set(style="whitegrid")

# --- Sentiment Distribution ---
plt.figure(figsize=(6,4))
sns.countplot(data=df, x='sentiment', hue='sentiment', palette='Set2', legend=False)
plt.title('Sentiment Distribution')
plt.xlabel('Sentiment')
plt.ylabel('Number of Comments')
plt.tight_layout()
plt.show()

# --- Sentiment Trend Over Time ---
plt.figure(figsize=(10,5))
monthly = df.groupby(['month', 'sentiment']).size().unstack().fillna(0)
monthly.plot(kind='line', marker='o')
plt.title('Sentiment Trend Over Time')
plt.xlabel('Month')
plt.ylabel('Number of Comments')
plt.grid(True)
plt.tight_layout()
plt.show()

# --- Sentiment by Department ---
plt.figure(figsize=(10,5))
dept = df.groupby(['department', 'sentiment']).size().unstack().fillna(0)
dept.plot(kind='bar', stacked=True, colormap='Accent')
plt.title('Sentiment by Department')
plt.xlabel('Department')
plt.ylabel('Number of Comments')
plt.xticks(rotation=45)
plt.tight_layout()
plt.show()

# --- Sentiment by Year of Study ---
plt.figure(figsize=(8,5))
year = df.groupby(['year_of_study', 'sentiment']).size().unstack().fillna(0)
year.plot(kind='bar', stacked=True, colormap='coolwarm')
plt.title('Sentiment by Year of Study')
plt.xlabel('Year')
plt.ylabel('Number of Comments')
plt.xticks(rotation=45)
plt.tight_layout()
plt.show()
