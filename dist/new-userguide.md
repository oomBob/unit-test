# Child Theme Unit Testing - User Guide

Comprehensive unit testing for the **hello-elementor-child** WordPress theme with integrated tests and SonarCloud support.

---

## 📦 Installation & Setup

### Step 1: Download the Testing Package

Download the **`hello-elementor-child-testing-*.zip`** file from the `dist/` directory. This package contains everything needed to run unit tests for the theme.

### Step 2: Extract the Package

Extract the zip file to your desired location (e.g., `/path/to/your-project/`):

```bash
unzip hello-elementor-child-testing-*.zip -d /path/to/your-project/
cd /path/to/your-project/
```

After extraction, you'll have a directory structure like this:

```
your-project/
├── hello-elementor-child/    # Theme files
├── tests/                     # Test files
│   ├── Unit/                 # Unit tests
│   └── bootstrap.php         # Test bootstrap
├── vendor/                    # PHPUnit and dependencies
├── phpunit.xml               # PHPUnit configuration
├── composer.json             # Composer configuration
├── composer.lock             # Dependency lock file
└── TESTING_README.md         # Testing documentation
```

### Step 3: Replace Your Theme Folder (IMPORTANT!)

**⚠️ IMPORTANT:** If you already have a `hello-elementor-child` folder in your WordPress themes directory, you need to **replace it** with the one from the testing package.

#### Option A: If running tests on a local WordPress installation:

1. **Backup your current theme** (if it has customizations):
   ```bash
   cp -r /path/to/wp-content/themes/hello-elementor-child /path/to/backup/
   ```

2. **Replace the theme folder**:
   ```bash
   # Remove old theme folder
   rm -rf /path/to/wp-content/themes/hello-elementor-child
   
   # Copy the new theme from extracted package
   cp -r /path/to/your-project/hello-elementor-child /path/to/wp-content/themes/
   ```

3. **Copy test files** to your project root (same level as wp-content):
   ```bash
   # Copy tests, vendor, and config files
   cp -r /path/to/your-project/tests /path/to/wp-content/../
   cp -r /path/to/your-project/vendor /path/to/wp-content/../
   cp /path/to/your-project/phpunit.xml /path/to/wp-content/../
   cp /path/to/your-project/composer.json /path/to/wp-content/../
   ```

#### Option B: If running tests in a standalone directory (Recommended for testing):

Simply run tests from the extracted directory structure. The tests are integrated with the theme, so they work together in the same project structure.

### Step 4: Verify Structure

After setup, you should be able to run tests from the **root directory** (where `phpunit.xml` is located), not from inside the `hello-elementor-child/` theme folder.

```bash
cd /path/to/your-project/  # Root directory with phpunit.xml
ls -la  # Should show: hello-elementor-child/, tests/, vendor/, phpunit.xml
```

---

## 🚀 Running Tests

### Prerequisites

- **PHP 7.4 or higher** (`php -v`)
- **Composer** (optional - dependencies are already included in the package)

### Quick Start

Navigate to the **root directory** of your extracted package (the directory containing `phpunit.xml`):

```bash
cd /path/to/your-project/  # Go to root directory
```

Then run tests:

```bash
# Option 1: Direct PHPUnit (recommended)
./vendor/bin/phpunit

# Option 2: With PHP executable
php vendor/bin/phpunit

# Option 3: Using Composer (if composer is installed locally)
composer test
```

### Generate Coverage Reports

```bash
# HTML coverage report
./vendor/bin/phpunit --coverage-html coverage --coverage-clover coverage.xml

# Or with Composer
composer test-coverage
```

After running, open `coverage/index.html` in your browser to view detailed coverage reports.

---

## 📁 Project Structure

The testing package follows this structure:

```
.
├── hello-elementor-child/          # WordPress theme files
│   ├── functions.php               # Main theme functions
│   ├── style.css                  # Theme stylesheet
│   ├── oom/                       # Custom theme functionality
│   │   ├── oom-ajax.php
│   │   ├── oom-custom-shortcode.php
│   │   ├── oom-elementor.php
│   │   ├── oom-global-shortcode.php
│   │   ├── oom-optimization-security.php
│   │   ├── oom-rest-api-v1.php
│   │   ├── oom-theme-options.php
│   │   └── widgets/               # Theme widgets
│   └── ...
│
├── tests/                          # Test files
│   ├── Unit/                       # Unit test files
│   │   ├── CustomShortcodesTest.php
│   │   ├── FormDatabaseTest.php
│   │   ├── FormFunctionsTest.php
│   │   ├── FormMainTest.php
│   │   ├── FormSubmissionPageTest.php
│   │   ├── FormWidgetTest.php
│   │   ├── FunctionsTest.php
│   │   ├── OptimizationTest.php
│   │   ├── SecurityTest.php
│   │   ├── ShortcodesTest.php
│   │   ├── TableWidgetTest.php
│   │   └── ThemeOptionsTest.php
│   └── bootstrap.php               # Test bootstrap file
│
├── vendor/                         # Composer dependencies
│   └── bin/
│       └── phpunit                 # PHPUnit executable
│
├── phpunit.xml                     # PHPUnit configuration
├── composer.json                   # Composer dependencies
├── composer.lock                   # Dependency lock file
└── TESTING_README.md               # Testing documentation
```

---

## 🧪 Test Files Overview

| Test File | Purpose | Coverage |
|-----------|---------|----------|
| `FunctionsTest.php` | Theme functions, hooks, filters | ~85% |
| `ShortcodesTest.php` | Global shortcodes (ratings, sliders) | ~90% |
| `CustomShortcodesTest.php` | Custom shortcodes (forms, pricing) | ~60% |
| `SecurityTest.php` | Security features & optimizations | ~95% |
| `OptimizationTest.php` | Performance optimizations | ~90% |
| `ThemeOptionsTest.php` | Admin theme options page | ~80% |
| `FormWidgetTest.php` | Elementor form widget | ~75% |
| `FormMainTest.php` | Form main functionality | ~70% |
| `FormFunctionsTest.php` | Form helper functions | ~80% |
| `FormDatabaseTest.php` | Form database operations | ~85% |
| `FormSubmissionPageTest.php` | Form submission page | ~75% |
| `TableWidgetTest.php` | Table widget functionality | ~65% |

---

## ⚙️ Configuration

### PHPUnit Configuration (`phpunit.xml`)

The `phpunit.xml` file is pre-configured for testing the child theme:

- **Bootstrap**: `tests/bootstrap.php`
- **Test Suite**: `tests/Unit/`
- **Coverage**: Includes `hello-elementor-child/` directory
- **Excludes**: Assets, template-parts, CSS files from coverage

You can customize it if needed, but the default configuration works for most use cases.

### Test Bootstrap (`tests/bootstrap.php`)

The bootstrap file:
- Loads Composer autoloader
- Sets up WordPress test constants (if needed)
- Initializes Brain Monkey for WordPress function mocking
- Defines theme constants

---

## 🎯 What's Being Tested

### Theme Functions
✅ Theme version constants  
✅ CSS/JS enqueuing  
✅ AJAX object localization  
✅ Post views tracking  
✅ Security headers  
✅ Menu registration  
✅ Auto-update filters

### Shortcodes
✅ Star ratings (`oom_ratings`)  
✅ Hero sliders (`oom_hero_slider`)  
✅ Rental forms  
✅ Pricing displays  
✅ Custom menus  
✅ Current year shortcode

### Security & Optimization
✅ Block CSS removal  
✅ Emoji script disabling  
✅ Pingback blocking  
✅ User enumeration blocking  
✅ REST API protection  
✅ Theme/plugin editor access control

### Widgets
✅ Form widget functionality  
✅ Form database operations  
✅ Form submission handling  
✅ Table widget functionality

### Theme Options
✅ GTM code settings  
✅ Table/Form status toggles  
✅ Google Places API configuration  
✅ Security deposit settings  
✅ Blockout dates validation

---

## 📊 Running Specific Tests

### Run a Single Test File

```bash
./vendor/bin/phpunit tests/Unit/FunctionsTest.php
```

### Run a Specific Test Method

```bash
./vendor/bin/phpunit --filter test_theme_version_constant_defined
```

### Run with Verbose Output

```bash
./vendor/bin/phpunit --testdox
```

### Stop on First Failure

```bash
./vendor/bin/phpunit --stop-on-failure
```

---

## 🌐 SonarCloud Integration

### Prerequisites

1. **SonarCloud Account**: Sign up at [sonarcloud.io](https://sonarcloud.io)
2. **GitHub Repository**: Your code should be in a GitHub repository
3. **SonarCloud Token**: Generate in SonarCloud → My Account → Security

### Setup Steps

#### 1. Create SonarCloud Project

1. Go to SonarCloud.io and sign in with GitHub
2. Click **"Create new project"**
3. Select your organization and repository
4. **Copy the project key and organization name**

#### 2. Update Configuration

If you have a `sonar-project.properties` file, update it:

```properties
sonar.projectKey=your-organization_unit-test
sonar.organization=your-organization
```

Replace:
- `your-organization` with your SonarCloud organization name
- `unit-test` with your actual project key

#### 3. Add SonarCloud Token to GitHub Secrets

1. Go to your GitHub repository
2. **Settings** → **Secrets and variables** → **Actions**
3. Click **"New repository secret"**
4. Add:
   - **Name**: `SONAR_TOKEN`
   - **Value**: (Paste your SonarCloud token)

#### 4. Generate Coverage Reports for SonarCloud

Run tests with coverage:

```bash
./vendor/bin/phpunit --coverage-clover coverage.xml
```

This generates `coverage.xml` that SonarCloud can read.

### View Results

After pushing to GitHub and running CI/CD:
- View results at: `https://sonarcloud.io/dashboard?id=your-project-key`
- Coverage reports appear in the **"Overall Code"** tab

---

## 🐛 Troubleshooting

### PHPUnit Not Found

**Error:**
```
command not found: phpunit
```

**Solution:**
```bash
# Dependencies are already included in the package
# Just use the vendor path:
./vendor/bin/phpunit

# Or with PHP:
php vendor/bin/phpunit
```

### Coverage Not Generated

**Error:**
```
No code coverage driver available
```

**Solution:**
```bash
# Install Xdebug (for coverage)
pecl install xdebug

# Or install PCOV (faster alternative)
pecl install pcov

# Verify installation
php -m | grep -E "xdebug|pcov"
```

### Tests Failing

**Common Issues:**

1. **Missing dependencies**: Run `composer install` (though vendor/ is included)
2. **PHP version**: Ensure PHP 7.4+
3. **File paths**: Make sure you're running from the root directory (where `phpunit.xml` is)

### "Cannot find hello-elementor-child" Error

**Error:**
```
Cannot find hello-elementor-child directory
```

**Solution:**
- Ensure `hello-elementor-child/` folder exists in the same directory as `phpunit.xml`
- Run tests from the **root directory** (not from inside the theme folder)
- Verify the directory structure matches the expected layout

### SonarCloud Not Receiving Coverage

**Solutions:**
- Ensure `coverage.xml` is generated: `./vendor/bin/phpunit --coverage-clover coverage.xml`
- Check GitHub Actions logs for errors
- Verify `SONAR_TOKEN` secret is set correctly
- Ensure `sonar-project.properties` has correct project key

---

## 📝 Important Notes

### Running Tests Location

✅ **Correct**: Run tests from the **root directory** (where `phpunit.xml` is located)
```bash
cd /path/to/your-project/  # Root with phpunit.xml
./vendor/bin/phpunit
```

❌ **Incorrect**: Don't run tests from inside the theme folder
```bash
cd /path/to/your-project/hello-elementor-child/  # Wrong!
./vendor/bin/phpunit  # This won't work correctly
```

### Running Tests from wp-content/themes/ Folder

**Yes, it's possible!** You can run tests from `wp-content/themes/` folder, but it requires some path adjustments.

For instructions on setting up tests in the themes folder, see:
- **`THEMES_FOLDER_SETUP.md`** - Complete guide for themes folder setup
- **`phpunit-themes.xml`** - Alternative PHPUnit config for themes folder
- **`tests/bootstrap-themes.php`** - Bootstrap file configured for themes folder

**Quick setup for themes folder:**

1. Extract testing package to `wp-content/themes/`
2. Update test paths (use provided script):
   ```bash
   ./update-paths-for-themes.sh wp-content/themes
   ```
3. Use themes-specific config:
   ```bash
   cd wp-content/themes/
   ./vendor/bin/phpunit -c phpunit-themes.xml
   ```

**Note**: The default root-level structure is recommended for most users as it's cleaner and doesn't require path modifications.

### Theme Replacement

⚠️ **Important**: When you extract the testing package:
- The `hello-elementor-child/` folder contains the theme files
- **Replace** your existing theme folder with this one if you're testing on a live WordPress site
- For standalone testing, just use the extracted package structure as-is

### No Database Required

Unlike the old testing method, this setup uses **Brain Monkey** to mock WordPress functions, so you **don't need a test database**. All tests run without database connections.

---

## 📚 Test Framework Details

### Brain Monkey

Used for mocking WordPress functions:
```php
Monkey\Functions\expect('wp_enqueue_script')
    ->once()
    ->with('jquery', 'path', [], 'version', true)
    ->andReturn(true);
```

### Mockery

Used for mocking complex objects:
```php
$mock = Mockery::mock('SomeClass');
$mock->shouldReceive('method')->andReturn('value');
```

### PHPUnit 9.5

Test framework with:
- Strict test execution
- Code coverage support
- Test dependency management

---

## 🔄 Updating Tests

When you add new functionality to the theme:

1. **Add corresponding tests** in `tests/Unit/`
2. **Follow naming convention**: `ClassNameTest.php`
3. **Run tests** to ensure they pass:
   ```bash
   ./vendor/bin/phpunit
   ```
4. **Check coverage** to see if new code is covered:
   ```bash
   ./vendor/bin/phpunit --coverage-html coverage
   ```

---

## ✅ Quick Reference

### Most Common Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage --coverage-clover coverage.xml

# Run specific test file
./vendor/bin/phpunit tests/Unit/FunctionsTest.php

# Run specific test method
./vendor/bin/phpunit --filter test_theme_version

# Verbose output
./vendor/bin/phpunit --testdox
```

### Directory Structure Summary

- **Root directory** = Where `phpunit.xml` is located (run tests from here)
- **Theme folder** = `hello-elementor-child/` (contains theme files)
- **Tests folder** = `tests/Unit/` (contains all test files)
- **Vendor folder** = `vendor/` (contains PHPUnit and dependencies)

---

## 📖 Additional Resources

- **PHPUnit Documentation**: https://phpunit.de/documentation.html
- **Brain Monkey Documentation**: https://giuseppe-mazzapica.gitbook.io/brain-monkey/
- **SonarCloud Documentation**: https://docs.sonarcloud.io/
- **WordPress Coding Standards**: https://developer.wordpress.org/coding-standards/

---

## 🎯 Summary

1. **Extract** the `hello-elementor-child-testing-*.zip` package
2. **Replace** your existing `hello-elementor-child/` folder with the one from the package (if needed)
3. **Navigate** to the root directory (where `phpunit.xml` is located)
4. **Run tests**: `./vendor/bin/phpunit`
5. **Generate coverage**: `./vendor/bin/phpunit --coverage-html coverage --coverage-clover coverage.xml`

**Remember**: Tests are integrated with the theme, so they work together in the same project structure. Run tests from the **root directory**, not from inside the theme folder!

---

**Version**: 2.0  
**Last Updated**: November 2025  
**Test Framework**: PHPUnit 9.5  
**PHP Version**: 7.4+  
**Status**: Production Ready

