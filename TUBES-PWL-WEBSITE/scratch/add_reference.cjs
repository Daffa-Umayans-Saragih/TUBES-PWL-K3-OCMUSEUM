const fs = require('fs');
const path = require('path');

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(file));
        } else if (file.endsWith('.css')) {
            results.push(file);
        }
    });
    return results;
}

const cssFiles = walk('resources/css');
const appCssPath = path.resolve('resources/css/app.css');

cssFiles.forEach(f => {
    let content = fs.readFileSync(f, 'utf8');
    if (path.resolve(f) === appCssPath) return;
    
    // Check if it uses @apply
    if (content.includes('@apply')) {
        // Check if it already has reference or import
        if (!content.includes('@reference') && !content.includes('@import "tailwindcss"')) {
            const relPath = path.relative(path.dirname(f), appCssPath).replace(/\\/g, '/');
            content = '@reference "' + relPath + '";\n' + content;
            fs.writeFileSync(f, content);
            console.log('Added reference to ' + f);
        }
    }
});
