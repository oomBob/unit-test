# Running Unit Tests from wp-content/themes/ Folder

This guide explains how to set up and run unit tests directly from the WordPress `wp-content/themes/` folder.

---

## 📁 Directory Structure

When set up for themes folder testing, your structure should look like:

```
wp-content/themes/
├── hello-elementor-child/        # Theme files
│   ├── functions.php
│   ├── style.css
│   └── oom/
│       └── ...
│
├── tests/                       # Test files
│   ├── Unit/                   # Unit tests
│   │   ├── FunctionsTest.php
│   │   ├── ShortcodesTest.php
│   │   └── ...
│   └── bootstrap-themes.php   # Modified bootstrap for themes folder
│
├── vendor/                     # PHPUnit and dependencies
│   └── bin/
│       └── phpunit
│
├── phpunit-themes.xml          # PHPUnit config for themes folder
├── composer.json               # Composer config
└── composer.lock               # Dependency lock
```

---

## 🚀 Setup Instructions

### Option A: Extract Testing Package to Themes Folder

1. **Extract the testing package** to `wp-content/themes/`:

   ```bash
   cd /path/to/wp-content/themes/
   unzip /path/to/hello-elementor-child-testing-*.zip
   ```

2. **Rename configuration files**:

   ```bash
   # Rename bootstrap file
   mv tests/bootstrap.php tests/bootstrap-original.php
   cp tests/bootstrap-themes.php tests/bootstrap.php
   
   # Or update phpunit config to use bootstrap-themes.php
   ```

3. **Update PHPUnit config** to use the themes bootstrap:

   Edit `phpunit-themes.xml` (or rename `phpunit.xml` to `phpunit-themes.xml`) and ensure:
   ```xml
   bootstrap="tests/bootstrap-themes.php"
   ```

4. **Update test file paths** (if needed):

   Some test files use relative paths like:
   ```php
   require_once __DIR__ . '/../../hello-elementor-child/...';
   ```
   
   For themes folder, this should be:
   ```php
   require_once __DIR__ . '/../hello-elementor-child/...';
   ```

### Option B: Manual Setup

1. **Copy theme folder** to `wp-content/themes/`:
   ```bash
   cp -r hello-elementor-child /path/to/wp-content/themes/
   ```

2. **Copy test files**:
   ```bash
   cp -r tests /path/to/wp-content/themes/
   cp -r vendor /path/to/wp-content/themes/
   cp phpunit-themes.xml /path/to/wp-content/themes/phpunit.xml
   cp composer.json /path/to/wp-content/themes/
   cp composer.lock /path/to/wp-content/themes/
   ```

3. **Update bootstrap file**:
   ```bash
   cp tests/bootstrap-themes.php /path/to/wp-content/themes/tests/bootstrap.php
   ```

4. **Update test file paths**:
   
   Search for all occurrences of:
   ```php
   __DIR__ . '/../../hello-elementor-child/
   ```
   
   Replace with:
   ```php
   __DIR__ . '/../hello-elementor-child/
   ```
   
   You can do this with:
   ```bash
   cd /path/to/wp-content/themes/
   find tests/Unit -name "*.php" -exec sed -i '' "s|__DIR__ . '/../../hello-elementor-child/|__DIR__ . '/../hello-elementor-child/|g" {} \;
   ```

---

## 🧪 Running Tests

Navigate to `wp-content/themes/` directory:

```bash
cd /path/to/wp-content/themes/
```

Then run tests:

```bash
# Option 1: Use themes-specific config
./vendor/bin/phpunit -c phpunit-themes.xml

# Option 2: If you renamed phpunit-themes.xml to phpunit.xml
./vendor/bin/phpunit

# Option 3: With PHP executable
php vendor/bin/phpunit -c phpunit-themes.xml
```

### Generate Coverage

```bash
./vendor/bin/phpunit -c phpunit-themes.xml --coverage-html coverage --coverage-clover coverage.xml
```

---

## ⚙️ Path Differences

### Root-Level Structure (Default)
```
project-root/
├── hello-elementor-child/
├── tests/Unit/
│   └── SomeTest.php
├── vendor/
└── phpunit.xml

# In tests: __DIR__ . '/../../hello-elementor-child/'
# (tests/Unit -> tests -> root -> hello-elementor-child)
```

### Themes Folder Structure
```
wp-content/themes/
├── hello-elementor-child/
├── tests/Unit/
│   └── SomeTest.php
├── vendor/
└── phpunit-themes.xml

# In tests: __DIR__ . '/../hello-elementor-child/'
# (tests/Unit -> tests -> themes -> hello-elementor-child)
```

---

## 🔧 Alternative: Create a Helper Script

You can create a script to automatically adjust paths when running from themes folder:

```php
<?php
// tests/path-helper.php

if (!defined('THEME_TEST_PATH')) {
    // Detect if we're running from themes folder
    $themePath = __DIR__ . '/../hello-elementor-child';
    
    if (file_exists($themePath)) {
        // We're in themes folder
        define('THEME_TEST_PATH', __DIR__ . '/../hello-elementor-child');
    } else {
        // We're in root-level structure
        define('THEME_TEST_PATH', __DIR__ . '/../../hello-elementor-child');
    }
}

function get_theme_path($relativePath = '') {
    return THEME_TEST_PATH . ($relativePath ? '/' . ltrim($relativePath, '/') : '');
}
```

Then use it in test files:
```php
require_once get_theme_path('oom/oom-custom-shortcode.php');
```

---

## ✅ Advantages of Themes Folder Testing

1. **Closer to WordPress**: Tests run in actual WordPress theme location
2. **Easier Integration**: Can test with actual WordPress if needed
3. **Simpler Deployment**: Everything in one place

---

## ❌ Considerations

1. **Path Updates Required**: Need to update test file paths
2. **Bootstrap Adjustment**: Need themes-specific bootstrap
3. **Not Standard**: Most PHPUnit setups run from project root

---

## 🎯 Recommended Approach

**For most users**: Stick with the default root-level structure (as described in the main guide). It's cleaner and doesn't require path modifications.

**For themes folder testing**: Use the alternative configuration files provided:
- `phpunit-themes.xml`
- `tests/bootstrap-themes.php`
- Update test paths as needed

---

## 🔍 Quick Check: Which Structure Am I Using?

Run this command from your test directory:

```bash
# From tests/Unit/
if [ -f "../../hello-elementor-child/functions.php" ]; then
    echo "Root-level structure detected"
elif [ -f "../hello-elementor-child/functions.php" ]; then
    echo "Themes folder structure detected"
else
    echo "Theme folder not found in expected location"
fi
```

---

**Summary**: Yes, you can run tests from `wp-content/themes/` folder, but it requires path adjustments in bootstrap.php and test files. The configuration files above (`phpunit-themes.xml` and `bootstrap-themes.php`) are provided to help with this setup.

