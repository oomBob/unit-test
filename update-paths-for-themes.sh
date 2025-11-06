#!/bin/bash

# Script to update test file paths for themes folder structure
# This converts root-level paths to themes folder paths

THEMES_DIR="${1:-wp-content/themes}"
TEST_DIR="${THEMES_DIR}/tests/Unit"

if [ ! -d "$TEST_DIR" ]; then
    echo "Error: Test directory not found: $TEST_DIR"
    echo "Usage: ./update-paths-for-themes.sh [themes-directory]"
    echo "Example: ./update-paths-for-themes.sh wp-content/themes"
    exit 1
fi

echo "🔄 Updating test file paths for themes folder structure..."
echo "📁 Target directory: $THEMES_DIR"
echo ""

# Count files that will be updated
FILE_COUNT=$(find "$TEST_DIR" -name "*.php" -type f | wc -l | tr -d ' ')

echo "📋 Found $FILE_COUNT test files"
echo ""

# Update paths in test files
# Change: __DIR__ . '/../../hello-elementor-child/
# To:     __DIR__ . '/../hello-elementor-child/

UPDATED=0

while IFS= read -r file; do
    if grep -q "__DIR__ . '/../../hello-elementor-child/" "$file" 2>/dev/null; then
        # Use different sed syntax for macOS vs Linux
        if [[ "$OSTYPE" == "darwin"* ]]; then
            sed -i '' "s|__DIR__ . '/../../hello-elementor-child/|__DIR__ . '/../hello-elementor-child/|g" "$file"
        else
            sed -i "s|__DIR__ . '/../../hello-elementor-child/|__DIR__ . '/../hello-elementor-child/|g" "$file"
        fi
        UPDATED=$((UPDATED + 1))
        echo "  ✓ Updated: $(basename "$file")"
    fi
done < <(find "$TEST_DIR" -name "*.php" -type f)

echo ""
if [ $UPDATED -gt 0 ]; then
    echo "✅ Updated $UPDATED test file(s)"
else
    echo "ℹ️  No files needed updating (may already be updated)"
fi

echo ""
echo "📝 Next steps:"
echo "   1. Ensure bootstrap-themes.php is in $THEMES_DIR/tests/"
echo "   2. Use phpunit-themes.xml config when running tests"
echo "   3. Run tests from $THEMES_DIR directory"
echo ""

