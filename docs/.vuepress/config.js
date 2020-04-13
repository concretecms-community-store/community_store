module.exports = {
    title: 'Community Store for concrete5',
    description: '',
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
                            'discounts'

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
                        'multilingual',
                    ]
                }],
            '/developers/':

                [{
                    title: 'Developer Guide',
                    collapsable: false,
                    children: [
                        '',
                        'events',
                        'attributes',
                        'shipping_methods',
                        'payment_methods',
                        'cli-commands'
                    ]
                }]
            ,
        }
    }
}

