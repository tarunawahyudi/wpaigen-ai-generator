# WPaigen AI Generator - Release Process

Simple, automated release process for WPaigen AI Generator using Git + SVN workflow.

## 🏗️ Repository Structure

- **Git Repository** - Development & GitHub releases
- **SVN Repository** - `https://plugins.svn.wordpress.org/wpaigen-ai-generator/`

## 🚀 Quick Release Process

### 1. Development & Testing
```bash
# Create feature branch (follow CONTRIBUTING.md conventions)
git checkout main
git checkout -b feature/your-feature-name
# Make changes...
git add .
git commit -m "feat: add your feature description"
git push origin feature/your-feature-name
# Create PR, merge to main after review
```

### 2. Version Bumping (Automated)
```bash
# Update version numbers across files (SemVer)
npm run version-check  # Check current versions

# Manual update for major/minor releases:
# Update in: wpaigen-ai-generator.php, readme.txt
# Add changelog entry in readme.txt
```

### 3. Git Release
```bash
git add .
git commit -m "chore(release): bump version to X.Y.Z"
git tag -a vX.Y.Z -m "Release vX.Y.Z: description"
git push origin vX.Y.Z
# Create GitHub release via UI
```

### 4. SVN Package (Automated)
```bash
# Prepare SVN package (automated)
npm run prepare-svn

# Script will prompt for SVN directory path and:
# - Clean trunk/ folder
# - Copy production files (excludes dev files)
# - Show file summary
# - Generate SVN status preview
```

### 5. SVN Commit
```bash
cd /path/to/your/svn/wpaigen-ai-generator
svn status                    # Review changes
svn add --force trunk/          # Add new files
svn commit -m "Release vX.Y.Z"
svn cp trunk tags/X.Y.Z        # Create tag
svn commit -m "Tag vX.Y.Z"
```

## 📝 Version Management

### Semantic Versioning
- **Major (X.0.0)**: Breaking changes, new payment systems
- **Minor (X.Y.0)**: New features, language support
- **Patch (X.Y.Z)**: Bug fixes, security updates

### Files to Update
1. `wpaigen-ai-generator.php` - Version header + constant
2. `readme.txt` - Stable tag + changelog
3. `package.json` - Node.js version

## 🛠️ Available Scripts

```bash
npm run prepare-svn     # Prepare SVN package
npm run version-check    # Check version consistency
```

## 📋 Files Status

### ✅ Included in SVN
- Plugin functionality files
- `wpaigen-ai-generator.php`
- `readme.txt`
- `includes/`, `admin/`, `admin/views/`

### 🚫 Excluded from SVN
- Development files
- `.git/`, package.json, RELEASING.md
- IDE files (.vscode, .idea)
- Test files, logs, backups

## 🧪 Testing Checklist

- [ ] Plugin activates/deactivates correctly
- [ ] All features work (Free + Pro versions)
- [ ] Payments (Midtrans/PayPal) work
- [ ] License validation works
- [ ] No PHP errors
- [ ] Compatible with latest WordPress

## 🔧 Troubleshooting

### SVN Issues
```bash
# Authentication failed
svn update --username your-wp-username

# File conflicts
svn resolve --accept working
```

### Version Validation
```bash
# Check all version numbers are consistent
npm run version-check
```

## 📅 Automation Benefits

- ✅ **No manual file copying/deleting**
- ✅ **No accidentally committed dev files**
- ✅ **Version consistency validation**
- ✅ **Detailed file summary**
- ✅ **SVN status preview**

**Streamlined release process for WordPress.org deployment!** 🚀