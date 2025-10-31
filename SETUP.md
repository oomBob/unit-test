# Quick Setup Guide for SonarCloud

Follow these steps to get your unit testing and SonarCloud integration working:

## Step 1: Install Dependencies

```bash
composer install
```

## Step 2: Run Tests Locally (Optional)

```bash
# Run tests
composer test

# Generate coverage report
composer test-coverage
```

Open `coverage/index.html` to view coverage locally.

## Step 3: Configure SonarCloud

### 3.1 Create SonarCloud Account

1. Go to https://sonarcloud.io
2. Sign in with GitHub
3. Create a new organization (or use existing)

### 3.2 Create Project

1. In SonarCloud, click **+** → **Analyze new project**
2. Select **GitHub**
3. Choose your repository
4. **IMPORTANT**: Copy the **Project Key** and **Organization** name

### 3.3 Update Configuration

Edit `sonar-project.properties` and replace:

```properties
# Replace these values:
sonar.projectKey=your-organization_unit-test
sonar.organization=your-organization
```

With your actual values:

```properties
# Example:
sonar.projectKey=mycompany_unit-test
sonar.organization=mycompany
```

### 3.4 Get SonarCloud Token

1. In SonarCloud: **My Account** → **Security**
2. Click **Generate Token**
3. Name it (e.g., "GitHub Actions")
4. **Copy the token** (you won't see it again!)

## Step 4: Configure GitHub

### 4.1 Add SonarCloud Token to GitHub Secrets

1. Go to your GitHub repository
2. **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Add:
   - **Name**: `SONAR_TOKEN`
   - **Secret**: (Paste your SonarCloud token from Step 3.4)
5. Click **Add secret**

## Step 5: Push to GitHub

```bash
git add .
git commit -m "Add unit testing and SonarCloud setup"
git push origin main
```

## Step 6: Verify

1. Go to **Actions** tab in GitHub
2. You should see "SonarCloud Analysis" workflow running
3. Wait for it to complete
4. Go to SonarCloud dashboard
5. You should see your project with test results

## Troubleshooting

### Workflow fails with "SONAR_TOKEN not found"

- Make sure you added the secret in GitHub (Step 4.1)
- Secret name must be exactly `SONAR_TOKEN`

### Workflow fails with "Project key not found"

- Check `sonar-project.properties` has correct project key
- Project key format: `organization_project-name`
- Verify in SonarCloud dashboard

### No coverage showing in SonarCloud

- Check GitHub Actions logs for errors
- Verify `coverage.xml` is generated (check workflow logs)
- Ensure Xdebug is enabled in workflow (it's already configured)

### Tests fail locally

- Run `composer install` first
- Check PHP version: `php -v` (needs 7.4+)
- Verify Xdebug installed: `php -m | grep xdebug`

## Next Steps

1. Write more tests to reach 80% coverage
2. Check SonarCloud dashboard regularly
3. Fix code smells and bugs reported
4. Maintain test coverage above 80%

## Need Help?

- Check the main [README.md](README.md) for detailed documentation
- Review SonarCloud logs in GitHub Actions
- Check SonarCloud dashboard for analysis results

