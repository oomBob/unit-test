# Contributing to Unit Tests

This document provides guidelines for writing unit tests to maintain 80% code coverage.

## Writing Tests

### Test Structure

```php
<?php
namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class MyClassTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        require_once __DIR__ . '/../../../hello-elementor-child/path/to/file.php';
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_function_name_scenario()
    {
        // Arrange
        // Act
        // Assert
    }
}
```

### Test Naming Convention

- Test method names should be descriptive: `test_function_name_scenario()`
- Example: `test_wpb_set_post_views_empty_count()`
- Use snake_case for test method names

### What to Test

#### ✅ Should Test:

1. **Functions** - All custom functions
2. **Shortcodes** - All shortcode outputs
3. **Filters** - Filter return values
4. **Actions** - Action callbacks
5. **Helper Functions** - Utility functions
6. **Edge Cases** - Boundary conditions, invalid inputs

#### ❌ Don't Need to Test:

1. WordPress core functions
2. Third-party library functions
3. Template files (template-parts/)
4. CSS/JS files (assets/)
5. Simple output buffering functions

### Testing WordPress Functions

Use Brain Monkey to mock WordPress functions:

```php
// Expect a function to be called
Monkey\Functions\expect('get_post_meta')
    ->once()
    ->with(1, 'meta_key', true)
    ->andReturn('value');

// Stub a function (don't care about calls)
Monkey\Functions\when('wp_enqueue_style')->justReturn();

// Mock a function that returns a value
Monkey\Functions\when('site_url')->justReturn('https://example.com');
```

### Coverage Goals

- **Target**: 80% overall coverage
- **Critical functions**: Aim for 100% coverage
- **Helper functions**: Aim for 80%+ coverage
- **Complex functions**: Test all branches

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/FunctionsTest.php

# Run with coverage
composer test-coverage

# Run specific test method
vendor/bin/phpunit --filter test_wpb_set_post_views_empty_count
```

### Test Best Practices

1. **One assertion per test** (when possible)
2. **Test behavior, not implementation**
3. **Use descriptive test names**
4. **Test edge cases** (empty, null, negative, out of range)
5. **Mock external dependencies**
6. **Keep tests isolated** (no shared state)
7. **Use setUp/tearDown** properly

### Examples

#### Testing a Shortcode

```php
public function test_oom_ratings_custom_display()
{
    $atts = ['display' => '3'];
    $result = oom_ratings($atts);
    
    $this->assertIsString($result);
    $this->assertStringContainsString('oom-star-rating', $result);
    $checked_count = substr_count($result, 'fa-star checked');
    $this->assertEquals(3, $checked_count);
}
```

#### Testing a Function with WordPress Dependencies

```php
public function test_wpb_set_post_views_empty_count()
{
    $post_id = 1;
    
    Monkey\Functions\expect('get_post_meta')
        ->once()
        ->with($post_id, 'wpb_post_views_count', true)
        ->andReturn('');
        
    Monkey\Functions\expect('delete_post_meta')
        ->once()
        ->with($post_id, 'wpb_post_views_count');
        
    Monkey\Functions\expect('add_post_meta')
        ->once()
        ->with($post_id, 'wpb_post_views_count', '1', true);

    wpb_set_post_views($post_id);
    
    $this->assertTrue(true); // Function completed without error
}
```

### Coverage Reports

After running `composer test-coverage`:

1. Open `coverage/index.html` in browser
2. Navigate to files to see coverage details
3. Red lines = not covered
4. Green lines = covered
5. Yellow lines = partially covered

### Adding New Tests

1. Create test file in `tests/Unit/`
2. Follow naming: `ClassNameTest.php`
3. Write tests for all functions in the class/file
4. Run tests: `composer test`
5. Check coverage: `composer test-coverage`
6. Commit and push

### Coverage Exclusions

Some files are excluded from coverage:

- `functions.php` (main theme file)
- `header.php` (template file)
- `style.css` (CSS)
- `assets/` (static files)
- `template-parts/` (templates)

See `phpunit.xml` for full exclusion list.

### Questions?

- Check existing tests for examples
- Review [PHPUnit documentation](https://phpunit.de/documentation.html)
- Check [Brain Monkey documentation](https://giuseppe-mazzapica.gitbook.io/brain-monkey/)

