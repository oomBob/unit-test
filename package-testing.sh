#!/bin/bash

# Script to package the WordPress theme WITH all testing dependencies
# This includes: theme files, tests, vendor, composer files, phpunit config

THEME_NAME="hello-elementor-child"
PACKAGE_NAME="hello-elementor-child-testing"
OUTPUT_DIR="dist"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
ZIP_FILE="${PACKAGE_NAME}-${TIMESTAMP}.zip"

echo "🧪 Packaging WordPress theme for unit testing..."
echo ""

# Create output directory
mkdir -p "${OUTPUT_DIR}"

# Create temporary directory for packaging
TEMP_DIR=$(mktemp -d)
echo "📦 Created temporary directory: ${TEMP_DIR}"

# Copy essential files for testing
echo "📋 Copying theme files..."
cp -r "${THEME_NAME}" "${TEMP_DIR}/"

echo "📋 Copying test files..."
cp -r "tests" "${TEMP_DIR}/"

echo "📋 Copying vendor dependencies..."
cp -r "vendor" "${TEMP_DIR}/"

echo "📋 Copying configuration files..."
cp "phpunit.xml" "${TEMP_DIR}/"
cp "composer.json" "${TEMP_DIR}/"
cp "composer.lock" "${TEMP_DIR}/"
cp ".gitignore" "${TEMP_DIR}/"

echo "📋 Copying documentation files..."
# Copy important documentation
for doc in README.md SETUP.md CONTRIBUTING.md; do
    if [ -f "$doc" ]; then
        cp "$doc" "${TEMP_DIR}/"
    fi
done

# Clean up unnecessary files from theme directory
echo "🧹 Cleaning up unnecessary files..."
find "${TEMP_DIR}/${THEME_NAME}" -type f \( \
    -name "*.bak" \
    -name ".DS_Store" \
\) -delete

find "${TEMP_DIR}" -type d -name ".git" -exec rm -rf {} + 2>/dev/null
find "${TEMP_DIR}" -type d -name ".idea" -exec rm -rf {} + 2>/dev/null
find "${TEMP_DIR}" -type d -name ".vscode" -exec rm -rf {} + 2>/dev/null

# Remove backup test files
find "${TEMP_DIR}/tests" -type f -name "*.bak" -delete

# Remove .DS_Store files
find "${TEMP_DIR}" -type f -name ".DS_Store" -delete

# Create a README for the testing package
cat > "${TEMP_DIR}/TESTING_README.md" << 'EOF'
# WordPress Theme Testing Package

This package contains everything needed to run unit tests for the Hello Elementor Child theme.

## Contents

- `hello-elementor-child/` - The theme files
- `tests/` - PHPUnit test files
- `vendor/` - Composer dependencies (PHPUnit, Mockery, Brain Monkey, etc.)
- `phpunit.xml` - PHPUnit configuration
- `composer.json` & `composer.lock` - Composer dependency definitions

## Requirements

- PHP 7.4 or higher
- Composer (optional - dependencies are already included)

## Running Tests

### Option 1: Using PHPUnit directly

```bash
./vendor/bin/phpunit
```

Or with PHP executable:
```bash
php vendor/bin/phpunit
```

### Option 2: Using Composer script (if composer is installed)

```bash
composer test
```

### Generate Coverage Report

```bash
./vendor/bin/phpunit --coverage-html coverage --coverage-clover coverage.xml
```

Or:
```bash
composer test-coverage
```

## Test Structure

- `tests/Unit/` - Unit tests for individual components
- `tests/bootstrap.php` - Test bootstrap file that sets up the testing environment

## Notes

- Tests use Brain Monkey for WordPress function mocking
- Tests use Mockery for object mocking
- Coverage reports are excluded from this package (will be generated when running tests)
EOF

# Create zip file
echo "📦 Creating zip file..."
cd "${TEMP_DIR}"
zip -r "${ZIP_FILE}" . -q
cd - > /dev/null

# Move zip to output directory
mv "${TEMP_DIR}/${ZIP_FILE}" "${OUTPUT_DIR}/"

# Clean up temporary directory
rm -rf "${TEMP_DIR}"

# Get file size
FILE_SIZE=$(du -h "${OUTPUT_DIR}/${ZIP_FILE}" | cut -f1)

echo ""
echo "✅ Testing package created successfully!"
echo "📁 Output: ${OUTPUT_DIR}/${ZIP_FILE}"
echo "📊 Size: ${FILE_SIZE}"
echo ""
echo "📝 This package includes:"
echo "   ✓ Theme files (hello-elementor-child/)"
echo "   ✓ All test files (tests/)"
echo "   ✓ Vendor dependencies (vendor/)"
echo "   ✓ PHPUnit configuration (phpunit.xml)"
echo "   ✓ Composer files (composer.json, composer.lock)"
echo ""
echo "🚀 To use:"
echo "   1. Extract the zip file"
echo "   2. Run: ./vendor/bin/phpunit"
echo ""

