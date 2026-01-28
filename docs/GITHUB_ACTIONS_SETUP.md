# GitHub Actions Setup for Hugo Deployment

This document describes the GitHub Actions secrets required for the Hugo build and Vercel deployment workflow.

## Required GitHub Secrets

The Hugo deployment workflow (`.github/workflows/hugo-deploy.yml`) requires the following secrets to be configured in your GitHub repository:

### 1. VERCEL_TOKEN

**Description**: Your Vercel authentication token for CLI deployments.

**How to get it**:
1. Log in to your Vercel account at https://vercel.com
2. Go to Settings > Tokens
3. Click "Create Token"
4. Give it a name (e.g., "GitHub Actions Hugo Deploy")
5. Copy the generated token

**How to add to GitHub**:
1. Go to your repository on GitHub
2. Navigate to Settings > Secrets and variables > Actions
3. Click "New repository secret"
4. Name: `VERCEL_TOKEN`
5. Value: Paste the token from Vercel
6. Click "Add secret"

### 2. VERCEL_ORG_ID

**Description**: Your Vercel organization ID (or team ID).

**How to get it**:
1. Install Vercel CLI: `npm install -g vercel`
2. Run: `vercel link` in your project directory
3. Follow the prompts to link to your Vercel project
4. After linking, run: `cat .vercel/project.json`
5. Copy the `orgId` value

**How to add to GitHub**:
1. Follow the same steps as VERCEL_TOKEN
2. Name: `VERCEL_ORG_ID`
3. Value: Paste the orgId value

### 3. VERCEL_PROJECT_ID

**Description**: Your Vercel project ID for this specific project.

**How to get it**:
1. After running `vercel link` (from step 2 above)
2. Run: `cat .vercel/project.json`
3. Copy the `projectId` value

**How to add to GitHub**:
1. Follow the same steps as VERCEL_TOKEN
2. Name: `VERCEL_PROJECT_ID`
3. Value: Paste the projectId value

## Workflow Behavior

Once these secrets are configured:

1. **On push to `main` or `master` branch**:
   - Hugo site is built
   - Artifacts are uploaded
   - Site is automatically deployed to Vercel production

2. **On pull requests**:
   - Hugo site is built
   - Artifacts are uploaded for review
   - No deployment occurs (preview only)

3. **Manual trigger**:
   - You can manually trigger the workflow from the Actions tab

## Testing the Workflow

After setting up the secrets:

1. Make a small change to a content file or layout
2. Commit and push to a feature branch
3. Create a pull request
4. Check the Actions tab to see the build status
5. Once merged to main/master, check for automatic deployment

## Troubleshooting

### "Error: No token found"
- Make sure `VERCEL_TOKEN` is correctly set in GitHub secrets
- Verify the token hasn't expired in Vercel

### "Error: Project not found"
- Verify `VERCEL_PROJECT_ID` matches your actual project
- Make sure the project exists in your Vercel account

### "Error: Unauthorized"
- Check that `VERCEL_ORG_ID` is correct
- Verify your Vercel token has access to the organization/team

## Security Notes

- Never commit these secrets to the repository
- Rotate tokens periodically for security
- Use different tokens for different environments if needed
- Keep `.vercel/` directory in `.gitignore` (already configured)
