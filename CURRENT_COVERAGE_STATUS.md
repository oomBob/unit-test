# Current Coverage Status - 1.9%

**Generated:** Based on `coverage.xml` and SonarCloud analysis  
**Date:** Latest coverage report

## Summary

- **Total Coverage:** 1.9% (75 / 4,040 statements)
- **Total Files Tracked:** 14 PHP files
- **Uncovered Statements:** 3,974
- **Target Coverage:** 80%

## File-by-File Coverage Breakdown

### 🔴 High Priority (0% Coverage - Large Files)

| File | Statements | Covered | Coverage % | Priority |
|------|-----------|---------|------------|----------|
| `oom-elementor.php` | **1,591** | 0 | **0%** | 🔴 Critical |
| `oom-ajax.php` | 9 | 0 | 0% | 🟡 Medium |
| `oom-elementor.php` (widget) | 9 | 0 | 0% | 🟡 Medium |
| `oom-rest-api-v1.php` | **247** | 0 | **0%** | 🟡 Medium |
| `oom-form-database.php` | 30 | 0 | 0% | 🟡 Medium |
| `oom-form-submission-page.php` | 171 | 0 | 0% | 🟡 Medium |
| `index.php` (form) | 1 | 0 | 0% | 🟢 Low |
| `oom-form.php` (widget) | 203 | 3 | 1.5% | 🟡 Medium |
| `oom-form.php` (main) | 421 | 0 | 0% | 🟡 Medium |
| `oom-form-functions.php` | 436 | 0 | 0% | 🟡 Medium |
| `oom-table-widget.php` | **1,591** | 0 | **0%** | 🔴 Critical |

### 🟡 Medium Priority (Low Coverage)

| File | Statements | Covered | Coverage % | Priority |
|------|-----------|---------|------------|----------|
| `oom-optimization-security.php` | **967** | **2** | **0.1%** | 🔴 Critical |
| `oom-theme-options.php` | 73 | 1 | 1.4% | 🟡 Medium |

### 🟢 Has Some Coverage (Needs Improvement)

| File | Statements | Covered | Coverage % | Priority |
|------|-----------|---------|------------|----------|
| `oom-global-shortcode.php` | 727 | **39** | **5.4%** | 🟡 Medium |
| `oom-custom-shortcode.php` | 86 | **15** | **17.4%** | 🟢 Low |

## Files Referenced in SonarCloud URLs

### Files Needing Tests

1. ✅ **`oom-form-database.php`** - 0% coverage (30 statements)
   - **Location:** `hello-elementor-child/oom/widgets/oom-elementor-form/admin/oom-form-database.php`
   - **Priority:** 🟡 Medium
   - **Action:** Create `FormDatabaseTest.php`

2. ✅ **`oom-form-functions.php`** - 0% coverage (436 statements)
   - **Location:** `hello-elementor-child/oom/widgets/oom-elementor-form/widgets/oom-form/inc/oom-form-functions.php`
   - **Priority:** 🔴 High (large file)
   - **Action:** Create `FormFunctionsTest.php`

3. ✅ **`oom-form-submission-page.php`** - 0% coverage (171 statements)
   - **Location:** `hello-elementor-child/oom/widgets/oom-elementor-form/admin/oom-form-submission-page.php`
   - **Priority:** 🟡 Medium
   - **Action:** Create `FormSubmissionPageTest.php`

4. ✅ **`oom-form.php` (widget)** - 1.5% coverage (203 statements, 3 covered)
   - **Location:** `hello-elementor-child/oom/widgets/oom-elementor-form/widgets/oom-form/oom-form.php`
   - **Priority:** 🟡 Medium
   - **Action:** Enhance existing tests

5. ✅ **`oom-form.php` (main)** - 0% coverage (421 statements)
   - **Location:** `hello-elementor-child/oom/widgets/oom-elementor-form/oom-form.php`
   - **Priority:** 🟡 Medium
   - **Action:** Create `FormMainTest.php`

6. ✅ **`oom-table-widget.php`** - 0% coverage (1,591 statements)
   - **Location:** `hello-elementor-child/oom/widgets/oom-table-widget/oom-table-widget.php`
   - **Priority:** 🔴 Critical (largest file)
   - **Action:** Create `TableWidgetTest.php`

7. ✅ **`oom-theme-options.php`** - 1.4% coverage (73 statements, 1 covered)
   - **Location:** `hello-elementor-child/oom/oom-theme-options.php`
   - **Priority:** 🟡 Medium
   - **Action:** Create `ThemeOptionsTest.php`

8. ✅ **`oom-global-shortcode.php`** - 5.4% coverage (727 statements, 39 covered)
   - **Location:** `hello-elementor-child/oom/oom-global-shortcode.php`
   - **Priority:** 🟡 Medium (has tests but needs more)
   - **Action:** Enhance `ShortcodesTest.php`

9. ✅ **`oom-optimization-security.php`** - 0.1% coverage (967 statements, 2 covered)
   - **Location:** `hello-elementor-child/oom/oom-optimization-security.php`
   - **Priority:** 🔴 Critical (second largest file)
   - **Action:** Enhance `OptimizationTest.php`

## Coverage Impact Analysis

### Biggest Impact Files (Test These First)

1. **`oom-elementor.php`** - 1,591 statements
   - **Impact:** Even 20% coverage = ~318 statements
   - **Action:** Create `ElementorTest.php`

2. **`oom-table-widget.php`** - 1,591 statements  
   - **Impact:** Even 20% coverage = ~318 statements
   - **Action:** Create `TableWidgetTest.php`

3. **`oom-optimization-security.php`** - 967 statements
   - **Impact:** Even 25% coverage = ~242 statements
   - **Action:** Enhance `OptimizationTest.php`

4. **`oom-global-shortcode.php`** - 727 statements (already 5.4%)
   - **Impact:** Improve to 30% = ~218 total covered
   - **Action:** Enhance `ShortcodesTest.php`

### Total Impact Estimate

If we achieve:
- `oom-elementor.php`: 20% coverage = +318 statements
- `oom-table-widget.php`: 20% coverage = +318 statements  
- `oom-optimization-security.php`: 25% coverage = +242 statements
- `oom-global-shortcode.php`: Improve to 30% = +179 statements
- `oom-form-functions.php`: 30% coverage = +131 statements

**Total: +1,188 covered statements**

**New Coverage:** ~31% (from 1.9%)

## Current Test Files

You have these test files that need enhancement:

1. ✅ `FunctionsTest.php` - Tests `functions.php` (excluded from coverage)
2. ✅ `ShortcodesTest.php` - Tests `oom-global-shortcode.php` (5.4% coverage)
3. ✅ `SecurityTest.php` - Needs more comprehensive tests
4. ✅ `OptimizationTest.php` - Only tests function_exists (0.1% coverage)

## Next Steps

### Immediate Actions (Highest Impact)

1. **Create `ElementorTest.php`** 
   - Target: `oom-elementor.php` (1,591 statements)
   - Expected impact: +300 statements covered

2. **Create `TableWidgetTest.php`**
   - Target: `oom-table-widget.php` (1,591 statements)
   - Expected impact: +300 statements covered

3. **Enhance `OptimizationTest.php`**
   - Target: `oom-optimization-security.php` (967 statements)
   - Current: Only tests function_exists
   - Expected impact: +240 statements covered

### Secondary Actions

4. **Create `FormFunctionsTest.php`**
   - Target: `oom-form-functions.php` (436 statements)
   - Expected impact: +130 statements covered

5. **Create `FormDatabaseTest.php`**
   - Target: `oom-form-database.php` (30 statements)
   - Expected impact: +25 statements covered

6. **Create `FormSubmissionPageTest.php`**
   - Target: `oom-form-submission-page.php` (171 statements)
   - Expected impact: +50 statements covered

7. **Create `ThemeOptionsTest.php`**
   - Target: `oom-theme-options.php` (73 statements)
   - Expected impact: +50 statements covered

## How to View Coverage Locally

```bash
# Generate coverage report
composer test-coverage

# Open in browser
open coverage/index.html
```

## How to View Coverage in SonarCloud

1. Go to: https://sonarcloud.io/component_measures?id=oomBob_unit-test&metric=coverage
2. Click on "Overall Code" tab (not "New Code")
3. Browse files by coverage percentage
4. Click on a file to see line-by-line coverage

## Coverage Files Location

- **Coverage XML:** `/Users/oomhradmin/oombob/unit-test/coverage.xml`
- **Coverage HTML:** `/Users/oomhradmin/oombob/unit-test/coverage/index.html`
- **SonarCloud:** https://sonarcloud.io/project/overview?id=oomBob_unit-test

---

**Note:** This document is based on the current `coverage.xml` file. Run `composer test-coverage` to regenerate and update these numbers.

