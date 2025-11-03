# WordPress Child Theme Unit Testing Setup

This repository contains unit tests for the Hello Elementor Child WordPress theme, configured to work with SonarCloud for code quality and coverage analysis.

## 📋 Prerequisites

- PHP 7.4 or higher
- Composer
- Git
- SonarCloud account (free tier available)

## 🚀 Setup Instructions

### 1. Install Dependencies

```bash
composer install
```

### 2. Run Tests Locally

```bash
# Run all tests
composer test

# Run tests with coverage report (HTML)
composer test-coverage

# Run tests with coverage report (Text)
composer test-coverage-text
```

The coverage reports will be generated in the `coverage/` directory.

### 3. SonarCloud Configuration

#### Step 1: Create SonarCloud Project

1. Go to [SonarCloud.io](https://sonarcloud.io) and sign in with GitHub
2. Click "Create new project"
3. Select your organization and repository
4. Copy the project key and organization name

#### Step 2: Update SonarCloud Configuration

Edit `sonar-project.properties` and update these values:

```properties
sonar.projectKey=your-organization_unit-test
sonar.organization=your-organization
```

Replace:
- `your-organization` with your SonarCloud organization name
- `unit-test` with your actual project key

#### Step 3: Get SonarCloud Token

1. In SonarCloud, go to **My Account** → **Security**
2. Generate a new token
3. Copy the token

#### Step 4: Configure GitHub Secrets

In your GitHub repository:

1. Go to **Settings** → **Secrets** → **Actions**
2. Add a new secret:
   - **Name**: `SONAR_TOKEN`
   - **Value**: Paste your SonarCloud token

### 4. GitHub Actions Workflow

The workflow file (`.github/workflows/sonarcloud.yml`) is already configured. It will:

- Run on pushes to `main` and `develop` branches
- Run on pull requests
- Execute PHPUnit tests
- Generate coverage reports
- Send results to SonarCloud

**Note**: Make sure your `sonar-project.properties` file has the correct project key and organization before pushing.

## 📊 Coverage Goal

The target is **80% code coverage**. Current coverage can be viewed:

- **Locally**: Open `coverage/index.html` in your browser
- **SonarCloud**: View the coverage dashboard on SonarCloud

## 📁 Project Structure

```
.
├── hello-elementor-child/    # Your WordPress theme
├── tests/
│   ├── Unit/                # Unit tests
│   │   ├── FunctionsTest.php
│   │   ├── ShortcodesTest.php
│   │   └── SecurityTest.php
│   ├── Integration/         # Integration tests (future)
│   └── bootstrap.php         # Test bootstrap
├── .github/
│   └── workflows/
│       └── sonarcloud.yml    # CI/CD workflow
├── composer.json             # PHP dependencies
├── phpunit.xml              # PHPUnit configuration
├── sonar-project.properties # SonarCloud configuration
└── README.md                # This file
```

## 🧪 Writing Tests

### Test File Naming

- Test files should end with `Test.php`
- Place unit tests in `tests/Unit/`
- Place integration tests in `tests/Integration/`

### Example Test

```php
<?php
namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class MyFunctionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        require_once __DIR__ . '/../../../hello-elementor-child/your-file.php';
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_my_function()
    {
        // Your test code here
        $this->assertTrue(true);
    }
}
```

## 🔍 SonarCloud Analysis

After pushing to GitHub, SonarCloud will:

1. Analyze your code
2. Calculate code coverage
3. Identify code smells
4. Detect bugs and vulnerabilities
5. Provide quality metrics

View results at: `https://sonarcloud.io/dashboard?id=your-project-key`

## 📝 Excluded Files

The following files are excluded from coverage:

- `functions.php` (main theme file, often hard to test)
- `header.php` (template file)
- `style.css` (CSS file)
- `assets/` directory (static assets)
- `template-parts/` directory (template files)

You can modify exclusions in `phpunit.xml` and `sonar-project.properties`.

## 🐛 Troubleshooting

### Tests not running

- Make sure Composer dependencies are installed: `composer install`
- Check PHP version: `php -v` (should be 7.4+)
- Verify PHPUnit is installed: `vendor/bin/phpunit --version`

### Coverage not generating

- Ensure Xdebug is installed: `php -m | grep xdebug`
- Check `phpunit.xml` coverage settings
- Verify file paths in `phpunit.xml`

### SonarCloud not receiving data

- Verify `SONAR_TOKEN` secret is set in GitHub
- Check `sonar-project.properties` has correct project key
- Review GitHub Actions logs for errors

### Automatic Analysis conflict error

If you see: **"You are running CI analysis while Automatic Analysis is enabled"**

- This means Automatic Analysis is enabled in SonarCloud, which conflicts with your CI/CD workflow
- **Fix**: Disable Automatic Analysis in SonarCloud project settings
- **See**: [FIX_AUTOMATIC_ANALYSIS.md](FIX_AUTOMATIC_ANALYSIS.md) for detailed instructions

### Coverage shows "not enough lines"

If you see: **"There are not enough lines to compute coverage"** in the New Code tab

- This is normal when the new code period is short or has minimal changes
- **Fix**: Switch to the **"Overall Code"** tab to see full project coverage
- Coverage data is being generated correctly - it just appears in the "Overall Code" tab
- **See**: [FIX_COVERAGE_NOT_ENOUGH_LINES.md](FIX_COVERAGE_NOT_ENOUGH_LINES.md) for explanation

### Coverage is low (e.g., 1.9%)

If your coverage percentage is low:

- **Current situation**: Coverage reflects actual test coverage of your code
- **Low coverage means**: Many files have no tests or minimal tests
- **How to improve**: Write more tests for uncovered files
- **Priority**: Focus on large files first (biggest impact)
- **See**: [IMPROVE_COVERAGE.md](IMPROVE_COVERAGE.md) for a complete strategy to improve coverage to 80%

## 📚 Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Brain Monkey Documentation](https://giuseppe-mazzapica.gitbook.io/brain-monkey/)
- [SonarCloud Documentation](https://docs.sonarcloud.io/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)

## 🤝 Contributing

1. Write tests for new features
2. Ensure all tests pass
3. Maintain at least 80% code coverage
4. Update this README if needed

## 📄 License

Same as the WordPress theme.

