import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Filament/**/*.php',
        './app/Livewire/**/*.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        maxWidth: 'none',
                        color: theme('colors.gray.700'),
                        '[class~="lead"]': {
                            color: theme('colors.gray.600'),
                        },
                        a: {
                            color: theme('colors.primary.600'),
                            textDecoration: 'none',
                            fontWeight: '500',
                            '&:hover': {
                                color: theme('colors.primary.700'),
                                textDecoration: 'underline',
                            },
                        },
                        strong: {
                            color: theme('colors.gray.900'),
                            fontWeight: '600',
                        },
                        'ol[type="A"]': {
                            '--list-counter-style': 'upper-alpha',
                        },
                        'ol[type="a"]': {
                            '--list-counter-style': 'lower-alpha',
                        },
                        'ol[type="A" s]': {
                            '--list-counter-style': 'upper-alpha',
                        },
                        'ol[type="a" s]': {
                            '--list-counter-style': 'lower-alpha',
                        },
                        'ol[type="I"]': {
                            '--list-counter-style': 'upper-roman',
                        },
                        'ol[type="i"]': {
                            '--list-counter-style': 'lower-roman',
                        },
                        'ol[type="I" s]': {
                            '--list-counter-style': 'upper-roman',
                        },
                        'ol[type="i" s]': {
                            '--list-counter-style': 'lower-roman',
                        },
                        'ol[type="1"]': {
                            '--list-counter-style': 'decimal',
                        },
                        'ul > li': {
                            position: 'relative',
                        },
                        'ol > li': {
                            position: 'relative',
                        },
                        'ul > li::before': {
                            content: '""',
                            position: 'absolute',
                            backgroundColor: theme('colors.gray.400'),
                            borderRadius: '50%',
                            width: '0.375rem',
                            height: '0.375rem',
                            top: 'calc(0.875rem - 0.1875rem)',
                            left: '-1.5rem',
                        },
                        'ol > li::before': {
                            content: 'counter(list-item, var(--list-counter-style, decimal)) "."',
                            fontWeight: '400',
                            color: theme('colors.gray.500'),
                            position: 'absolute',
                            left: '-1.5rem',
                        },
                        'blockquote p:first-of-type::before': {
                            content: 'none',
                        },
                        'blockquote p:last-of-type::after': {
                            content: 'none',
                        },
                        pre: {
                            color: theme('colors.gray.50'),
                            backgroundColor: theme('colors.gray.900'),
                            overflowX: 'auto',
                            fontWeight: '400',
                            borderRadius: '0.5rem',
                            border: `1px solid ${theme('colors.gray.700')}`,
                            padding: '1rem',
                            marginTop: '1.5em',
                            marginBottom: '1.5em',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                        },
                        'pre code': {
                            backgroundColor: 'transparent',
                            borderWidth: '0',
                            borderRadius: '0',
                            padding: '0',
                            fontWeight: 'inherit',
                            color: 'inherit',
                            fontSize: 'inherit',
                            fontFamily: 'inherit',
                            lineHeight: 'inherit',
                        },
                        'pre code::before': {
                            content: 'none',
                        },
                        'pre code::after': {
                            content: 'none',
                        },
                        code: {
                            backgroundColor: theme('colors.gray.100'),
                            color: theme('colors.gray.900'),
                            borderRadius: '0.25rem',
                            padding: '0.2em 0.4em',
                            fontWeight: '500',
                        },
                        'code::before': {
                            content: 'none',
                        },
                        'code::after': {
                            content: 'none',
                        },
                        table: {
                            width: '100%',
                            tableLayout: 'auto',
                            textAlign: 'left',
                            marginTop: '2em',
                            marginBottom: '2em',
                            fontSize: '0.875rem',
                            lineHeight: '1.7142857',
                        },
                        thead: {
                            borderBottomWidth: '1px',
                            borderBottomColor: theme('colors.gray.300'),
                        },
                        'thead th': {
                            color: theme('colors.gray.900'),
                            fontWeight: '600',
                            verticalAlign: 'bottom',
                            paddingRight: '0.5714286em',
                            paddingBottom: '0.5714286em',
                            paddingLeft: '0.5714286em',
                        },
                        'tbody tr': {
                            borderBottomWidth: '1px',
                            borderBottomColor: theme('colors.gray.200'),
                        },
                        'tbody tr:last-child': {
                            borderBottomWidth: '0',
                        },
                        'tbody td': {
                            verticalAlign: 'baseline',
                        },
                        tfoot: {
                            borderTopWidth: '1px',
                            borderTopColor: theme('colors.gray.300'),
                        },
                        'tfoot td': {
                            verticalAlign: 'top',
                        },
                    },
                },
                dark: {
                    css: {
                        color: theme('colors.gray.300'),
                        '[class~="lead"]': {
                            color: theme('colors.gray.400'),
                        },
                        a: {
                            color: theme('colors.primary.400'),
                            '&:hover': {
                                color: theme('colors.primary.300'),
                            },
                        },
                        strong: {
                            color: theme('colors.gray.100'),
                        },
                        'ul > li::before': {
                            backgroundColor: theme('colors.gray.600'),
                        },
                        'ol > li::before': {
                            color: theme('colors.gray.400'),
                        },
                        blockquote: {
                            borderLeftColor: theme('colors.gray.700'),
                            color: theme('colors.gray.300'),
                        },
                        h1: {
                            color: theme('colors.gray.100'),
                        },
                        h2: {
                            color: theme('colors.gray.100'),
                        },
                        h3: {
                            color: theme('colors.gray.100'),
                        },
                        h4: {
                            color: theme('colors.gray.100'),
                        },
                        'figure figcaption': {
                            color: theme('colors.gray.400'),
                        },
                        code: {
                            backgroundColor: theme('colors.gray.800'),
                            color: theme('colors.blue.300'),
                            borderRadius: '0.25rem',
                            padding: '0.2em 0.4em',
                            fontWeight: '500',
                            border: `1px solid ${theme('colors.gray.700')}`,
                        },
                        'a code': {
                            color: theme('colors.primary.400'),
                        },
                        pre: {
                            color: theme('colors.gray.200'),
                            backgroundColor: theme('colors.gray.900'),
                            borderRadius: '0.5rem',
                            border: `1px solid ${theme('colors.gray.700')}`,
                            padding: '1rem',
                            marginTop: '1.5em',
                            marginBottom: '1.5em',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.3)',
                        },
                        thead: {
                            borderBottomColor: theme('colors.gray.600'),
                        },
                        'thead th': {
                            color: theme('colors.gray.100'),
                        },
                        'tbody tr': {
                            borderBottomColor: theme('colors.gray.700'),
                        },
                        tfoot: {
                            borderTopColor: theme('colors.gray.600'),
                        },
                        'tfoot td': {
                            color: theme('colors.gray.300'),
                        },
                    },
                },
            }),
        },
    },

    plugins: [
        forms,
        typography,
    ],
};
