const tailwindcss = require('tailwindcss');
const autoprefixer = require('autoprefixer');

const purgecss = require('@fullhuman/postcss-purgecss')({
    // Specify the paths to all of the template files in your project
    content: ['./public/index.html', './src/**/*.html', './src/**/*.vue'],

    // Include any special characters you're using in this regular expression
    defaultExtractor: (content) => content.match(/[\w-/:]+(?<!:)/g) || [],
});

module.exports = {
    plugins: [
        tailwindcss('./tailwind.config.js'),
        autoprefixer({
            add: true,
            grid: true,
        }),
        ...(process.env.NODE_ENV === 'production' ? [purgecss] : []),
    ],
};
