#!/usr/bin/env node

/**
 * WPaigen AI Generator - SVN Package Preparation Script
 *
 * This script prepares the plugin package for WordPress.org SVN deployment.
 * It automatically handles file copying, exclusion of development files,
 * and replacement of the trunk folder with clean plugin files.
 */

const fs = require('fs');
const path = require('path');
const readline = require('readline');

class SVNPackager {
    constructor() {
        this.rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout
        });

        // Development files to exclude from SVN package
        this.excludePatterns = [
            'CONTRIBUTING.md',
            'prepare-svn-package.js',
            '.git',
            '.gitignore',
            '.gitattributes',
            '.github',
            'RELEASING.md',
            'CLAUDE.md',
            'package.json',
            'package-lock.json',
            'webpack.config.js',
            'gulpfile.js',
            '.vscode',
            '.idea',
            '.DS_Store',
            'Thumbs.db',
            '*.bak',
            '*.old',
            '*.orig',
            '.editorconfig',
            '.eslintrc*',
            '.prettierrc*',
            '.babelrc*',
            'tsconfig.json'
        ];

        // Files that must be included
        this.requiredFiles = [
            'wpaigen-ai-generator.php',
            'readme.txt',
            'includes/',
            'admin/',
            'admin/js/',
            'admin/css/',
            'admin/views/'
        ];
    }

    async promptUser(question) {
        return new Promise((resolve) => {
            this.rl.question(question, (answer) => {
                resolve(answer.trim());
            });
        });
    }

    async getSVNPath() {
        console.log('\n🚀 WPaigen AI Generator - SVN Package Preparation');
        console.log('=====================================================\n');

        const svnPath = await this.promptUser(
            'Enter the path to your SVN plugin directory: '
        );

        if (!svnPath) {
            console.log('❌ Error: SVN path is required');
            process.exit(1);
        }

        if (!fs.existsSync(svnPath)) {
            console.log(`❌ Error: Path does not exist: ${svnPath}`);
            process.exit(1);
        }

        const resolvedPath = path.resolve(svnPath);
        console.log(`✅ SVN path: ${resolvedPath}`);

        return resolvedPath;
    }

    validateSVNStructure(svnPath) {
        console.log('\n📋 Validating SVN structure...');

        const requiredDirs = ['trunk', 'tags', 'assets'];
        const missingDirs = [];

        requiredDirs.forEach(dir => {
            const dirPath = path.join(svnPath, dir);
            if (!fs.existsSync(dirPath)) {
                missingDirs.push(dir);
            }
        });

        if (missingDirs.length > 0) {
            console.log(`❌ Error: Missing SVN directories: ${missingDirs.join(', ')}`);
            console.log('Required structure: trunk/, tags/, assets/');
            process.exit(1);
        }

        console.log('✅ SVN structure validation passed');
        return true;
    }

    shouldExcludeFile(filePath) {
        const fileName = path.basename(filePath);

        return this.excludePatterns.some(pattern => {
            // Handle glob patterns
            if (pattern.includes('*')) {
                const regex = new RegExp(pattern.replace(/\*/g, '.*'));
                return regex.test(fileName);
            }

            // Handle directory patterns
            if (pattern.endsWith('/')) {
                return filePath.includes(pattern);
            }

            // Handle exact file matches
            return fileName === pattern;
        });
    }

    copyFileWithValidation(src, dest) {
        try {
            // Ensure destination directory exists
            const destDir = path.dirname(dest);
            if (!fs.existsSync(destDir)) {
                fs.mkdirSync(destDir, { recursive: true });
            }

            fs.copyFileSync(src, dest);
            return true;
        } catch (error) {
            console.log(`⚠️  Warning: Could not copy ${src} -> ${dest}: ${error.message}`);
            return false;
        }
    }

    async preparePackage(sourceDir, destDir) {
        console.log('\n📦 Preparing SVN package...');
        console.log(`Source: ${sourceDir}`);
        console.log(`Destination: ${destDir}`);

        // Clear trunk directory
        console.log('\n🗑️  Clearing trunk directory...');
        if (fs.existsSync(destDir)) {
            fs.rmSync(destDir, { recursive: true, force: true });
        }
        fs.mkdirSync(destDir, { recursive: true });

        let copiedFiles = 0;
        let skippedFiles = 0;
        const totalFiles = [];

        // Recursively copy files
        const copyDirectory = (currentPath, relativePath = '') => {
            const items = fs.readdirSync(currentPath);

            for (const item of items) {
                const itemPath = path.join(currentPath, item);
                const relativeItemPath = path.join(relativePath, item);
                const destPath = path.join(destDir, relativeItemPath);

                // Skip development files
                if (this.shouldExcludeFile(relativeItemPath)) {
                    skippedFiles++;
                    continue;
                }

                totalFiles.push(relativeItemPath);

                const stats = fs.statSync(itemPath);

                if (stats.isDirectory()) {
                    // Create directory and recurse
                    fs.mkdirSync(destPath, { recursive: true });
                    copyDirectory(itemPath, relativeItemPath + '/');
                } else {
                    // Copy file
                    if (this.copyFileWithValidation(itemPath, destPath)) {
                        copiedFiles++;
                    }
                }
            }
        };

        copyDirectory(sourceDir);

        console.log(`✅ Package preparation complete:`);
        console.log(`   📁 Copied files: ${copiedFiles}`);
        console.log(`   🚫 Skipped files: ${skippedFiles}`);

        // Validate required files
        console.log('\n✅ Validating required files...');
        const missingFiles = [];

        this.requiredFiles.forEach(file => {
            const filePath = path.join(destDir, file);
            if (!fs.existsSync(filePath)) {
                missingFiles.push(file);
            }
        });

        if (missingFiles.length > 0) {
            console.log(`⚠️  Warning: Missing required files: ${missingFiles.join(', ')}`);
        } else {
            console.log('✅ All required files are present');
        }

        return { copiedFiles, skippedFiles, totalFiles };
    }

    showSummary(result) {
        console.log('\n📊 Package Summary');
        console.log('==================');
        console.log(`Total files processed: ${result.totalFiles.length}`);
        console.log(`Files copied to trunk: ${result.copiedFiles}`);
        console.log(`Development files excluded: ${result.skippedFiles}`);
        console.log(`Package size: ${this.getDirectorySize(result.totalFiles)}`);

        // Show excluded files
        if (result.skippedFiles > 0) {
            console.log('\n🚫 Excluded Files:');
            this.excludePatterns.forEach(pattern => {
                console.log(`   - ${pattern}`);
            });
        }

        // Show included files summary
        console.log('\n📁 Included Files Summary:');
        const dirs = {};
        result.totalFiles.forEach(file => {
            const dir = path.dirname(file) || 'root';
            if (!dirs[dir]) dirs[dir] = 0;
            dirs[dir]++;
        });

        Object.entries(dirs).forEach(([dir, count]) => {
            console.log(`   ${dir}/: ${count} files`);
        });
    }

    getDirectorySize(files) {
        let totalSize = 0;
        // This is a rough estimate - actual size may vary
        files.forEach(file => {
            totalSize += 1024; // Assume 1KB per file
        });

        if (totalSize < 1024) {
            return `${totalSize} bytes`;
        } else if (totalSize < 1024 * 1024) {
            return `${(totalSize / 1024).toFixed(1)} KB`;
        } else {
            return `${(totalSize / (1024 * 1024)).toFixed(1)} MB`;
        }
    }

    generateSVNStatus(result, svnPath, trunkPath) {
        console.log('\n📋 SVN Status Preview');
        console.log('====================');
        console.log('Files that will be added/modified in trunk:\n');

        result.totalFiles.forEach(file => {
            const fullPath = path.join(trunkPath, file);
            if (fs.existsSync(fullPath)) {
                console.log(`M       ${file}`);
            } else {
                console.log(`A       ${file}`);
            }
        });

        console.log('\nNext steps:');
        console.log('1. Review the files above');
        console.log('2. Navigate to your SVN directory');
        console.log('3. Run: cd ' + svnPath);
        console.log('4. Run: svn status');
        console.log('5. Run: svn add --force trunk/');
        console.log('6. Run: svn commit -m "Release vX.Y.Z"');
    }

    async run() {
        try {
            // Get current plugin directory (where this script is located)
            const currentDir = __dirname;
            console.log(`📍 Current plugin directory: ${currentDir}`);

            // Get SVN directory
            const svnPath = await this.getSVNPath();

            // Validate SVN structure
            this.validateSVNStructure(svnPath);

            // Get trunk path
            const trunkPath = path.join(svnPath, 'trunk');

            // Prepare the package
            const result = await this.preparePackage(currentDir, trunkPath);

            // Show summary
            this.showSummary(result);

            // Generate SVN status preview
            this.generateSVNStatus(result, svnPath, trunkPath);

            console.log('\n✅ Package preparation completed successfully!');
            console.log('\n💡 Tip: Review the SVN status preview above before committing');

        } catch (error) {
            console.error('\n❌ Error:', error.message);
            process.exit(1);
        } finally {
            this.rl.close();
        }
    }
}

// Run the packager
const packager = new SVNPackager();
packager.run().catch(error => {
    console.error('Fatal error:', error);
    process.exit(1);
});