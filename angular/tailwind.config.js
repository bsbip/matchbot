module.exports = {
    prefix: '',
    purge: {
        enabled: true,
        content: ['./src/**/*.{html,ts}'],
    },
    darkMode: 'class',
    theme: {
        extend: {},
    },
    variants: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
