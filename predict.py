import pandas as pd
from textblob import TextBlob
from sqlalchemy import create_engine

# Function to fetch comments from MySQL database
def get_comments():
    print("Fetching counselor comments...")

    try:
        # Update credentials as needed
        engine = create_engine("mysql+mysqlconnector://root:D_vine%40245@localhost/ppddm")

        query = """
            SELECT counselor_comments
            FROM counseling
            WHERE counselor_comments IS NOT NULL
        """

        df = pd.read_sql(query, engine)
        print(f"Fetched {len(df)} comments.")
        return df

    except Exception as e:
        print("Error fetching comments:", e)
        return pd.DataFrame()

# Function to analyze sentiment
def analyze_sentiment(comment):
    if pd.isna(comment) or comment.strip() == "":
        return "Neutral"
    
    blob = TextBlob(comment)
    polarity = blob.sentiment.polarity

    if polarity > 0.1:
        return "Positive"
    elif polarity < -0.1:
        return "Negative"
    else:
        return "Neutral"

# Main function
def main():
    comments_df = get_comments()

    if comments_df.empty:
        print("No comments to analyze.")
        return

    print("Analyzing sentiments...")
    comments_df['Sentiment'] = comments_df['counselor_comments'].apply(analyze_sentiment)

    print("\nResults:")
    print(comments_df)

    # Optional: Save to CSV
    comments_df.to_csv("sentiment_results.csv", index=False)
    print("\nSentiment analysis results saved to 'sentiment_results.csv'.")

if __name__ == "__main__":
    main()
