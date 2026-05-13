import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

const config: Config = {
    title: 'Jinya Router',
    tagline: 'A simple attribute-based routing library',
    favicon: 'img/logo.svg',

    // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
    future: {
        v4: true, // Improve compatibility with the upcoming Docusaurus v4
    },

    // Set the production url of your site here
    url: 'https://router.jinya.dev',
    // Set the /<baseUrl>/ pathname under which your site is served
    // For GitHub pages deployment, it is often '/<projectName>/'
    baseUrl: '/',

    // GitHub pages deployment config.
    // If you aren't using GitHub pages, you don't need these.
    organizationName: 'jinya-cms', // Usually your GitHub org/user name.
    projectName: 'jinya-router', // Usually your repo name.

    onBrokenLinks: 'throw',

    // Even if you don't use internationalization, you can use this field to set
    // useful metadata like html lang. For example, if your site is Chinese, you
    // may want to replace "en" with "zh-Hans".
    i18n: {
        defaultLocale: 'en',
        locales: ['en'],
    },

    presets: [
        [
            'classic',
            {
                docs: {
                    sidebarPath: './sidebars.ts',
                    // Please change this to your repo.
                    // Remove this to remove the "edit this page" links.
                    editUrl:
                        'https://github.com/jinya-cms/jinya-router/tree/main/docs/',
                },
                theme: {
                    customCss: './src/css/custom.css',
                },
            } satisfies Preset.Options,
        ],
    ],

    themeConfig: {
        // Replace with your project's social card
        image: 'img/social-card.jpg',
        colorMode: {
            respectPrefersColorScheme: true,
        },
        navbar: {
            title: 'Jinya Router',
            logo: {
                alt: 'Jinya Router Logo',
                src: 'img/logo.svg',
            },
            items: [
                {
                    type: 'docSidebar',
                    sidebarId: 'tutorialSidebar',
                    position: 'left',
                    label: 'Documentation',
                },
                {
                    href: 'https://gitlab.imanuel.dev/jinya-cms/jinya-router/',
                    label: 'GitLab',
                    position: 'right',
                },
                {
                    href: 'https://github.com/jinya-cms/jinya-router',
                    label: 'GitHub',
                    position: 'right',
                },
            ],
        },
        footer: {
            style: 'dark',
            links: [
                {
                    title: 'Documentation',
                    items: [
                        {
                            label: 'Documentation',
                            to: '/docs/intro',
                        },
                    ],
                },
                {
                    title: 'Community',
                    items: [
                        {
                            label: 'Stack Overflow',
                            href: 'https://stackoverflow.com/questions/tagged/jinya-cms',
                        },
                        {
                            label: 'Website',
                            href: 'https://jinya.de',
                        },
                    ],
                },
                {
                    title: 'More',
                    items: [
                        {
                            href: 'https://gitlab.imanuel.dev/jinya-cms/jinya-router/',
                            label: 'GitLab',
                        },
                        {
                            label: 'GitHub',
                            href: 'https://github.com/jinya-cms/jinya-router',
                        },
                    ],
                },
            ],
            copyright: `Copyright © ${new Date().getFullYear()} Jinya Developers. Built with Docusaurus.`,
        },
        prism: {
            additionalLanguages: ['php', 'php-extras'],
            theme: prismThemes.github,
            darkTheme: prismThemes.dracula,
        },
    } satisfies Preset.ThemeConfig,
};

export default config;
