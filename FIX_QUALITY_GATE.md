# How to Fix Quality Gate "Failed" Status

## Problem

Your SonarCloud Quality Gate is showing as "Failed" with this error:
- **0.0% Security Hotspots Reviewed** (requires 100%)

This happens because SonarCloud's default quality gate requires **100% of security hotspots to be reviewed**, which is very strict and often not practical.

## Solution: Adjust Quality Gate Requirements

### Option 1: Remove Security Hotspots Requirement (Recommended)

1. **Go to SonarCloud Dashboard**
   - Navigate to: https://sonarcloud.io
   - Sign in with your GitHub account

2. **Select Your Organization**
   - Click on organization: `oombob`

3. **Navigate to Quality Gates**
   - Click on **"Quality Gates"** in the top menu
   - Or go directly to: https://sonarcloud.io/organizations/oombob/quality_gates

4. **Edit the Quality Gate**
   - Find **"Sonar way"** quality gate (or the one assigned to your project)
   - Click the **"..."** menu → **"Edit"**

5. **Remove Security Hotspots Condition**
   - Scroll down to find **"Security Hotspots Reviewed on New Code"**
   - Click the **trash icon** 🗑️ to remove this condition
   - Click **"Save"** at the top

6. **Assign to Your Project**
   - Go to your project: `oomBob_unit-test`
   - Go to **Project Settings** → **Quality Gate**
   - Make sure **"Sonar way"** (or your edited gate) is selected

### Option 2: Lower the Threshold (Alternative)

If you want to keep the requirement but make it less strict:

1. Follow steps 1-4 above

2. **Modify the Condition Instead of Removing**
   - Find **"Security Hotspots Reviewed on New Code"**
   - Click **"Edit"** (pencil icon ✏️)
   - Change the requirement from **100%** to **0%** or **50%**
   - Click **"Save"**

3. **Save the Quality Gate**
   - Click **"Save"** at the top

### Option 3: Create a Custom Quality Gate (Advanced)

1. **Create New Quality Gate**
   - Go to Quality Gates page
   - Click **"Create"** button
   - Name it: "Custom" or "Relaxed"

2. **Add Conditions**
   - Add the conditions you want (coverage, duplications, etc.)
   - **DO NOT** add "Security Hotspots Reviewed" or set it to 0%

3. **Assign to Project**
   - Go to your project → Project Settings → Quality Gate
   - Select your new quality gate

## After Making Changes

1. **Re-run the Workflow**
   - Go to GitHub → Actions
   - Click "SonarCloud Analysis"
   - Click "Run workflow" (top right)
   - Or push a new commit

2. **Check Quality Gate Status**
   - Wait for the workflow to complete
   - Go to SonarCloud → Your Project
   - Check if Quality Gate shows **"Passed"** ✅

## Coverage Issue

If you see: **"A few extra steps are needed for SonarQube Cloud to analyze your code coverage"**

This usually means:
1. The `coverage.xml` file wasn't uploaded properly
2. The workflow failed before generating coverage
3. The path in `sonar-project.properties` is incorrect

**To fix:**
1. Check that the workflow generates `coverage.xml` successfully
2. Verify `sonar.php.coverage.reportPaths=coverage.xml` in `sonar-project.properties`
3. Ensure the workflow completes all steps successfully

## Quick Checklist

- [ ] Quality Gate edited (removed or lowered security hotspots requirement)
- [ ] Quality Gate assigned to your project
- [ ] Workflow re-run successfully
- [ ] Coverage file (`coverage.xml`) generated
- [ ] SonarCloud shows coverage percentage

## Troubleshooting

### Quality Gate Still Shows Failed
- Make sure you saved the Quality Gate changes
- Verify the correct Quality Gate is assigned to your project
- Wait a few minutes for changes to propagate

### Coverage Not Showing
- Check GitHub Actions logs for errors
- Verify `coverage.xml` exists after workflow runs
- Check that `sonar.php.coverage.reportPaths=coverage.xml` is correct

### Workflow Still Failing
- Check GitHub Actions logs for specific error messages
- Verify `SONAR_TOKEN` secret is set correctly
- Generate a new token if token is expired

## Need Help?

- Check GitHub Actions logs for detailed error messages
- Review SonarCloud project settings
- Verify all secrets are correctly configured
- Check SonarCloud documentation: https://docs.sonarcloud.io

