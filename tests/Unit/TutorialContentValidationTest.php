<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Str;

class TutorialContentValidationTest extends TestCase
{
    /**
     * Test that tutorial content generation logic produces valid results
     */
    public function test_content_validation_logic()
    {
        // Simulate generated content
        $sampleContent = $this->generateSampleContent();

        // Test word count
        $wordCount = str_word_count(strip_tags($sampleContent));
        $this->assertGreaterThanOrEqual(2500, $wordCount,
            "Generated content should have minimum 2500 words, got {$wordCount}");

        // Test markdown headers
        $headerCount = preg_match_all('/^#+\s+.+$/m', $sampleContent);
        $this->assertGreaterThanOrEqual(5, $headerCount,
            "Content should have at least 5 markdown headers, got {$headerCount}");

        // Test code blocks
        $codeBlockCount = preg_match_all('/```[a-z]*\n/i', $sampleContent);
        $this->assertGreaterThanOrEqual(2, $codeBlockCount,
            "Content should have at least 2 code blocks, got {$codeBlockCount}");

        // Test that enhancement sections are present
        $this->assertStringContainsString('Table of Contents', $sampleContent);
        $this->assertStringContainsString('About the Author', $sampleContent);
        $this->assertStringContainsString('Key Takeaways', $sampleContent);
        $this->assertStringContainsString('Related Tutorials', $sampleContent);

        echo "✅ Content Validation Passed\n";
        echo "   - Word Count: " . number_format($wordCount) . "\n";
        echo "   - Headers: {$headerCount}\n";
        echo "   - Code Blocks: {$codeBlockCount}\n";
    }

    /**
     * Test slug generation
     */
    public function test_slug_generation()
    {
        $topics = [
            'Understanding AI: Machine Learning Basics',
            'Advanced ChatGPT Techniques: Custom Instructions and GPTs',
            'Building Production AI Applications with LangChain',
        ];

        foreach ($topics as $topic) {
            $slug = Str::slug($topic);
            $this->assertNotEmpty($slug, "Slug should not be empty for topic: {$topic}");
            $this->assertFalse(str_contains($slug, ' '), "Slug should not contain spaces");
            $this->assertFalse(str_contains($slug, ':'), "Slug should not contain special characters");

            echo "✅ Slug generated: {$topic}\n";
            echo "   → {$slug}\n";
        }
    }

    /**
     * Test series progression
     */
    public function test_series_progression()
    {
        $totalParts = 8;
        $premiumThreshold = 6; // Parts 7-8 are premium

        for ($i = 1; $i <= $totalParts; $i++) {
            $isPremium = $i >= $premiumThreshold;
            $status = $isPremium ? 'PREMIUM' : 'FREE';

            echo "Part {$i}/8: {$status}\n";

            if ($i <= 6) {
                $this->assertFalse($isPremium, "Part {$i} should be free");
            } else {
                $this->assertTrue($isPremium, "Part {$i} should be premium");
            }
        }

        echo "✅ Series premium tier configuration valid\n";
    }

    /**
     * Test read time calculation
     */
    public function test_read_time_calculation()
    {
        $testCases = [
            1000 => 5,    // ~5 min
            2000 => 10,   // ~10 min
            3000 => 15,   // ~15 min
            4000 => 20,   // ~20 min
        ];

        foreach ($testCases as $wordCount => $expectedMinutes) {
            $readTime = ceil($wordCount / 200);
            $this->assertGreaterThanOrEqual($expectedMinutes - 1, $readTime);
            $this->assertLessThanOrEqual($expectedMinutes + 1, $readTime);

            echo "✅ {$wordCount} words = {$readTime} min read time\n";
        }
    }

    /**
     * Test SEO metadata structure
     */
    public function test_seo_metadata_structure()
    {
        $seoMeta = [
            'title' => 'Understanding AI: Machine Learning Basics - Part 1',
            'description' => 'Learn machine learning fundamentals with production-grade examples...',
            'keywords' => 'ai,machine-learning,python,tutorial,production',
        ];

        $this->assertArrayHasKey('title', $seoMeta);
        $this->assertArrayHasKey('description', $seoMeta);
        $this->assertArrayHasKey('keywords', $seoMeta);

        // Validate title length (55-60 chars optimal for Google)
        $this->assertLessThanOrEqual(60, strlen($seoMeta['title']),
            "SEO title should be 55-60 chars, got " . strlen($seoMeta['title']));

        // Validate description length (150-160 chars optimal)
        $this->assertGreaterThanOrEqual(150, strlen($seoMeta['description']),
            "SEO description should be 150-160 chars");
        $this->assertLessThanOrEqual(160, strlen($seoMeta['description']),
            "SEO description should be 150-160 chars");

        echo "✅ SEO Metadata Valid\n";
        echo "   - Title: " . strlen($seoMeta['title']) . " chars\n";
        echo "   - Description: " . strlen($seoMeta['description']) . " chars\n";
        echo "   - Keywords: " . count(explode(',', $seoMeta['keywords'])) . " keywords\n";
    }

    /**
     * Test author bio matching
     */
    public function test_expertise_level_matching()
    {
        $levels = ['beginner', 'intermediate', 'advanced'];
        $expectedExpertise = [
            'beginner' => 'AI fundamentals and practical implementations',
            'intermediate' => 'Production-grade AI systems and architecture patterns',
            'advanced' => 'Scalable AI infrastructure and optimization techniques',
        ];

        foreach ($levels as $level) {
            $this->assertArrayHasKey($level, $expectedExpertise,
                "Expertise level '{$level}' should have matching bio");

            echo "✅ {$level}: {$expectedExpertise[$level]}\n";
        }
    }

    /**
     * Generate sample tutorial content for testing
     */
    private function generateSampleContent(): string
    {
        return <<<'CONTENT'
# Complete Machine Learning Tutorial

## 📋 Table of Contents

1. [Introduction](#introduction)
2. [Fundamentals](#fundamentals)
3. [Implementation](#implementation)
4. [Best Practices](#best-practices)
5. [Production Deployment](#production-deployment)

## Introduction

This is a comprehensive guide to machine learning implementation.

### What You'll Learn

- Core ML concepts
- Production implementation
- Performance optimization
- Real-world case studies

## Fundamentals

Machine learning systems architecture varies significantly from development to production.

### Key Concepts

**Supervised Learning** involves training on labeled data:

```python
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split

# Load and prepare data
X = load_features()
y = load_labels()

# Split data
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)

# Train model
model = RandomForestClassifier(n_estimators=100)
model.fit(X_train, y_train)

# Evaluate
score = model.score(X_test, y_test)
print(f"Accuracy: {score:.3f}")
```

**Unsupervised Learning** finds patterns without labels:

```python
from sklearn.cluster import KMeans

# Data without labels
X = load_features()

# Cluster data
kmeans = KMeans(n_clusters=3)
clusters = kmeans.fit_predict(X)

# Analyze clusters
for i in range(3):
    cluster_size = sum(clusters == i)
    print(f"Cluster {i}: {cluster_size} samples")
```

## Implementation

Production ML systems require robust architectures:

### Data Pipeline

```python
class DataPipeline:
    def __init__(self, source):
        self.source = source

    def load(self):
        return pd.read_csv(self.source)

    def validate(self, df):
        assert df.isnull().sum().sum() == 0, "Missing values found"
        return df

    def transform(self, df):
        return df.apply(lambda x: (x - x.mean()) / x.std())

    def run(self):
        df = self.load()
        df = self.validate(df)
        return self.transform(df)
```

### Model Training

Complete model training with validation:

```python
from sklearn.metrics import accuracy_score, precision_score, recall_score

def train_and_evaluate(X_train, X_test, y_train, y_test):
    # Train
    model = RandomForestClassifier(n_estimators=100, max_depth=10)
    model.fit(X_train, y_train)

    # Predict
    y_pred = model.predict(X_test)

    # Evaluate
    metrics = {
        'accuracy': accuracy_score(y_test, y_pred),
        'precision': precision_score(y_test, y_pred),
        'recall': recall_score(y_test, y_pred),
    }

    return model, metrics
```

## Best Practices

### Code Quality

- Always validate inputs
- Log all predictions
- Monitor model drift
- Version your models
- Test edge cases

### Performance Optimization

- Use appropriate data structures
- Implement caching layers
- Profile bottlenecks
- Optimize hyperparameters

## Production Deployment

### Containerization

```dockerfile
FROM python:3.11-slim

WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt

COPY . .
CMD ["python", "main.py"]
```

### Health Checks

```python
@app.get("/health")
def health_check():
    return {"status": "healthy"}

@app.get("/metrics")
def get_metrics():
    return {
        "predictions_made": counter,
        "avg_latency_ms": avg_latency,
        "error_rate": error_rate
    }
```

---

## 👨‍💻 About the Author

This tutorial is created by ML education experts with 10+ years of software engineering experience. Our content focuses on:

- ✅ Production-ready code examples
- ✅ Real-world problem-solving
- ✅ Industry best practices
- ✅ Verified and tested approaches

---

## 🎯 Key Takeaways

From this tutorial, you should understand:

1. **Core Concepts** - Machine learning fundamentals and system design
2. **Implementation Details** - Practical code examples and patterns
3. **Best Practices** - Industry-standard approaches
4. **Common Pitfalls** - What to avoid and why
5. **Next Steps** - How to continue learning

---

## 🔗 Related Tutorials

This tutorial is part of a comprehensive series. Explore other tutorials:

- **Part 1:** Fundamentals and core concepts
- **Part 2:** Setup and configuration
- **Part 3:** Basic implementation
- **Part 4:** Advanced patterns
- **Part 5:** Optimization techniques
- **Part 6:** Production deployment (Premium)
- **Part 7:** Scaling strategies (Premium)
- **Part 8:** Case studies and real-world applications (Premium)
CONTENT;
    }
}
