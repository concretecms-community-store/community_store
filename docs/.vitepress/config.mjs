import { defineConfig } from 'vitepress'

export default defineConfig({
    base: '/community_store/',
    title: 'Community Store for Concrete CMS',
    description: 'An open, free and community developed eCommerce system for Concrete CMS',
    head: [
        ['link', { rel: 'shortcut icon', href: '/community_store/favicon.ico' }]
    ],
    themeConfig: {
        nav: [
            { text: 'User Guide', link: '/user-guide/' },
            { text: 'How-Tos', link: '/how-tos/' },
            { text: 'Developers', link: '/developers/' },
            { text: 'Github', link: 'https://github.com/concretecms-community-store' }
        ],
        sidebar: {
            '/user-guide/': [
                {
                    text: 'Getting Started',
                    items: [
                        { text: 'Introduction', link: '/user-guide/' },
                        { text: 'Essentials', link: '/user-guide/essentials' },
                        { text: 'Installation', link: '/user-guide/installation' },
                        { text: 'Initial Setup', link: '/user-guide/configuration' }
                    ]
                },
                {
                    text: 'Store Management',
                    items: [
                        { text: 'Products', link: '/user-guide/products' },
                        { text: 'Orders', link: '/user-guide/orders' },
                        { text: 'Discounts', link: '/user-guide/discounts' },
                        { text: 'Multilingual', link: '/user-guide/multilingual' }
                    ]
                },
                {
                    text: 'Going Live',
                    items: [
                        { text: 'Go-live Checklist', link: '/user-guide/going-live' }
                    ]
                }
            ],
            '/how-tos/': [
                {
                    text: 'How-Tos',
                    items: [
                        { text: 'Introduction', link: '/how-tos/' },
                        { text: 'Digital Downloads', link: '/how-tos/digital' },
                        { text: 'Memberships', link: '/how-tos/memberships' },
                        { text: 'Donations and Invoice Payments', link: '/how-tos/donations' },
                        { text: 'Product Categorization', link: '/how-tos/categorization' },
                        { text: 'Related Products', link: '/how-tos/related-products' },
                        { text: 'Billing and Shipping Countries', link: '/how-tos/restricting-countries' },
                        { text: 'Multilingual Setup', link: '/how-tos/multilingual' }
                    ]
                }
            ],
            '/developers/': [
                {
                    text: 'Developer Guide',
                    items: [
                        { text: 'Customizations', link: '/developers/' },
                        { text: 'Product and Order Attributes', link: '/developers/attributes' },
                        { text: 'Events', link: '/developers/events' },
                        { text: 'Creating Shipping Methods', link: '/developers/shipping_methods' },
                        { text: 'Creating Payment Methods', link: '/developers/payment_methods' },
                        { text: 'CLI Commands', link: '/developers/cli-commands' },
                        { text: 'Translations', link: '/developers/translations' }
                    ]
                }
            ]
        },
        search: {
            provider: 'local'
        },
        footer: {
            message: 'MIT Licensed'
        }
    }
})
