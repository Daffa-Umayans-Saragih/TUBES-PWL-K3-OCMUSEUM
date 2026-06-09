const fs = require('fs');
const path = require('path');

function inject(dir) {
    let count = 0;
    fs.readdirSync(dir).forEach(f => {
        let p = path.join(dir, f);
        if (fs.statSync(p).isDirectory()) {
            count += inject(p);
        } else if (p.endsWith('.css')) {
            let content = fs.readFileSync(p, 'utf8');
            if (content.includes('@apply') && !content.includes('@reference "tailwindcss"') && !content.includes('@import "tailwindcss"')) {
                content = '@reference "tailwindcss";\n' + content;
                fs.writeFileSync(p, content, 'utf8');
                count++;
            }
        }
    });
    return count;
}

let updated = inject('resources/css');
console.log('Successfully injected @reference into ' + updated + ' files.');
