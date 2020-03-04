module.exports = {
    title: 'Community Store for concrete5',
    description: '',
    themeConfig: {
        nav: [
            { text: 'Getting Started', link: '/start/' },
            { text: 'User Guide', link: '/user-guide/' },
            { text: 'How-Tos', link: '/how-tos/' },
            { text: 'Developers', link: '/developers/' },
            { text: 'Github', link: 'https://github.com/concrete5-community-store' }
        ],
        sidebar: {
            '/start/':
                [{
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        '',
                        'setup',
                        'config',
                    ]
                }]
            ,
            '/user-guide/': [
                {
                title: 'Dashboard',
                collapsable: false,
                children: [
                    'store-overview',
                    'orders',
                    'products',
                    'manufacturers',
                    'discounts',
                    'settings',
                    'reports',
                    'multilingual'
                ]
                },
                {
                    title: 'Blocks',
                    collapsable: false,
                    children: [
                        'blocks'
                    ]
                },
                {
                    title: 'Going Live',
                    collapsable: false,
                    children: [
                        'going-live',
                    ]
                }

            ],

            '/how-tos/' :
                [{
                    title: 'How-Tos',
                    collapsable: false,
                    children: [
                        '',
                        'digital',
                        'memberships',
                        'donations',
                        'category-pages',
                        'related-products',
                    ]
                }],
            '/developers/':

                [{
                    title: 'Developer Guide',
                    collapsable: false,
                    children: [
                        '',
                        'customizations',
                        'events',
                        'cli-commands',
                        'contributing'
                    ]
                }]
            ,
        }
    }
}

//
//
// '/start/',
//     '/products/',
//     '/discounts/',
//     '/blocks/',
//     '/payment_methods/',
//     '/shipping_methods/',
//     '/multilingual/',
//     '/customizations/',
//     '/how-tos/',
//
