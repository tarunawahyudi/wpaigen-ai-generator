# Contributing to WPaigen AI Generator

Thank you for your interest in contributing to WPaigen AI Generator! This document provides guidelines for contributing to the project.

## 🏗️ Development Workflow

### 1. Repository Setup
- Fork the repository
- Clone your fork locally
- Add upstream repository: `git remote add upstream https://github.com/tarunawahyudi/wpaigen-ai-generator.git`

### 2. Branching Strategy

#### 🌿 Branch Naming Convention
- `feature/feature-name` - New features
- `fix/issue-description` - Bug fixes
- `hotfix/critical-fix` - Urgent production fixes
- `docs/documentation-update` - Documentation changes
- `refactor/code-cleanup` - Code refactoring

#### 🌳 Main Branches
- `main` - Production-ready code (always deployable)
- `develop` - Development integration (if needed)

### 3. Commit Message Convention

Follow [Conventional Commits](https://www.conventionalcommits.org/) format:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

#### 📝 Commit Types
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `style` - Code formatting, missing semi-colons
- `refactor` - Code refactoring without feature changes
- `test` - Adding or updating tests
- `chore` - Maintenance tasks, build process, dependency updates

#### ✅ Examples
```
feat(paypal): add PayPal integration for international payments

fix(license): resolve license downgrade after plugin reinstall

docs(readme): update installation instructions for WordPress 6.0+

refactor(api): optimize database queries for better performance

chore(deps): update dependencies to latest stable versions
```

### 4. Pull Request Process

#### 📋 PR Requirements
1. **Branch Status**: Up-to-date with main branch
2. **Testing**: All features work correctly
3. **Documentation**: Updated if needed
4. **No Conflicts**: Merge conflicts resolved

#### 📝 PR Title Format
```
<type>(<scope>): <brief description>
```

#### 📄 PR Description Template
```markdown
## Description
Brief description of changes made

## Changes
- [ ] Add new feature X
- [ ] Fix bug Y
- [ ] Update documentation

## Testing
- [ ] Tested in WordPress 6.0+
- [ ] Tested with PHP 7.4+
- [ ] Tested free and pro versions

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Tests added/updated
```

## 💻 Coding Standards

### PHP (WordPress Coding Standards)
```php
<?php
/**
 * Brief description of the function.
 *
 * @since 4.0.0
 * @param string $param1 Description of parameter.
 * @param array  $param2 Description of parameter.
 * @return bool|string Return description.
 */
function sample_function( $param1, $param2 ) {
    if ( ! $param1 ) {
        return false;
    }

    $result = process_data( $param2 );

    return $result;
}
```

### JavaScript
```javascript
// Use camelCase for variables and functions
const wpaigenData = {
    isLoading: false,
    errorMessage: ''
};

// Use meaningful variable names
function processApiResponse( response ) {
    if ( ! response.success ) {
        throw new Error( response.message );
    }

    return response.data;
}
```

### CSS
```css
/* Use BEM-like naming with wpaigen- prefix */
.wpaigen-button {
    display: inline-block;
    padding: 12px 24px;
}

.wpaigen-button--primary {
    background: #0073aa;
    color: white;
}

.wpaigen-button__icon {
    margin-right: 8px;
}
```

## 🧪 Testing

### Manual Testing Checklist
- [ ] Plugin activates/deactivates successfully
- [ ] All features work in both free and pro versions
- [ ] Payment processing (Midtrans/PayPal) functions correctly
- [ ] License validation and persistence work
- [ ] Content generation works in all supported languages
- [ ] No PHP errors or warnings
- [ ] Compatible with latest WordPress version

### WordPress Compatibility
- Test with WordPress 5.8+ (minimum requirement)
- Test with latest WordPress stable version
- Test with PHP 7.4+ (minimum requirement)

## 📚 Documentation

### When to Update Documentation
- New features added
- Existing features modified
- Installation/activation process changed
- Configuration options added/changed
- Breaking changes introduced

### Documentation Files
- `README.md` - Main plugin documentation (GitHub)
- `readme.txt` - WordPress.org readme
- Inline code comments for complex logic

## 🚀 Release Process

### Automated Scripts
Use the automated scripts for release preparation:

1. **Version Bumping**: Update version numbers across files
2. **SVN Package**: `npm run prepare-svn` - Prepares package for WordPress.org
3. **Version Check**: `npm run version-check` - Validates version consistency

### Release Steps
1. Update version numbers
2. Update changelog in `readme.txt`
3. Create git tag: `git tag -a v4.0.0 -m "Release v4.0.0"`
4. Push tag: `git push origin v4.0.0`
5. Create GitHub release
6. Run SVN package script: `npm run prepare-svn`
7. Commit to SVN and create tag

## 🤝 Support Channels

- **Issues**: [GitHub Issues](https://github.com/tarunawahyudi/wpaigen-ai-generator/issues)
- **Discussions**: [GitHub Discussions](https://github.com/tarunawahyudi/wpaigen-ai-generator/discussions)
- **Email**: wahyuditaruna97@gmail.com

## 📋 Code Review Guidelines

### What to Review
1. **Functionality**: Does the code work as expected?
2. **Security**: Are inputs validated and outputs escaped?
3. **Performance**: Is the code efficient?
4. **Compatibility**: Does it follow WordPress standards?
5. **Documentation**: Is code properly documented?

### Review Process
1. Self-review your code before submitting PR
2. Focus on logic, not just style
3. Provide constructive feedback
4. Ask questions if anything is unclear

## 🏆 Recognition

Contributors will be recognized in:
- GitHub contributors list
- Plugin credits (for significant contributions)
- Release changelog

Thank you for contributing to WPaigen AI Generator! 🎉