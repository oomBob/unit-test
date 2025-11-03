# How to Improve Code Coverage from 1.9% to 80%

## Current Situation

Your coverage is **1.9%** (75 covered statements out of 4,040 total statements).

### Coverage Breakdown by File

| File | Statements | Covered | Coverage % | Priority |
|------|-----------|---------|------------|----------|
| `oom-elementor.php` | 1,591 | 0 | 0% | 🔴 High |
| `oom-optimization-security.php` | 967 | 1 | 0.1% | 🔴 High |
| `oom-rest-api-v1.php` | 247 | 0 | 0% | 🟡 Medium |
| `oom-theme-options.php` | 73 | 1 | 1.4% | 🟡 Medium |
| `oom-global-shortcode.php` | 727 | 39 | 5.4% | 🟢 Low (has tests) |
| `oom-custom-shortcode.php` | 86 | 15 | 17.4% | 🟢 Low (has tests) |
| Widget files | ~1,349 | 19 | 1.4% | 🟡 Medium |

## Why Coverage is Low

1. **Large files have no tests** - `oom-elementor.php` (1,591 lines) and `oom-optimization-security.php` (967 lines) have almost zero coverage
2. **Only 4 test files exist** - Current tests only cover a small portion of the codebase
3. **Most code paths aren't tested** - Many functions and classes aren't being exercised by tests

## Strategy to Improve Coverage

### Phase 1: Target High-Impact Files (Goal: 30% coverage)

Focus on the largest files first to make the biggest impact:

#### 1. Create `ElementorTest.php` for `oom-elementor.php`

**Why:** This file has 1,591 statements and zero coverage. Even 20% coverage would add ~318 covered statements.

**Steps:**
```bash
# Create test file
touch tests/Unit/ElementorTest.php
```

**Test the main functions/classes:**
- Elementor widget registrations
- Elementor control additions
- Elementor render functions
- Elementor hooks and filters

#### 2. Create `OptimizationSecurityTest.php` for `oom-optimization-security.php`

**Why:** This file has 967 statements. Even 25% coverage would add ~242 covered statements.

**Test these functions:**
- Security functions (XSS prevention, input sanitization)
- Optimization functions (cache settings, performance tweaks)
- Hook callbacks
- Admin panel functions

#### 3. Create `RestApiTest.php` for `oom-rest-api-v1.php`

**Why:** REST API endpoints are critical and should have high coverage.

**Test these:**
- API endpoint registrations
- API callback functions
- Request validation
- Response formatting

### Phase 2: Improve Existing Tests (Goal: 50% coverage)

#### 4. Enhance `ShortcodesTest.php`

Currently tests `oom-global-shortcode.php` but only covers 5.4%.

**Add tests for:**
- All shortcode variations
- Edge cases (empty attributes, invalid inputs)
- All conditional branches
- Error handling

#### 5. Create `ThemeOptionsTest.php` for `oom-theme-options.php`

**Test:**
- Theme option registrations
- Option defaults
- Option validation
- Admin panel rendering

### Phase 3: Widget Tests (Goal: 65% coverage)

#### 6. Create widget tests

Test files in `oom/widgets/`:
- `oom-elementor-form` widget
- `oom-table-widget`

### Phase 4: Comprehensive Testing (Goal: 80% coverage)

#### 7. Edge cases and error handling

- Test all error paths
- Test boundary conditions
- Test invalid inputs
- Test WordPress hook interactions

## Quick Start: Create Your First High-Impact Test

### Example: Test `oom-elementor.php`

Create `tests/Unit/ElementorTest.php`:

```php
<?php
namespace HelloElementorChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class ElementorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Mock WordPress and Elementor functions
        Monkey\Functions\when('add_action')->justReturn();
        Monkey\Functions\when('add_filter')->justReturn();
        Monkey\Functions\when('is_admin')->justReturn(false);
        
        // Load the Elementor file
        require_once __DIR__ . '/../../hello-elementor-child/oom/oom-elementor.php';
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_elementor_widget_registered()
    {
        // Mock Elementor functions
        Monkey\Functions\expect('register_widget_type')
            ->once();
            
        // Call the function that registers widgets
        // Adjust based on your actual code structure
        $this->assertTrue(true);
    }
    
    // Add more tests based on functions in oom-elementor.php
}
```

## Coverage Targets by Priority

### 🔴 Critical (Do First)
1. `oom-elementor.php` - 1,591 statements → Target: 20% (318 covered)
2. `oom-optimization-security.php` - 967 statements → Target: 25% (242 covered)

**Impact:** ~560 new covered statements → **Coverage jumps to ~15%**

### 🟡 Important (Do Second)
3. `oom-rest-api-v1.php` - 247 statements → Target: 40% (99 covered)
4. `oom-theme-options.php` - 73 statements → Target: 50% (37 covered)
5. Widget files - ~1,349 statements → Target: 30% (405 covered)

**Impact:** ~541 new covered statements → **Coverage jumps to ~28%**

### 🟢 Enhance (Do Third)
6. Improve existing shortcode tests → Add ~200 covered statements
7. Test edge cases → Add ~300 covered statements

**Impact:** ~500 new covered statements → **Coverage jumps to ~40%**

### Continue to 80%

8. Comprehensive testing of remaining code paths
9. Error handling and edge cases
10. Integration tests for complex workflows

## Testing Best Practices

### 1. Test One Function/Method at a Time
```php
public function test_function_name_scenario()
{
    // Arrange
    $input = 'test';
    
    // Act
    $result = your_function($input);
    
    // Assert
    $this->assertEquals('expected', $result);
}
```

### 2. Test All Branches
- Test if conditions (true and false)
- Test loop conditions
- Test error paths
- Test edge cases (empty, null, zero, etc.)

### 3. Mock WordPress Functions
```php
// Expect function to be called
Monkey\Functions\expect('get_post_meta')
    ->once()
    ->with(1, 'key', true)
    ->andReturn('value');

// Stub function (don't care about calls)
Monkey\Functions\when('add_action')->justReturn();
```

### 4. Aim for Function-Level Coverage
- Each function should have at least one test
- Complex functions need multiple tests
- Test both happy paths and error paths

## Running Tests and Checking Coverage

### Run Tests Locally
```bash
# Run all tests
composer test

# Run with coverage (HTML report)
composer test-coverage

# View coverage report
open coverage/index.html
```

### Check Coverage in Coverage Report

1. Open `coverage/index.html` in your browser
2. Click on a file to see which lines are covered (green) and which aren't (red)
3. Focus on files with low coverage percentage
4. Write tests for uncovered lines

### Check Coverage in SonarCloud

1. Go to SonarCloud → Your Project → Measures tab
2. Click on "Coverage" metric
3. See which files have low coverage
4. Prioritize files with many statements and low coverage

## Tracking Progress

### After Phase 1 (High-Impact Files)
- **Expected:** 15-20% coverage
- **New covered statements:** ~560
- **Files tested:** 2-3 new test files

### After Phase 2 (Improve Existing)
- **Expected:** 25-30% coverage
- **New covered statements:** ~541
- **Files improved:** 3-4 test files

### After Phase 3 (Widgets)
- **Expected:** 40-50% coverage
- **New covered statements:** ~500
- **Files tested:** 2-3 widget test files

### After Phase 4 (Comprehensive)
- **Expected:** 65-80% coverage
- **New covered statements:** ~800+
- **All critical paths tested**

## Tips for Faster Coverage Growth

1. **Start with the largest files** - Biggest impact with fewer tests
2. **Test public functions first** - Usually easier to test
3. **Skip complex internal functions initially** - Can come back to them
4. **Use data providers** - Test multiple scenarios efficiently
5. **Mock aggressively** - Don't test WordPress core, just your code

## Current Test Files

You have these test files that need enhancement:
- ✅ `FunctionsTest.php` - Tests functions.php (excluded from coverage)
- ✅ `ShortcodesTest.php` - Tests oom-global-shortcode.php (5.4% coverage)
- ✅ `SecurityTest.php` - Needs more tests
- ✅ `OptimizationTest.php` - Needs more tests

## Next Steps

1. **Create `ElementorTest.php`** - Target `oom-elementor.php` (biggest impact)
2. **Enhance `OptimizationTest.php`** - Target `oom-optimization-security.php`
3. **Create `RestApiTest.php`** - Target `oom-rest-api-v1.php`
4. **Run tests and check coverage** - See progress in `coverage/index.html`
5. **Commit and push** - Coverage will update in SonarCloud

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Brain Monkey Documentation](https://giuseppe-mazzapica.gitbook.io/brain-monkey/)
- [SonarCloud Coverage Documentation](https://docs.sonarcloud.io/user-guide/code-coverage/)

---

**Remember:** Coverage is a journey, not a destination. Start with high-impact files and gradually improve coverage over time.

