# How to Verify SonarCloud Workflow is Working

## Quick Checklist

### ✅ Step 1: Check GitHub Actions Status

1. **Go to your GitHub repository**
   - Navigate to: `https://github.com/YOUR_USERNAME/YOUR_REPO`

2. **Click on the "Actions" tab**
   - You should see a list of workflow runs

3. **Look for "SonarCloud Analysis" workflow**
   - Check if it's running (yellow circle) ✓
   - Check if it completed successfully (green checkmark) ✓
   - Check if it failed (red X) ✗

4. **Click on the latest workflow run** to see detailed logs

---

### ✅ Step 2: Verify Workflow Steps

In the workflow run logs, check that these steps complete successfully:

1. ✅ **Checkout code** - Should complete in seconds
2. ✅ **Setup PHP** - Should show PHP 8.0 installation
3. ✅ **Install Composer dependencies** - Should install packages
4. ✅ **Test Xdebug Coverage Generation** - Should show "✅ Xdebug version"
5. ✅ **Verify Xdebug is enabled** - Should show "Xdebug extension loaded: YES"
6. ✅ **Run PHPUnit tests with coverage** - Should run tests and generate `coverage.xml`
7. ✅ **Verify coverage file exists** - Should show "✅ Coverage file found"
8. ✅ **Verify sonar-project.properties** - Should show configuration
9. ✅ **Show coverage file before scan** - Should validate coverage.xml format
10. ✅ **Verify SonarCloud Token Configuration** - Should show "✅ SONAR_TOKEN secret is configured"
11. ✅ **SonarCloud Scan** - Should complete successfully

---

### ✅ Step 3: Check for Common Issues

#### Issue 1: Token Not Found
**Look for:**
```
❌ ERROR: SONAR_TOKEN secret is not set!
```

**Fix:**
- Go to GitHub → Settings → Secrets and variables → Actions
- Add secret named `SONAR_TOKEN` with your SonarCloud token

#### Issue 2: Token Invalid
**Look for:**
```
ERROR: Invalid token
Authentication failed
```

**Fix:**
1. Generate a new token in SonarCloud:
   - SonarCloud → My Account → Security → Generate Token
2. Update the GitHub secret with the new token

#### Issue 3: Coverage File Not Generated
**Look for:**
```
❌ coverage.xml NOT found!
```

**Fix:**
- Check if tests are passing
- Verify Xdebug is enabled (should see "Xdebug extension loaded: YES")

#### Issue 4: Project Key Mismatch
**Look for:**
```
Project key not found
```

**Fix:**
- Check `sonar-project.properties` has correct `sonar.projectKey` and `sonar.organization`

#### Issue 5: Coverage Shows "Not Enough Lines"

**Look for:**
```
There are not enough lines to compute coverage
```

**This is Normal!**

This message appears in the **"New Code"** tab when the new code period is too short or has minimal changes.

**Fix:**
1. **Switch to "Overall Code" tab**
   - In SonarCloud dashboard, click on **"Overall Code"** tab (next to "New Code")
   - Coverage should be displayed there

2. **Or check "Measures" tab**
   - Go to **"Measures"** tab in SonarCloud
   - Coverage metrics are shown there

**Why this happens:**
- "New Code" tab only shows coverage for code changed in a specific period (e.g., last 3 days)
- If there are few changes in that period, SonarCloud may not show coverage
- This doesn't mean coverage isn't working - just check "Overall Code" tab instead

**See:** [FIX_COVERAGE_NOT_ENOUGH_LINES.md](FIX_COVERAGE_NOT_ENOUGH_LINES.md) for detailed explanation

#### Issue 6: Automatic Analysis Conflict
**Look for:**
```
You are running CI analysis while Automatic Analysis is enabled.
Please consider disabling one or the other.
```

**Fix:**
1. **Go to SonarCloud Dashboard**
   - Navigate to: https://sonarcloud.io
   - Sign in with your GitHub account

2. **Select Your Project**
   - Click on project: `oomBob_unit-test`
   - Or go directly to: https://sonarcloud.io/project/overview?id=oomBob_unit-test

3. **Disable Automatic Analysis**
   - Go to **Project Settings** (gear icon in top right)
   - Click on **"Analysis Method"** or **"Automatic Analysis"** in the left menu
   - Find the **"Automatic Analysis"** section
   - **Disable** or turn off Automatic Analysis
   - Click **"Save"**

4. **Verify**
   - After disabling, re-run your GitHub Actions workflow
   - The error should be resolved

**Why this happens:**
- SonarCloud has two ways to analyze code:
  1. **Automatic Analysis**: Triggered automatically by GitHub commits
  2. **CI/CD Analysis**: Triggered by your GitHub Actions workflow
- These two methods conflict and cannot run simultaneously
- For CI/CD workflows, disable Automatic Analysis to use your GitHub Actions setup

---

### ✅ Step 4: Verify in SonarCloud Dashboard

1. **Go to SonarCloud**
   - Navigate to: `https://sonarcloud.io`

2. **Check your project**
   - Should see project: `oomBob_unit-test`
   - Organization: `oombob`

3. **Look for:**
   - ✅ Latest analysis timestamp (should be recent)
   - ✅ Code coverage percentage
   - ✅ Code smells/issues count
   - ✅ Security vulnerabilities (if any)

4. **Check the activity timeline**
   - Should show recent analysis runs matching your GitHub Actions runs

---

### ✅ Step 5: Manually Trigger Workflow (Optional)

If you want to test without making a commit:

1. **Go to Actions tab in GitHub**
2. **Click on "SonarCloud Analysis" workflow**
3. **Click "Run workflow" button** (top right)
4. **Select branch** (usually `main`)
5. **Click "Run workflow"**
6. **Watch it execute in real-time**

---

## Expected Success Output

When working correctly, you should see:

### In GitHub Actions Logs:
```
✅ SONAR_TOKEN secret is configured
✅ Token length looks correct
✅ Xdebug extension loaded: YES
✅ Coverage file found
✅ Coverage file is valid Clover XML format
[SUCCESS] Analysis reports have been uploaded to SonarCloud
```

### In SonarCloud:
- Project shows recent analysis
- Coverage percentage displayed
- Code quality metrics updated
- No authentication errors

---

## Quick Test Commands (Local)

You can also test locally to verify everything works:

```bash
# 1. Install dependencies
composer install

# 2. Run tests with coverage
vendor/bin/phpunit --coverage-clover=coverage.xml

# 3. Verify coverage file exists
ls -lh coverage.xml

# 4. Check coverage file format
head -20 coverage.xml  # Should start with <?xml and contain <coverage>

# 5. Verify PHP configuration
php -m | grep xdebug  # Should show xdebug
php -r "echo extension_loaded('xdebug') ? 'YES' : 'NO';"  # Should output YES
```

---

## Troubleshooting

### If workflow doesn't run:
- ✅ Check if you pushed to `main` or `develop` branch
- ✅ Check if workflow_dispatch is enabled (for manual runs)
- ✅ Check GitHub Actions permissions in repository settings

### If workflow runs but fails:
- ✅ Check the specific failing step in logs
- ✅ Look for error messages (usually in red)
- ✅ Check if all secrets are configured
- ✅ Verify `sonar-project.properties` configuration

### If SonarCloud doesn't show results:
- ✅ Wait a few minutes (analysis takes time to process)
- ✅ Check SonarCloud organization has access to the project
- ✅ Verify project key matches between workflow and SonarCloud

---

## Need Help?

- Check GitHub Actions logs for detailed error messages
- Review SonarCloud dashboard for analysis status
- Verify all secrets are correctly set in GitHub
- Ensure SonarCloud project is properly configured

