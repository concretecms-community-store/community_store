module.exports = {
    base: '/community_store/',
    title: 'Community Store for concrete5',
    description: 'An open, free and community developed eCommerce system for concrete5',
    head: [
       ['link', { rel: "shortcut icon", href: "/favicon.ico"}]
    ],
    themeConfig: {
        nav: [
            {text: 'User Guide', link: '/user-guide/'},
            {text: 'How-Tos', link: '/how-tos/'},
            {text: 'Developers', link: '/developers/'},
            {text: 'Github', link: 'https://github.com/concrete5-community-store'}
        ],
        sidebar: {
            '/user-guide/':
                [{
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        '',
                        'essentials',
                        'installation',
                        'configuration',
                    ]
                },
                    {
                        title: 'Store Management',
                        collapsable: false,
                        children: [
                            'products',
                            'orders',
                            'discounts',
                            'multilingual'

                        ]
                    } ,
                    {
                        title: 'Going Live',
                        collapsable: false,
                        children: [
                            'going-live',
                        ]
                    }

                ]
            ,
            '/how-tos/':
                [{
                    title: 'How-Tos',
                    collapsable: false,
                    children: [
                        '',
                        'digital',
                        'memberships',
                        'donations',
                        'categorization',
                        'related-products',
                        'restricting-countries',
                        'multilingual',
                    ]
                }],
            '/developers/':

                [{
                    title: 'Developer Guide',
                    collapsable: false,
                    children: [
                        '',
                        'attributes',
                        'events',
                        'shipping_methods',
                        'payment_methods',
                        'cli-commands',
                        'translations'
                    ]
                }]
            ,
        }
    }
}
