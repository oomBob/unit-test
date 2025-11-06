#!/bin/bash

# Script to package the WordPress theme for distribution
# This excludes test files, vendor, coverage, logs, and other development files

THEME_NAME="hello-elementor-child"
THEME_DIR="hello-elementor-child"
PACKAGE_NAME="hello-elementor-child-standalone"
OUTPUT_DIR="dist"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
ZIP_FILE="${PACKAGE_NAME}-${TIMESTAMP}.zip"

echo "🎨 Packaging WordPress theme..."
echo ""

# Create output directory
mkdir -p "${OUTPUT_DIR}"

# Create temporary directory for packaging
TEMP_DIR=$(mktemp -d)
echo "📦 Created temporary directory: ${TEMP_DIR}"

# Copy theme directory
echo "📋 Copying theme files..."
cp -r "${THEME_DIR}" "${TEMP_DIR}/${THEME_NAME}"

# Remove any test files or development files from the theme directory itself
echo "🧹 Cleaning up development files from theme..."
find "${TEMP_DIR}/${THEME_NAME}" -type f \( \
    -name "*.bak" \
    -name "*.test.php" \
    -name "phpunit.xml" \
    -name "composer.json" \
    -name "composer.lock" \
    -name ".gitignore" \
    -name "README.md" \
    -name "*.md" \
\) -delete

# Remove any test directories within the theme
find "${TEMP_DIR}/${THEME_NAME}" -type d \( \
    -name "tests" \
    -name "vendor" \
    -name "coverage" \
    -name "coverage_single" \
    -name "logs" \
    -name ".git" \
    -name ".idea" \
    -name ".vscode" \
\) -exec rm -rf {} + 2>/dev/null

# Create zip file
echo "📦 Creating zip file..."
cd "${TEMP_DIR}"
zip -r "${ZIP_FILE}" "${THEME_NAME}" -q
cd - > /dev/null

# Move zip to output directory
mv "${TEMP_DIR}/${ZIP_FILE}" "${OUTPUT_DIR}/"

# Clean up temporary directory
rm -rf "${TEMP_DIR}"

echo ""
echo "✅ Theme packaged successfully!"
echo "📁 Output: ${OUTPUT_DIR}/${ZIP_FILE}"
echo ""
echo "🚀 You can now upload this zip file to WordPress via:"
echo "   Appearance > Themes > Add New > Upload Theme"
echo ""

