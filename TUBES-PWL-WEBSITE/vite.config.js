import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';
import path from 'path';

function getFiles(dir, extensions = ['.css', '.js']) {
    let results = [];

    if (!fs.existsSync(dir)) return results;

    const list = fs.readdirSync(dir);

    list.forEach((file) => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);

        if (stat && stat.isDirectory()) {
            results = results.concat(getFiles(filePath, extensions));
        } else {
            if (extensions.includes(path.extname(file))) {
                results.push(filePath.replace(/\\/g, '/'));
            }
        }
    });

    return results;
}

const cssFiles = getFiles('resources/css');
const jsFiles = getFiles('resources/js');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                ...cssFiles,
                ...jsFiles,
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});